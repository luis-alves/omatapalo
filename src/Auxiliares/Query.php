<?php

namespace Src\Auxiliares;

use PDO;

/**
 *
 */
class Query
{
    public static function getSQL_custos()
    {
        include 'src/Auxiliares/globals.php';

        $cindus = $cisRelatorioMensal[$cAnalitico];

        $query = "SELECT `familia`,
                         sum(`valor`) AS valor,
                         MONTH(`data`) AS mes,
                         `data`,
                         `artigo`
                  FROM `custos`
                  WHERE  YEAR(custos.data) IN ('$ano') AND `cind` = $cindus
                  GROUP BY familia, mes
                  ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

            return $vars['row'];
        } else {
            $vars['row'] = 0;
            return $vars['row'];
        }
    }

    public static function getSQLVendas($tipo1, $tipo2)
    {
        include "src/Auxiliares/globals.php";


        $centroInd = 'importacao'.'_'.$cAnalitico;

        $query = "SELECT *,
                    CASE
                         WHEN '$tipo1' IN ('GTO', 'SPA')
                         THEN (`peso` * `valor_in_ton`)
                         ELSE (`peso` * `valor_ex_ton` * (1 - `desco`))
                         END
                        AS `total`,
                    CASE
                         WHEN '$tipo1' IN ('GTO', 'SPA')
                         THEN (`valor_in_ton` * `baridade`)
                         ELSE (`valor_ex_ton` * `baridade` * (1 - `desco`))
                         END
                        AS `preco_m3`,
                    MONTH(`data`) AS `mes`,
                    round(`peso` /`baridade`,2) as m3
                   FROM $centroInd
                   LEFT JOIN `agregados`
                   ON `nome_agr` = `nome_agre`
                   LEFT JOIN `baridades`
                   ON `agr_id` = `agregado_id`
                   LEFT JOIN `valorun_interno_ton`
                   ON `agr_bar_id` = `agr_id`
                   LEFT JOIN `valorun_externo_ton`
                   ON `agr_bar_ton_id` = `agr_id`
                   LEFT JOIN `obras`
                   ON `id_obra` = `obra`
                   WHERE `tipo_doc` IN ('$tipo1', '$tipo2') AND
                         `nome_agr` IN ($lista_agregados) AND
                         YEAR(`data`) IN ('$ano')
        ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            return $vars['row'];
        } else {
            return [];
        }
    }

    public static function getSQLProducoes($tipo1, $tipo2)
    {
        include "src/Auxiliares/globals.php";
        include "src/Auxiliares/helpers.php";

        $centroInd = 'producoes'.'_'.$cAnalitico;

        $query = " SELECT *,
                    round(`qt` / `baridade`,0) as m3,
                    round(`valor_in_ton` * `baridade`,2) as pu,
                    round(round(`qt` / `baridade`,0) * round(`valor_in_ton` * `baridade`,2)) AS `total`,
                    MONTH(`data_in`) AS `mes`,
                    `nome_agr_corr` AS `nome`
                   FROM $centroInd
                   LEFT JOIN `agregados`
                   ON `cod_agr` = `agr_id`
                   LEFT JOIN `baridades`
                   ON `agr_id` = `agregado_id`
                   LEFT JOIN `valorun_interno_ton`
                   ON `agr_bar_id` = `agr_id`
                   LEFT JOIN `valorun_externo_ton`
                   ON `agr_bar_ton_id` = `agr_id`
                   WHERE `tipo_doc` IN ('$tipo1', '$tipo2') AND
                         `nome_agre` IN ($lista_agregados) AND
                         YEAR(`data_in`) IN ('$ano')
                   GROUP BY mes, nome
        ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $array = $vars['row'];


            return $array;
        } else {
            for ($i=1; $i <= 12; $i++) {
                $array[$i] = 0;
            }

            return $array;
        }
    }

    public static function getStock()
    {
        include 'src/Auxiliares/globals.php';

        # Verificar quais os preços dos agregados
        $query = " SELECT  `valor_in_ton` * `baridade` AS pu,
                           `nome_agre` AS brita
                   FROM `valorun_interno_ton`
                   LEFT JOIN `agregados`
                   ON `agr_id` = `agr_bar_id`
                   LEFT JOIN `baridades`
                   ON `agr_id` = `agregado_id`
                   GROUP BY `brita`
        ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);
        }

        $britasPrecos = $vars['row'];

        $valorStockInicial = 0;
        foreach ($britasPrecos as $numero => $nome) {
            foreach ($stock[$cAnalitico][$ano] as $key => $value) {
                if ($key == $nome->brita) {
                    $valorStockInicial += $value * $nome->pu;
                }
            }
        }

        return $valorStockInicial;
    }

    public static function getSQL_MaoDeObra($tipo)
    {
        include 'src/Auxiliares/globals.php';

        $cindus = $cisRelatorioMensal[$cAnalitico];
        $anoDefinido = 'ano_'.$ano;

        $query = "SELECT MONTH(`data`) AS mes,
	                     SUM(`h_normais` * $anoDefinido + `h_extras` * $anoDefinido) AS `custo_un`
                  FROM `folha_ponto`
                  LEFT JOIN `colaboradores`
                  ON `num_mec` = `n_mec`
                  WHERE  YEAR(`data`) IN ($ano) AND
                         `nacional` = '$tipo' AND `cind` = $cindus
                  GROUP BY mes";


        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $array = array();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            for ($i=1; $i <= 12; $i++) {
                foreach ($vars['row'] as $number => $value) {
                    if ($value->mes == $i) {
                        $dados[$i] = $value->custo_un;
                    }
                    if (!isset($dados[$i])) {
                        $dados[$i] = 0;
                    }
                }
            }

            return $dados;
        } else {
            for ($i=1; $i <= 12 ; $i++) {
                $dados[$i] = 0;
            }
            return $dados;
        }
    }

    public static function getSQLEntradas()
    {
        include "src/Auxiliares/globals.php";

        $centroInd = 'producoes'.'_'.$cAnalitico;

        $cindus = $cisRelatorioMensal[$cAnalitico];

        $query = " SELECT
                    ROUND(SUM(`qt` * `valor_in_ton`)) AS `total`,
                    MONTH(`data_in`) AS `mes`
                   FROM $centroInd
                   LEFT JOIN `agregados`
                   ON `cod_agr` = `agr_id`
                   LEFT JOIN `valorun_interno_ton`
                   ON `agr_bar_id` = `agr_id`
                   LEFT JOIN `valorun_externo_ton`
                   ON `agr_bar_ton_id` = `agr_id`
                   WHERE `tipo_doc` IN ('ENT') AND
                         `nome_agre` IN ($lista_agregados) AND
                         YEAR(`data_in`) IN ('$ano')
                   GROUP BY mes
        ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

            $array = $vars['row'];

            # Ordenar por array de meses (On^2)
            #
            for ($i=0; $i < 12; $i++) {
                foreach ($array as $key => $value) {
                    if ($value->mes - 1 == $i) {
                        $arrayOrdenado[$value->mes] = $value->total;
                    }
                }
            }

            # Inserir meses sem valores (O n.n^2)
            #
            for ($i=1; $i <= 12; $i++) {
                if (empty($arrayOrdenado[$i])) {
                    $arrayOrdenado[$i] = 0;
                }
            }

            return $arrayOrdenado;
        } else {
            for ($i=1; $i <= 12; $i++) {
                $arrayOrdenado[$i] = 0;
            }

            return $arrayOrdenado;
        }
    }

    public static function getSQLDRVendas($tipo1, $tipo2)
    {
        include "src/Auxiliares/globals.php";

        $centroInd = 'importacao'.'_'.$cAnalitico;

        $cindus = $cisRelatorioMensal[$cAnalitico];

        $query = "SELECT nome_agr,

                                 `peso` * `valor_in_ton`
                                  AS `total`,

                                   `baridade` * `valor_in_ton`
                                    AS `preco_m3`,
                                    round(`peso` / `baridade`,2) as m3,
                            MONTH(`data`) AS `mes`,
                            `peso` /`baridade` as m3
                           FROM importacao_cassosso
                           LEFT JOIN `agregados`
                           ON `nome_agr` = `nome_agre`
                           LEFT JOIN `baridades`
                           ON `agr_id` = `agregado_id`
                           LEFT JOIN `valorun_interno_ton`
                           ON `agr_bar_id` = `agr_id`
                           LEFT JOIN `valorun_externo_ton`
                           ON `agr_bar_ton_id` = `agr_id`
                           LEFT JOIN `obras`
                           ON `id_obra` = `obra`
                           WHERE `tipo_doc` IN ('GTO', 'SPA') AND
                                 YEAR(`data`) IN ('2016')
                                 ";






        // $query = "SELECT *,
        //              (CASE
        //                  WHEN '$tipo1' IN ('GTO', 'SPA')
        //                  THEN `peso` * `valor_in_ton`
        //                  ELSE `peso` * `valor_ex_ton` * (1 - `desco`)
        //                  END
        //                ) AS `total`,
        //              (CASE
        //                  WHEN '$tipo2' IN ('GTO', 'SPA')
        //                  THEN `baridade` * `valor_in_ton`
        //                  ELSE `baridade` * `valor_ex_ton` * (1 - `desco`)
        //                  END
        //                   ) AS `preco_m3`,
        //             round(`peso` / `baridade`,2) as m3,
        //             MONTH(`data`) AS `mes`
        //            FROM $centroInd
        //            LEFT JOIN `agregados`
        //            ON `nome_agr` = `nome_agre`
        //            LEFT JOIN `baridades`
        //            ON `agr_id` = `agregado_id`
        //            LEFT JOIN `valorun_interno_ton`
        //            ON `agr_bar_id` = `agr_id`
        //            LEFT JOIN `valorun_externo_ton`
        //            ON `agr_bar_ton_id` = `agr_id`
        //            LEFT JOIN `obras`
        //            ON `id_obra` = `obra`
        //            WHERE `tipo_doc` IN ('$tipo1', '$tipo2') AND
        //                  `nome_agr` IN ($lista_agregados) AND
        //                  YEAR(`data`) IN ('$ano')
        //
        // ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);
            // dump($vars);
            return $vars['row'];
        } else {
            return [];
        }
    }

    public static function getSQLDRProducao()
    {
        include "src/Auxiliares/globals.php";

        $centroInd = ' producoes'.'_'.$cAnalitico;

        $cindus = $cisRelatorioMensal[$cAnalitico];

        $query = "SELECT `nome_agr_corr` as `nome`,
                        month(`data_in`) AS `mes`,
                         round(`qt` / `baridade`) AS `m3`,
                         round(round(`qt` / `baridade`) * round(`valor_in_ton` * `baridade`,2)) AS `total`,
                         round(`valor_in_ton` * `baridade`, 2) AS `pu`
                   FROM $centroInd
                   LEFT JOIN `agregados`
                   ON `agr_id` = `cod_agr`
                   LEFT JOIN `baridades`
                   ON `agr_id` = `agregado_id`
                   LEFT JOIN `valorun_interno_ton`
                   ON `agr_bar_id` = `agr_id`
                   WHERE `nome_agre` IN ($lista_agregados) AND
                         YEAR(`data_in`) IN ('$ano')
                  ORDER BY `mes`
        ";

        $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $stmt = $conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

            return $vars['row'];
        } else {
            return [];
        }
    }
}
