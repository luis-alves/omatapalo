<?php

namespace Src\Controllers;

final class RelatorioMensalAction extends Action
{
    private $listaImportacao = array('importacao_arimba', 'importacao_caraculo', 'importacao_cassosso');
    private $listaProducoes = array('producoes_arimba', 'producoes_caraculo', 'producoes_cassosso');

    private function getSQL($cIndustrial, $op1, $op2, $mesInicial, $mesActual)
    {
        include 'src/Auxiliares/globals.php';

        if (!in_array($cIndustrial, $this->listaImportacao)) {
            return [];
        }

        $tipos = [$op1, $op2];
        $placeholders = str_repeat('?, ', count($tipos) - 1).'?';
        $placeholders2 = str_repeat('?, ', count($lista_agregados_array) - 1).'?';

        $query = "SELECT `nome_agre` AS `nome`,
                         ROUND(SUM(`peso` / `baridade`),2) AS `m3`,
                         ROUND(SUM(
                             CASE
                             WHEN ? IN ('GTO', 'SPA')
                             THEN `peso` * `valor_in_ton`
                             ELSE `peso` * `valor_ex_ton` * (1-`desco`)
                             END
                         )) AS `total`
                  FROM `$cIndustrial`
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agr_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_bar_ton_id` = `agr_id`
                  LEFT JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE  `tipo_doc` IN ($placeholders) AND `nome_agr` IN ($placeholders2)
                  AND YEAR(`data`) IN (?) AND MONTH(`data`) BETWEEN ? AND ?
                  GROUP BY `nome_agr_corr`
                  ORDER by `nome_agr_corr`
                  ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$op1], $tipos, $lista_agregados_array, [$ano, $mesInicial, $mesActual]);
        $rows->execute($params);
        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                foreach ($lista_array_agregados as $agregado => $real) {
                    if ($value->nome === $agregado) {
                        $array[$agregado] = [
                            'nome' => $value->nome,
                            'm3' => $value->m3,
                            'total' => $value->total,
                        ];
                    }
                }
            }
        }

        return $array;
    }

    private function getSQL_clientes($cIndustrial, $op1, $op2, $mesInicial, $mesActual)
    {
        include 'src/Auxiliares/globals.php';

        if (!in_array($cIndustrial, $this->listaImportacao)) {
            return [];
        }

        $tipos = [$op1, $op2];
        $placeholders = str_repeat('?, ', count($tipos) - 1).'?';
        $placeholders2 = str_repeat('?, ', count($lista_agregados_array) - 1).'?';

        $query = "SELECT `nome_agr_corr` AS `nome`,
                         ROUND(SUM(`peso` / `baridade`),2) AS `m3`,
                         ROUND(SUM(
                             CASE
                             WHEN ? IN ('GTO', 'SPA')
                             THEN `peso` * `valor_in_ton`
                             ELSE `peso` * `valor_ex_ton` * (1-`desco`)
                             END
                         )) AS `total`,
                         `nome_cliente` AS `cliente`
                  FROM `$cIndustrial`
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agr_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_bar_ton_id` = `agr_id`
                  LEFT JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE  `tipo_doc` IN ($placeholders) AND `nome_agr` IN ($placeholders2)
                  AND YEAR(`data`) IN (?) AND MONTH(`data`) BETWEEN ? AND ?
                  GROUP BY `cliente`
                  ORDER by `cliente`
                  ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$op1], $tipos, $lista_agregados_array, [$ano, $mesInicial, $mesActual]);
        $rows->execute($params);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                $array[$value->cliente] = (array) $value;
            }
        }

        return $array;
    }

    private function getSQL_producao($cIndustrial, $agora, $final)
    {
        include 'src/Auxiliares/globals.php';

        if (!in_array($cIndustrial, $this->listaProducoes)) {
            return [];
        }

        $query = "SELECT `nome_agre` AS `nome`,
                         MONTH(`data_in`) AS `mes`,
                         ROUND(SUM(`qt` / `baridade`),2) AS `m3`,
                         ROUND(AVG(`valor_in_ton` * `baridade`),2) AS `pu`
                  FROM `$cIndustrial`
                  LEFT JOIN `agregados`
                  ON `agr_id` = `cod_agr`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `cod_agr`
                  JOIN `baridades`
                  ON `cod_agr` = `agregado_id`
                  WHERE `nome_agre` IN ($lista_agregados) AND YEAR(`data_in`) IN (?)
                  AND MONTH(`data_in`) BETWEEN ? and ?
                  GROUP BY `nome_agre`
                  ORDER by `nome_agre`
                 ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$ano, $agora, $final]);
        $rows->execute($params);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                foreach ($lista_array_agregados as $agregado => $real) {
                    if ($value->nome === $agregado) {
                        $array[$agregado] = [
                            'nome' => $value->nome,
                            'm3' => $value->m3,
                            'pu' => $value->pu,
                        ];
                    }
                }
            }
        }

        return $array;
    }

    private function getSQL_custos($mesInicial, $mesActual)
    {
        include 'src/Auxiliares/globals.php';

        $query = 'SELECT `superfamilia`,
                         `cind`,
                         SUM(`valor`) AS valor,
                         MONTH(`data`) AS mes
                  FROM `custos`
                  LEFT JOIN `familias`
                  ON custos.familia = familias.familia
                  WHERE  YEAR(custos.data) IN (?) AND MONTH(custos.data)
                    BETWEEN ? AND ?
                  GROUP BY superfamilia, cind
                  ';

        $rows = $this->db->prepare($query);
        $rows->execute([$ano, $mesInicial, $mesActual]);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            return $vars['row'];
        } else {
            $vars['row'] = 0;

            return $vars['row'];
        }
    }

    private function getSQL_MaoDeObra($mesInicial, $mesActual, $tipo)
    {
        include 'src/Auxiliares/globals.php';

        $anoAnalise = 'ano_'.$ano;

        $query = 'SELECT MONTH(`data`) AS mes,
                         `cind`,
	                     SUM(`h_normais` * "'.$anoAnalise.'" + `h_extras` * "'.$anoAnalise.'") AS `custo_un`
                  FROM `folha_ponto`
                  LEFT JOIN `colaboradores`
                  ON `num_mec` = `n_mec`
                  WHERE  YEAR(`data`) IN (?) AND MONTH(`data`)
                         BETWEEN ? AND ? AND
                         `nacional` = ?
                  GROUP BY cind';

        $rows = $this->db->prepare($query);
        $rows->execute([$ano, $mesInicial, $mesActual, $tipo]);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            return $vars['row'];
        } else {
            $vars['row'] = 0;

            return $vars['row'];
        }
    }

    private function getProducao($cIndustrial, $mesA, $mesF)
    {
        include 'src/Auxiliares/globals.php';

        $cindus = 'producoes_'.$cIndustrial;

        if (!in_array($cindus, $this->listaProducoes)) {
            return [];
        }

        $producao = $this->getSql_producao($cindus, $mesA, $mesF);
        $arrayProducoes = array('producao' => $cIndustrial,
                                );
        // Normalizar os nomes dos agregados
        // foreach ($producao as $key => $value) {
        //     $producao[$key]['nome'] = $key;
        // }

        // Inicializar os contentores de valores a inserir no quadro
        foreach ($arrayProducoes as $key => $value) {
            foreach ($lista_array_agregados as $key2 => $value2) {
                ${$value}[$key2] = array();
            }
        }

        // Criar novo array com todas britas
        foreach ($lista_array_agregados as $key => $value) {
            foreach ($arrayProducoes as $key2 => $value2) {
                if (isset(${$key2}[$key]['nome'])) {
                    if ($key === ${$key2}[$key]['nome']) {
                        ${$value2}[$key] = ${$key2}[$key];
                    } else {
                        $temp[0] = array('nome' => $key, 'm3' => 0, 'pu' => 0);
                        ${$value2}[$key] = $temp[0];
                    }
                } else {
                    $temp[0] = array('nome' => $key, 'm3' => 0, 'pu' => 0);
                    ${$value2}[$key] = $temp[0];
                }
            }
        }

        return ${$cIndustrial};
    }

    private function getFornecimento($cIndustrial, $op1, $op2, $mesActual, $mesFinal)
    {
        include 'src/Auxiliares/globals.php';

        $fornecimento = $this->getSQL('importacao'.'_'.$cIndustrial, $op1, $op2, $mesActual, $mesFinal);

        $arrayFornecimentos = array('fornecimento' => $cIndustrial,
                                );

        // Normalizar os nomes dos agregados
        foreach ($fornecimento as $key => $value) {
            $fornecimento[$key]['nome'] = $key;
        }

        // Inicializar os contentores de valores a inserir no quadro
        foreach ($arrayFornecimentos as $key => $value) {
            foreach ($lista_array_agregados as $key2 => $value2) {
                ${$value}[$key2] = array();
            }
        }

        // Criar novo array com todas britas
        foreach ($lista_array_agregados as $key => $value) {
            foreach ($arrayFornecimentos as $key2 => $value2) {
                if (isset(${$key2}[$key]['nome'])) {
                    if ($key === ${$key2}[$key]['nome']) {
                        ${$value2}[$key] = ${$key2}[$key];
                    } else {
                        $temp[0] = array('nome' => $key, 'm3' => 0);
                        ${$value2}[$key] = $temp[0];
                    }
                } else {
                    $temp[0] = array('nome' => $key, 'm3' => 0, 'total' => 0);
                    ${$value2}[$key] = $temp[0];
                }
            }
        }

        return ${$cIndustrial};
    }

    private function getFornecimento_clientes($cIndustrial, $op1, $op2, $mesActual, $mesFinal)
    {
        include 'src/Auxiliares/globals.php';

        $fornecimento = $this->getSQL_clientes('importacao'.'_'.$cIndustrial, $op1, $op2, $mesActual, $mesFinal);

        $arrayFornecimentos = array('fornecimento' => $cIndustrial,
                                );

        $cIndustrial = $fornecimento;

        return $cIndustrial;
    }

    private function getPreco($agregado)
    {
        $query = 'SELECT ROUND(`valor_in_ton` * `baridade`,2) AS `valor`
                  FROM `agregados`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_id` = `agr_bar_id`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  WHERE `nome_agre` IN (?)';

        $rows = $this->db->prepare($query);
        $rows->execute([$agregado]);

        if ($rows->rowCount() > 0) {
            $vars = $rows->fetchAll(\PDO::FETCH_OBJ);

            return $vars[0]->valor;
        } else {
            return 0;
        }
    }

    public function dadosMensais($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $mes = $request->getAttribute('item');

        $preco1 = 'valor_in_ton';
        $op1 = 'GTO';
        $op2 = 'SPA';
        $preco2 = 'valor_ex_ton';
        $op3 = 'GR';
        $op4 = 'VD';
        $vars['title'] = 'Resumo Mensal';
        $label1 = 'Quantidade Produzida (m3)';
        $label2 = 'Valor Produzido (USD)';

        foreach ($cisRelatorioMensal as $key => $valor) {
            // Obter a produção no mês em análise
            $producao[$key] = $this->getProducao($key, $mes, $mes);
            // Obter a produção acumulativa até ao mês anterior ao em análise

            $producao_acumulada[$key] = $this->getProducao($key, 1, $mes - 1);

            // Obter a produção acumulativa até ao mês actual
            $producao_actual[$key] = $this->getProducao($key, 1, $mes);

            // Obter o fornecimento interno no mês em análise
            $fInterno[$key] = $this->getFornecimento($key, $op1, $op2, $mes, $mes);

            // Obter o fornecimento interno acumulativo até ao mês anterior ao em análise
            $fInterno_acumulado[$key] = $this->getFornecimento($key, $op1, $op2, 1, $mes - 1);

            // Obter o fornecimento interno acumulativo até ao mês actual
            $fInterno_actual[$key] = $this->getFornecimento($key, $op1, $op2, 1, $mes);

            // Obter o fornecimento externo no mês em análise
            $fExterno[$key] = $this->getFornecimento($key, $op3, $op4, $mes, $mes);

            // Obter o fornecimento externo acumulativo até ao mês anterior ao em análise
            $fExterno_acumulado[$key] = $this->getFornecimento($key, $op3, $op4, 1, $mes - 1);

            // Obter o fornecimento externo acumulativo até ao mês actual
            $fExterno_actual[$key] = $this->getFornecimento($key, $op3, $op4, 1, $mes);
        }

        // Stocks do inicio de cada mês de Fevereiro a Dezembro
        if ($mes == 1) {
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockInicio[$cis][$key] = $stock[$cis][$ano][$key];
                }
            }
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockFim[$cis][$key] = $stockInicio[$cis][$key] +
                                            $producao[$cis][$key]['m3'] -
                                            $fInterno[$cis][$key]['m3'] -
                                            $fExterno[$cis][$key]['m3'];
                }
            }
        } else {
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockInicio[$cis][$key] = $stock[$cis][$ano][$key] +
                                               $producao_acumulada[$cis][$key]['m3'] -
                                               $fInterno_acumulado[$cis][$key]['m3'] -
                                               $fExterno_acumulado[$cis][$key]['m3'];
                }
            }
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockFim[$cis][$key] = $stock[$cis][$ano][$key] +
                                            $producao_actual[$cis][$key]['m3'] -
                                            $fInterno_actual[$cis][$key]['m3'] -
                                            $fExterno_actual[$cis][$key]['m3'];
                }
            }
        }

        // Agrupamento de britas por classes de tamanho
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $stockInicial[$valor]['Pó Pedra'] = $stockInicio[$cis]['Pó de Pedra (Britagem)'] +
                                                $stockInicio[$cis]['Pó de Pedra - Dundo'];

            $stockInicial[$valor]['Brita I'] = $stockInicio[$cis]['Brita I'] +
                                               $stockInicio[$cis]['Brita 3/8'] +
                                               $stockInicio[$cis]['Brita 5/15'] +
                                               $stockInicio[$cis]['Brita 5/19'] +
                                               $stockInicio[$cis]['ABG Brita 10/15 - Dundo'] +
                                               $stockInicio[$cis]['ABG Brita 5/10 - Dundo'];

            $stockInicial[$valor]['Brita II'] = $stockInicio[$cis]['Brita II'] +
                                                $stockInicio[$cis]['ABG Brita 05/25 - Dundo'] +
                                                $stockInicio[$cis]['ABG Brita 10/25 - Dundo'] +
                                                $stockInicio[$cis]['ABG Brita 15/25 - Dundo'] +
                                                $stockInicio[$cis]['Brita 15/25'];

            $stockInicial[$valor]['Brita III'] = $stockInicio[$cis]['Brita III'] +
                                                 $stockInicio[$cis]['ABG Brita 40/60 - Dundo'];

            $stockInicial[$valor]['Tout-venant'] = $stockInicio[$cis]['Tout-Venant'] +
                                                   $stockInicio[$cis]['Tout-Venant - Dundo'];

            $stockInicial[$valor]['P.Desmonte'] = $stockInicio[$cis]['Pedra de Desmonte'] +
                                                  $stockInicio[$cis]['Pedra de Desmonte - Dundo'];

            $stockInicial[$valor]['Rachão'] = $stockInicio[$cis]['Pedra Rachão'] +
                                              $stockInicio[$cis]['Pedra Rachão - Dundo'];
        }

        // Agrupamento de britas por classes de tamanho
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $stockFinal[$valor]['Pó Pedra'] = $stockFim[$cis]['Pó de Pedra (Britagem)'] +
                                                $stockFim[$cis]['Pó de Pedra - Dundo'];

            $stockFinal[$valor]['Brita I'] = $stockFim[$cis]['Brita I'] +
                                               $stockFim[$cis]['Brita 3/8'] +
                                               $stockFim[$cis]['Brita 5/15'] +
                                               $stockFim[$cis]['Brita 5/19'] +
                                               $stockFim[$cis]['ABG Brita 10/15 - Dundo'] +
                                               $stockFim[$cis]['ABG Brita 5/10 - Dundo'];

            $stockFinal[$valor]['Brita II'] = $stockFim[$cis]['Brita II'] +
                                                $stockFim[$cis]['ABG Brita 05/25 - Dundo'] +
                                                $stockFim[$cis]['ABG Brita 10/25 - Dundo'] +
                                                $stockFim[$cis]['ABG Brita 15/25 - Dundo'] +
                                                $stockFim[$cis]['Brita 15/25'];

            $stockFinal[$valor]['Brita III'] = $stockFim[$cis]['Brita III'] +
                                                 $stockFim[$cis]['ABG Brita 40/60 - Dundo'];

            $stockFinal[$valor]['Tout-venant'] = $stockFim[$cis]['Tout-Venant'] +
                                                   $stockFim[$cis]['Tout-Venant - Dundo'];

            $stockFinal[$valor]['P.Desmonte'] = $stockFim[$cis]['Pedra de Desmonte'] +
                                                  $stockFim[$cis]['Pedra de Desmonte - Dundo'];

            $stockFinal[$valor]['Rachão'] = $stockFim[$cis]['Pedra Rachão'] +
                                              $stockFim[$cis]['Pedra Rachão - Dundo'];
        }

        // Agrupamento de britas nos valores de produção
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupoProducao[$valor]['Pó Pedra'] = $producao[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $producao[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupoProducao[$valor]['Brita I'] = $producao[$cis]['Brita I']['m3'] +
                                                $producao[$cis]['Brita 3/8']['m3'] +
                                                $producao[$cis]['Brita 5/15']['m3'] +
                                                $producao[$cis]['Brita 5/19']['m3'] +
                                                $producao[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $producao[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupoProducao[$valor]['Brita II'] = $producao[$cis]['Brita II']['m3'] +
                                                 $producao[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $producao[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $producao[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $producao[$cis]['Brita 15/25']['m3'];

            $grupoProducao[$valor]['Brita III'] = $producao[$cis]['Brita III']['m3'] +
                                                  $producao[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupoProducao[$valor]['Tout-venant'] = $producao[$cis]['Tout-Venant']['m3'] +
                                                    $producao[$cis]['Tout-Venant - Dundo']['m3'];

            $grupoProducao[$valor]['P.Desmonte'] = $producao[$cis]['Pedra de Desmonte']['m3'] +
                                                   $producao[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupoProducao[$valor]['Rachão'] = $producao[$cis]['Pedra Rachão']['m3'] +
                                               $producao[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Agrupamento de britas nos valores de fornecimento interno
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupofInterno[$valor]['Pó Pedra'] = $fInterno[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $fInterno[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupofInterno[$valor]['Brita I'] = $fInterno[$cis]['Brita I']['m3'] +
                                                $fInterno[$cis]['Brita 3/8']['m3'] +
                                                $fInterno[$cis]['Brita 5/15']['m3'] +
                                                $fInterno[$cis]['Brita 5/19']['m3'] +
                                                $fInterno[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $fInterno[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupofInterno[$valor]['Brita II'] = $fInterno[$cis]['Brita II']['m3'] +
                                                 $fInterno[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $fInterno[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $fInterno[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $fInterno[$cis]['Brita 15/25']['m3'];

            $grupofInterno[$valor]['Brita III'] = $fInterno[$cis]['Brita III']['m3'] +
                                                  $fInterno[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupofInterno[$valor]['Tout-venant'] = $fInterno[$cis]['Tout-Venant']['m3'] +
                                                    $fInterno[$cis]['Tout-Venant - Dundo']['m3'];

            $grupofInterno[$valor]['P.Desmonte'] = $fInterno[$cis]['Pedra de Desmonte']['m3'] +
                                                   $fInterno[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupofInterno[$valor]['Rachão'] = $fInterno[$cis]['Pedra Rachão']['m3'] +
                                               $fInterno[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Agrupamento de britas nos valores de fornecimento externo
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupofExterno[$valor]['Pó Pedra'] = $fExterno[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $fExterno[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupofExterno[$valor]['Brita I'] = $fExterno[$cis]['Brita I']['m3'] +
                                                $fExterno[$cis]['Brita 3/8']['m3'] +
                                                $fExterno[$cis]['Brita 5/15']['m3'] +
                                                $fExterno[$cis]['Brita 5/19']['m3'] +
                                                $fExterno[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $fExterno[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupofExterno[$valor]['Brita II'] = $fExterno[$cis]['Brita II']['m3'] +
                                                 $fExterno[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $fExterno[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $fExterno[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $fExterno[$cis]['Brita 15/25']['m3'];

            $grupofExterno[$valor]['Brita III'] = $fExterno[$cis]['Brita III']['m3'] +
                                                  $fExterno[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupofExterno[$valor]['Tout-venant'] = $fExterno[$cis]['Tout-Venant']['m3'] +
                                                    $fExterno[$cis]['Tout-Venant - Dundo']['m3'];

            $grupofExterno[$valor]['P.Desmonte'] = $fExterno[$cis]['Pedra de Desmonte']['m3'] +
                                                   $fExterno[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupofExterno[$valor]['Rachão'] = $fExterno[$cis]['Pedra Rachão']['m3'] +
                                               $fExterno[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Somatório do rodapé
        foreach ($drBritas as $brita => $nome) {
            $rodapeStockInicial[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $rodapeStockInicial[$brita] += $stockInicial[$value][$brita];
            }
        }

        foreach ($drBritas as $brita => $nome) {
            $rodapeStockFinal[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $rodapeStockFinal[$brita] += $stockFinal[$value][$brita];
            }
        }

        foreach ($drBritas as $brita => $nome) {
            $rodapeGrupoProducao[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $rodapeGrupoProducao[$brita] += $grupoProducao[$value][$brita];
            }
        }

        foreach ($drBritas as $brita => $nome) {
            $rodapeGrupoFInterno[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $rodapeGrupoFInterno[$brita] += $grupofInterno[$value][$brita];
            }
        }

        foreach ($drBritas as $brita => $nome) {
            $rodapeGrupoFExterno[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $rodapeGrupoFExterno[$brita] += $grupofExterno[$value][$brita];
            }
        }

        // somatório por linhas
        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaStockInicial[$value] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaStockInicial[$value] += $stockInicial[$value][$brita];
            }
        }

        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaProducao[$value] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaProducao[$value] += $grupoProducao[$value][$brita];
            }
        }
        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaFInterno[$value] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaFInterno[$value] += $grupofInterno[$value][$brita];
            }
        }

        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaFExterno[$value] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaFExterno[$value] += $grupofExterno[$value][$brita];
            }
        }

        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaStockFinal[$value] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaStockFinal[$value] += $stockFinal[$value][$brita];
            }
        }

        // Somatório do rodapé
        $somaRodapeStockInicial = 0;
        $somaRodapeStockFinal = 0;
        $somaRodapeProducao = 0;
        $somaRodapefInterno = 0;
        $somaRodapefExterno = 0;
        foreach ($drBritas as $brita => $value) {
            $somaRodapeStockInicial += $rodapeStockInicial[$brita];
            $somaRodapeStockFinal += $rodapeStockFinal[$brita];
            $somaRodapeProducao += $rodapeGrupoProducao[$brita];
            $somaRodapefInterno += $rodapeGrupoFInterno[$brita];
            $somaRodapefExterno += $rodapeGrupoFExterno[$brita];
        }

        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['page'] = 'dadosmensais';
        $vars['agregados_nome'] = $agregados_nome;
        $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
        $vars['stockFinal'] = $stockFinal;
        $vars['stockInicial'] = $stockInicial;
        $vars['drBritas'] = $drBritas;
        $vars['producao'] = $grupoProducao;
        $vars['fInterno'] = $grupofInterno;
        $vars['fExterno'] = $grupofExterno;
        $vars['rodapeProducao'] = $rodapeGrupoProducao;
        $vars['rodapefInterno'] = $rodapeGrupoFInterno;
        $vars['rodapefExterno'] = $rodapeGrupoFExterno;
        $vars['rodapeStockFinal'] = $rodapeStockFinal;
        $vars['rodapeStockInicial'] = $rodapeStockInicial;
        $vars['somaProducao'] = $somaProducao;
        $vars['somafInterno'] = $somaFInterno;
        $vars['somafExterno'] = $somaFExterno;
        $vars['somaStockFinal'] = $somaStockFinal;
        $vars['somaStockInicial'] = $somaStockInicial;
        $vars['somaRodapeProducao'] = $somaRodapeProducao;
        $vars['somaRodapefInterno'] = $somaRodapefInterno;
        $vars['somaRodapefExterno'] = $somaRodapefExterno;
        $vars['somaRodapeStockFinal'] = $somaRodapeStockFinal;
        $vars['somaRodapeStockInicial'] = $somaRodapeStockInicial;

        // gráfico
        $vars['totalProducao'] = $totalProducao;
        $vars['titlo_grafico'] = 'Produção por C.Industrial';

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    public function dadosAcumulados($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $mes = $request->getAttribute('item');

        $preco1 = 'valor_in_ton';
        $op1 = 'GTO';
        $op2 = 'SPA';
        $preco2 = 'valor_ex_ton';
        $op3 = 'GR';
        $op4 = 'VD';
        $vars['title'] = 'Resumo Mensal';
        $label1 = 'Quantidade Produzida (m3)';
        $label2 = 'Valor Produzido (USD)';

        foreach ($cisRelatorioMensal as $key => $value) {
            // Obter a produção no mês em análise
            $producao[$key] = $this->getProducao($key, $mes, $mes);

            // Obter a produção acumulativa até ao mês anterior ao em análise
            $producao_acumulada[$key] = $this->getProducao($key, 1, $mes - 1);

            // Obter a produção acumulativa até ao mês actual
            $producao_actual[$key] = $this->getProducao($key, 1, $mes);

            // Obter o fornecimento interno no mês em análise
            $fInterno[$key] = $this->getFornecimento($key, $op1, $op2, $mes, $mes);

            // Obter o fornecimento interno acumulativo até ao mês anterior ao em análise
            $fInterno_acumulado[$key] = $this->getFornecimento($key, $op1, $op2, 1, $mes - 1);

            // Obter o fornecimento interno acumulativo até ao mês actual
            $fInterno_actual[$key] = $this->getFornecimento($key, $op1, $op2, 1, $mes);

            // Obter o fornecimento externo no mês em análise
            $fExterno[$key] = $this->getFornecimento($key, $op3, $op4, $mes, $mes);

            // Obter o fornecimento externo acumulativo até ao mês anterior ao em análise
            $fExterno_acumulado[$key] = $this->getFornecimento($key, $op3, $op4, 1, $mes - 1);

            // Obter o fornecimento externo acumulativo até ao mês actual
            $fExterno_actual[$key] = $this->getFornecimento($key, $op3, $op4, 1, $mes);
        }

        // Stocks do inicio de cada mês de Fevereiro a Dezembro
        if ($mes == 1) {
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockInicio[$cis][$key] = $stock[$cis][$ano][$key];
                }
            }
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockFim[$cis][$key] = $stockInicio[$cis][$key] +
                                            $producao[$cis][$key]['m3'] -
                                            $fInterno[$cis][$key]['m3'] -
                                            $fExterno[$cis][$key]['m3'];
                }
            }
        } else {
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockInicio[$cis][$key] = $stock[$cis][$ano][$key] +
                                               $producao_acumulada[$cis][$key]['m3'] -
                                               $fInterno_acumulado[$cis][$key]['m3'] -
                                               $fExterno_acumulado[$cis][$key]['m3'];
                }
            }
            foreach ($cisRelatorioMensal as $cis => $valor) {
                foreach ($lista_array_agregados as $key => $value) {
                    $stockFim[$cis][$key] = $stock[$cis][$ano][$key] +
                                            $producao_actual[$cis][$key]['m3'] -
                                            $fInterno_actual[$cis][$key]['m3'] -
                                            $fExterno_actual[$cis][$key]['m3'];
                }
            }
        }

        // Agrupamento de britas por classes de tamanho
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $stockInicial[$valor]['Pó Pedra'] = $stock[$cis][$ano]['Pó de Pedra (Britagem)'] +
                                                $stock[$cis][$ano]['Pó de Pedra - Dundo'];

            $stockInicial[$valor]['Brita I'] = $stock[$cis][$ano]['Brita I'] +
                                               $stock[$cis][$ano]['Brita 3/8'] +
                                               $stock[$cis][$ano]['Brita 5/15'] +
                                               $stock[$cis][$ano]['Brita 5/19'] +
                                               $stock[$cis][$ano]['ABG Brita 10/15 - Dundo'] +
                                               $stock[$cis][$ano]['ABG Brita 5/10 - Dundo'];

            $stockInicial[$valor]['Brita II'] = $stock[$cis][$ano]['Brita II'] +
                                                $stock[$cis][$ano]['ABG Brita 05/25 - Dundo'] +
                                                $stock[$cis][$ano]['ABG Brita 10/25 - Dundo'] +
                                                $stock[$cis][$ano]['ABG Brita 15/25 - Dundo'] +
                                                $stock[$cis][$ano]['Brita 15/25'];

            $stockInicial[$valor]['Brita III'] = $stock[$cis][$ano]['Brita III'] +
                                                 $stock[$cis][$ano]['ABG Brita 40/60 - Dundo'];

            $stockInicial[$valor]['Tout-venant'] = $stock[$cis][$ano]['Tout-Venant'] +
                                                   $stock[$cis][$ano]['Tout-Venant - Dundo'];

            $stockInicial[$valor]['P.Desmonte'] = $stock[$cis][$ano]['Pedra de Desmonte'] +
                                                  $stock[$cis][$ano]['Pedra de Desmonte - Dundo'];

            $stockInicial[$valor]['Rachão'] = $stock[$cis][$ano]['Pedra Rachão'] +
                                              $stock[$cis][$ano]['Pedra Rachão - Dundo'];
        }

        // Agrupamento de britas por classes de tamanho
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $stockFinal[$valor]['Pó Pedra'] = $stockFim[$cis]['Pó de Pedra (Britagem)'] +
                                                $stockFim[$cis]['Pó de Pedra - Dundo'];

            $stockFinal[$valor]['Brita I'] = $stockFim[$cis]['Brita I'] +
                                               $stockFim[$cis]['Brita 3/8'] +
                                               $stockFim[$cis]['Brita 5/15'] +
                                               $stockFim[$cis]['Brita 5/19'] +
                                               $stockFim[$cis]['ABG Brita 10/15 - Dundo'] +
                                               $stockFim[$cis]['ABG Brita 5/10 - Dundo'];

            $stockFinal[$valor]['Brita II'] = $stockFim[$cis]['Brita II'] +
                                                $stockFim[$cis]['ABG Brita 05/25 - Dundo'] +
                                                $stockFim[$cis]['ABG Brita 10/25 - Dundo'] +
                                                $stockFim[$cis]['ABG Brita 10/25 - Dundo'] +
                                                $stockFim[$cis]['Brita 15/25'];

            $stockFinal[$valor]['Brita III'] = $stockFim[$cis]['Brita III'] +
                                                 $stockFim[$cis]['ABG Brita 40/60 - Dundo'];

            $stockFinal[$valor]['Tout-venant'] = $stockFim[$cis]['Tout-Venant'] +
                                                   $stockFim[$cis]['Tout-Venant - Dundo'];

            $stockFinal[$valor]['P.Desmonte'] = $stockFim[$cis]['Pedra de Desmonte'] +
                                                  $stockFim[$cis]['Pedra de Desmonte - Dundo'];

            $stockFinal[$valor]['Rachão'] = $stockFim[$cis]['Pedra Rachão'] +
                                              $stockFim[$cis]['Pedra Rachão - Dundo'];
        }

        // Agrupamento de britas nos valores de produção
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupoProducao[$valor]['Pó Pedra'] = $producao_actual[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $producao_actual[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupoProducao[$valor]['Brita I'] = $producao_actual[$cis]['Brita I']['m3'] +
                                                $producao_actual[$cis]['Brita 3/8']['m3'] +
                                                $producao_actual[$cis]['Brita 5/15']['m3'] +
                                                $producao_actual[$cis]['Brita 5/19']['m3'] +
                                                $producao_actual[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $producao_actual[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupoProducao[$valor]['Brita II'] = $producao_actual[$cis]['Brita II']['m3'] +
                                                 $producao_actual[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $producao_actual[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $producao_actual[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $producao_actual[$cis]['Brita 15/25']['m3'];

            $grupoProducao[$valor]['Brita III'] = $producao_actual[$cis]['Brita III']['m3'] +
                                                  $producao_actual[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupoProducao[$valor]['Tout-venant'] = $producao_actual[$cis]['Tout-Venant']['m3'] +
                                                    $producao_actual[$cis]['Tout-Venant - Dundo']['m3'];

            $grupoProducao[$valor]['P.Desmonte'] = $producao_actual[$cis]['Pedra de Desmonte']['m3'] +
                                                   $producao_actual[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupoProducao[$valor]['Rachão'] = $producao_actual[$cis]['Pedra Rachão']['m3'] +
                                               $producao_actual[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Agrupamento de britas nos valores de fornecimento interno
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupofInterno[$valor]['Pó Pedra'] = $fInterno_actual[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $fInterno_actual[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupofInterno[$valor]['Brita I'] = $fInterno_actual[$cis]['Brita I']['m3'] +
                                                $fInterno_actual[$cis]['Brita 3/8']['m3'] +
                                                $fInterno_actual[$cis]['Brita 5/15']['m3'] +
                                                $fInterno_actual[$cis]['Brita 5/19']['m3'] +
                                                $fInterno_actual[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $fInterno_actual[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupofInterno[$valor]['Brita II'] = $fInterno_actual[$cis]['Brita II']['m3'] +
                                                 $fInterno_actual[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $fInterno_actual[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $fInterno_actual[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $fInterno_actual[$cis]['Brita 15/25']['m3'];

            $grupofInterno[$valor]['Brita III'] = $fInterno_actual[$cis]['Brita III']['m3'] +
                                                  $fInterno_actual[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupofInterno[$valor]['Tout-venant'] = $fInterno_actual[$cis]['Tout-Venant']['m3'] +
                                                    $fInterno_actual[$cis]['Tout-Venant - Dundo']['m3'];

            $grupofInterno[$valor]['P.Desmonte'] = $fInterno_actual[$cis]['Pedra de Desmonte']['m3'] +
                                                   $fInterno_actual[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupofInterno[$valor]['Rachão'] = $fInterno_actual[$cis]['Pedra Rachão']['m3'] +
                                               $fInterno_actual[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Agrupamento de britas nos valores de fornecimento externo
        foreach ($cisRelatorioMensal as $cis => $valor) {
            $grupofExterno[$valor]['Pó Pedra'] = $fExterno_actual[$cis]['Pó de Pedra (Britagem)']['m3'] +
                                                 $fExterno_actual[$cis]['Pó de Pedra - Dundo']['m3'];

            $grupofExterno[$valor]['Brita I'] = $fExterno_actual[$cis]['Brita I']['m3'] +
                                                $fExterno_actual[$cis]['Brita 3/8']['m3'] +
                                                $fExterno_actual[$cis]['Brita 5/15']['m3'] +
                                                $fExterno_actual[$cis]['Brita 5/19']['m3'] +
                                                $fExterno_actual[$cis]['ABG Brita 10/15 - Dundo']['m3'] +
                                                $fExterno_actual[$cis]['ABG Brita 5/10 - Dundo']['m3'];

            $grupofExterno[$valor]['Brita II'] = $fExterno_actual[$cis]['Brita II']['m3'] +
                                                 $fExterno_actual[$cis]['ABG Brita 05/25 - Dundo']['m3'] +
                                                 $fExterno_actual[$cis]['ABG Brita 10/25 - Dundo']['m3'] +
                                                 $fExterno_actual[$cis]['ABG Brita 15/25 - Dundo']['m3'] +
                                                 $fExterno_actual[$cis]['Brita 15/25']['m3'];

            $grupofExterno[$valor]['Brita III'] = $fExterno_actual[$cis]['Brita III']['m3'] +
                                                  $fExterno_actual[$cis]['ABG Brita 40/60 - Dundo']['m3'];

            $grupofExterno[$valor]['Tout-venant'] = $fExterno_actual[$cis]['Tout-Venant']['m3'] +
                                                    $fExterno_actual[$cis]['Tout-Venant - Dundo']['m3'];

            $grupofExterno[$valor]['P.Desmonte'] = $fExterno_actual[$cis]['Pedra de Desmonte']['m3'] +
                                                   $fExterno_actual[$cis]['Pedra de Desmonte - Dundo']['m3'];

            $grupofExterno[$valor]['Rachão'] = $fExterno_actual[$cis]['Pedra Rachão']['m3'] +
                                               $fExterno_actual[$cis]['Pedra Rachão - Dundo']['m3'];
        }

        // Somatório do rodapé
        foreach ($drBritas as $brita => $value) {
            $rodapeStockInicial[$brita] = 0;
            $rodapeStockFinal[$brita] = 0;
            $rodapeProducao[$brita] = 0;
            $rodapeFInterno[$brita] = 0;
            $rodapeFExterno[$brita] = 0;
            foreach ($cisRelatorioMensal as $ci => $codigo) {
                $rodapeStockInicial[$brita] += $stockInicial[$codigo][$brita];
                $rodapeStockFinal[$brita] += $stockFinal[$codigo][$brita];
                $rodapeProducao[$brita] += $grupoProducao[$codigo][$brita];
                $rodapeFInterno[$brita] += $grupofInterno[$codigo][$brita];
                $rodapeFExterno[$brita] += $grupofExterno[$codigo][$brita];
            }
        }

        // Somatório de linhas do rodapé
        foreach ($cisRelatorioMensal as $ci => $codigo) {
            $somaRodapeStockInicial = 0;
            $somaRodapeStockFinal = 0;
            $somaRodapeProducao = 0;
            $somaRodapeFInterno = 0;
            $somaRodapeFExterno = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaRodapeStockInicial += $rodapeStockInicial[$brita];
                $somaRodapeStockFinal += $rodapeStockFinal[$brita];
                $somaRodapeProducao += $rodapeProducao[$brita];
                $somaRodapeFInterno += $rodapeFInterno[$brita];
                $somaRodapeFExterno += $rodapeFExterno[$brita];
            }
        }

        foreach ($cisRelatorioMensal as $ci => $codigo) {
            $somaStockInicial[$ci] = 0;
            $somaStockFinal[$ci] = 0;
            $somaProducao[$ci] = 0;
            $somaFInterno[$ci] = 0;
            $somaFExterno[$ci] = 0;
            foreach ($drBritas as $brita => $nome) {
                $somaStockInicial[$ci] += $stockInicial[$codigo][$brita];
                $somaStockFinal[$ci] += $stockFinal[$codigo][$brita];
                $somaProducao[$ci] += $grupoProducao[$codigo][$brita];
                $somaFInterno[$ci] += $grupofInterno[$codigo][$brita];
                $somaFExterno[$ci] += $grupofExterno[$codigo][$brita];
            }
        }

        $vars['title'] = 'Resumo Mensal Acumulado';
        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['page'] = 'dadosacumulados';
        $vars['agregados_nome'] = $agregados_nome;
        $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
        $vars['stockFinal'] = $stockFinal;
        $vars['stockInicial'] = $stockInicial;
        $vars['drBritas'] = $drBritas;
        $vars['producao'] = $grupoProducao;
        $vars['fInterno'] = $grupofInterno;
        $vars['fExterno'] = $grupofExterno;
        $vars['stock'] = $stock;
        //rodapé
        $vars['rodapeStockInicial'] = $rodapeStockInicial;
        $vars['rodapeStockFinal'] = $rodapeStockFinal;
        $vars['rodapeProducao'] = $rodapeProducao;
        $vars['rodapeFInterno'] = $rodapeFInterno;
        $vars['rodapeFExterno'] = $rodapeFExterno;
        $vars['somaRodapeStockInicial'] = $somaRodapeStockInicial;
        $vars['somaRodapeStockFinal'] = $somaRodapeStockFinal;
        $vars['somaRodapeProducao'] = $somaRodapeProducao;
        $vars['somaRodapeFInterno'] = $somaRodapeFInterno;
        $vars['somaRodapeFExterno'] = $somaRodapeFExterno;
        // Soma linhas
        $vars['somaStockInicial'] = $somaStockInicial;
        $vars['somaStockFinal'] = $somaStockFinal;
        $vars['somaProducao'] = $somaProducao;
        $vars['somaFInterno'] = $somaFInterno;
        $vars['somaFExterno'] = $somaFExterno;

        // gráfico

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    public function custos_grafico($mesActual)
    {
        include 'src/Auxiliares/globals.php';

        $mes = $mesActual;

        $custosAcumulados = $this->getSQL_custos(1, $mes);
        $custosMensais = $this->getSQL_custos($mes, $mes);

        $arimba_mensal = array('Materiais' => 0,
                               'Ferramentas' => 0,
                               'Expatriados' => 0,
                               'Nacionais' => 0,
                               'Equipamento' => 0,
                               'Transportes' => 0,
                               'Serviços internos' => 0,
                               'Serviços externos' => 0,
                               'Outros custos internos' => 0,
                               'Outros custos externos' => 0,
                               'total' => 0,
                              );

        $arimba_acumulado = array('Materiais' => 0,
                                  'Ferramentas' => 0,
                                  'Expatriados' => 0,
                                  'Nacionais' => 0,
                                  'Equipamento' => 0,
                                  'Transportes' => 0,
                                  'Serviços internos' => 0,
                                  'Serviços externos' => 0,
                                  'Outros custos internos' => 0,
                                  'Outros custos externos' => 0,
                                  'total' => 0,
                                 );

        $caraculo_mensal = $arimba_mensal;
        $cassosso_mensal = $arimba_mensal;

        $caraculo_acumulado = $arimba_acumulado;
        $cassosso_acumulado = $arimba_acumulado;

        if ($custosMensais) {
            // introduzir dados mensais de custos
            foreach ($custosMensais as $key => $value) {
                if ($value->cind == 120401) {
                    if (in_array($value->superfamilia, $arimba_mensal)) {
                        $arimba_mensal[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120402) {
                    if (in_array($value->superfamilia, $caraculo_mensal)) {
                        $caraculo_mensal[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120403) {
                    if (in_array($value->superfamilia, $cassosso_mensal)) {
                        $cassosso_mensal[$value->superfamilia] = $value->valor;
                    }
                }
            }

            // Cálculo do total de custos por centro industrial
            foreach ($arimba_mensal as $key => $value) {
                $arimba_mensal['total'] += $value;
            }

            // Cálculo do total de custos por centro industrial
            foreach ($caraculo_mensal as $key => $value) {
                $caraculo_mensal['total'] += $value;
            }

            // Cálculo do total de custos por centro industrial
            foreach ($cassosso_mensal as $key => $value) {
                $cassosso_mensal['total'] += $value;
            }

            // Cálculo dos totais por superfamilia de custos
            foreach ($arimba_mensal as $key => $value) {
                $totalGeral_mensal[$key] = $arimba_mensal[$key] + $caraculo_mensal[$key] + $cassosso_mensal[$key];
            }
        }

        if ($custosAcumulados) {
            // introduzir dados mensais acumulados de custos
            foreach ($custosAcumulados as $key => $value) {
                if ($value->cind == 120401) {
                    if (in_array($value->superfamilia, $arimba_acumulado)) {
                        $arimba_acumulado[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120402) {
                    if (in_array($value->superfamilia, $caraculo_acumulado)) {
                        $caraculo_acumulado[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120403) {
                    if (in_array($value->superfamilia, $cassosso_acumulado)) {
                        $cassosso_acumulado[$value->superfamilia] = $value->valor;
                    }
                }
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($arimba_acumulado as $key => $value) {
                $arimba_acumulado['total'] += $value;
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($caraculo_acumulado as $key => $value) {
                $caraculo_acumulado['total'] += $value;
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($cassosso_acumulado as $key => $value) {
                $cassosso_acumulado['total'] += $value;
            }

            // Cálculo dos totais por superfamilia de custos
            foreach ($arimba_acumulado as $key => $value) {
                $totalGeral_acumulado[$key] = $arimba_acumulado[$key] + $caraculo_acumulado[$key] + $cassosso_acumulado[$key];
            }
        }

        $vars['arimba_mensal'] = $arimba_mensal;
        $vars['caraculo_mensal'] = $caraculo_mensal;
        $vars['cassosso_mensal'] = $cassosso_mensal;
        $vars['arimba_acumulado'] = $arimba_acumulado;
        $vars['caraculo_acumulado'] = $caraculo_acumulado;
        $vars['cassosso_acumulado'] = $cassosso_acumulado;

        return $vars;
    }

    public function custos($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

        $mes = $request->getAttribute('item');

        $custosAcumulados = $this->getSQL_custos(1, $mes);
        $custosMensais = $this->getSQL_custos($mes, $mes);

        $arimba_mensal = array('Materiais' => 0,
                               'Ferramentas' => 0,
                               'Expatriados' => 0,
                               'Nacionais' => 0,
                               'Equipamento' => 0,
                               'Transportes' => 0,
                               'Serviços internos' => 0,
                               'Serviços externos' => 0,
                               'Outros custos internos' => 0,
                               'Outros custos externos' => 0,
                               'total' => 0,
                              );

        $arimba_acumulado = $arimba_mensal;
        $caraculo_mensal = $arimba_mensal;
        $cassosso_mensal = $arimba_mensal;
        $caraculo_acumulado = $arimba_mensal;
        $cassosso_acumulado = $arimba_mensal;

        if ($custosMensais) {
            // introduzir dados mensais de custos
            foreach ($custosMensais as $key => $value) {
                if ($value->cind == 120401) {
                    if (in_array($value->superfamilia, $arimba_mensal)) {
                        $arimba_mensal[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120402) {
                    if (in_array($value->superfamilia, $caraculo_mensal)) {
                        $caraculo_mensal[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120403) {
                    if (in_array($value->superfamilia, $cassosso_mensal)) {
                        $cassosso_mensal[$value->superfamilia] = $value->valor;
                    }
                }
            }

            // Adicionar o custo com mão de obra nacional (a fazer - Possibilitar
            // entrada de valores sem que haja de outras familias)
            $moNacionalMensal = $this->getSQL_MaoDeObra($mes, $mes, 'N');

            if (!empty($moNacionalMensal)) {
                foreach ($moNacionalMensal as $key => $value) {
                    if ($value->cind == 120401) {
                        $arimba_mensal['Nacionais'] += $moNacionalMensal[0]->custo_un;
                    } elseif ($value->cind == 120402) {
                        $caraculo_mensal['Nacionais'] += $moNacionalMensal[0]->custo_un;
                    } elseif ($value->cind == 120403) {
                        $cassosso_mensal['Nacionais'] += $moNacionalMensal[0]->custo_un;
                    }
                }
            }

            $moExpatriadoMensal = $this->getSQL_MaoDeObra($mes, $mes, 'E');

            if (!empty($moExpatriadoMensal)) {
                foreach ($moExpatriadoMensal as $key => $value) {
                    if ($value->cind == 120401) {
                        $arimba_mensal['Expatriados'] += $value->custo_un;
                    } elseif ($value->cind == 120402) {
                        $caraculo_mensal['Expatriados'] += $value->custo_un;
                    } elseif ($value->cind == 120403) {
                        $cassosso_mensal['Expatriados'] += $value->custo_un;
                    }
                }
            }

            // Cálculo do total de custos por centro industrial
            foreach ($arimba_mensal as $key => $value) {
                $arimba_mensal['total'] += $value;
            }

            // Cálculo do total de custos por centro industrial
            foreach ($caraculo_mensal as $key => $value) {
                $caraculo_mensal['total'] += $value;
            }

            // Cálculo do total de custos por centro industrial
            foreach ($cassosso_mensal as $key => $value) {
                $cassosso_mensal['total'] += $value;
            }

            // Cálculo dos totais por superfamilia de custos
            foreach ($arimba_mensal as $key => $value) {
                $totalGeral_mensal[$key] = $arimba_mensal[$key] + $caraculo_mensal[$key] + $cassosso_mensal[$key];
            }
        }

        if ($custosAcumulados) {
            // introduzir dados mensais acumulados de custos
            foreach ($custosAcumulados as $key => $value) {
                if ($value->cind == 120401) {
                    if (in_array($value->superfamilia, $arimba_acumulado)) {
                        $arimba_acumulado[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120402) {
                    if (in_array($value->superfamilia, $caraculo_acumulado)) {
                        $caraculo_acumulado[$value->superfamilia] = $value->valor;
                    }
                } elseif ($value->cind == 120403) {
                    if (in_array($value->superfamilia, $cassosso_acumulado)) {
                        $cassosso_acumulado[$value->superfamilia] = $value->valor;
                    }
                }
            }

            // Adicionar o custo com mão de obra nacional Acumulada
            $moNacionalAcumulada = $this->getSQL_MaoDeObra(1, $mes, 'N');

            if (!empty($moNacionalAcumulada)) {
                foreach ($moNacionalAcumulada as $key => $value) {
                    if ($value->cind == 120401) {
                        $arimba_acumulado['Nacionais'] += $value->custo_un;
                    } elseif ($value->cind == 120402) {
                        $caraculo_acumulado['Nacionais'] += $value->custo_un;
                    } elseif ($value->cind == 120403) {
                        $cassosso_acumulado['Nacionais'] += $value->custo_un;
                    }
                }
            }

            // Adicionar o custo com mão de obra expatriada Acumulada
            $moExpatriadaAcumulada = $this->getSQL_MaoDeObra(1, $mes, 'E');

            if (!empty($moExpatriadaAcumulada)) {
                foreach ($moExpatriadaAcumulada as $key => $value) {
                    if ($value->cind == 120401) {
                        $arimba_acumulado['Expatriados'] += $value->custo_un;
                    } elseif ($value->cind == 120402) {
                        $caraculo_acumulado['Expatriados'] += $value->custo_un;
                    } elseif ($value->cind == 120403) {
                        $cassosso_acumulado['Expatriados'] += $value->custo_un;
                    }
                }
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($arimba_acumulado as $key => $value) {
                $arimba_acumulado['total'] += $value;
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($caraculo_acumulado as $key => $value) {
                $caraculo_acumulado['total'] += $value;
            }

            // Cálculo do total de custos acumulados por centro industrial
            foreach ($cassosso_acumulado as $key => $value) {
                $cassosso_acumulado['total'] += $value;
            }

            // Cálculo dos totais por superfamilia de custos
            foreach ($arimba_acumulado as $key => $value) {
                $totalGeral_acumulado[$key] = $arimba_acumulado[$key] +
                                              $caraculo_acumulado[$key] +
                                              $cassosso_acumulado[$key];
            }
        }

        for ($i = 1; $i <= $mes; ++$i) {
            $dadosGrafico[$i] = $this->custos_grafico($i);
        }

        for ($i = 1; $i <= $mes; ++$i) {
            $arimbaGraficoMensal[] = $dadosGrafico[$i]['arimba_mensal']['total'];
            $caraculoGraficoMensal[] = $dadosGrafico[$i]['caraculo_mensal']['total'];
            $cassossoGraficoMensal[] = $dadosGrafico[$i]['cassosso_mensal']['total'];
            $arimbaGraficoAcumulado[] = $dadosGrafico[$i]['arimba_acumulado']['total'];
            $caraculoGraficoAcumulado[] = $dadosGrafico[$i]['caraculo_acumulado']['total'];
            $cassossoGraficoAcumulado[] = $dadosGrafico[$i]['cassosso_acumulado']['total'];
        }

        // foreach ($dadosGrafico as $key => $value) {
        //     foreach ($value as $ci => $valores) {
        //         if ($ci == 'arimba_mensal') {
        //             array_push($arimbaGrafico, $valores['total']) ;
        //         }
        //     }
        // }

        $vars['page'] = 'custos';
        $vars['title'] = 'Custos Operacionais';
        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['arimba_mensal'] = $arimba_mensal;
        $vars['caraculo_mensal'] = $caraculo_mensal;
        $vars['cassosso_mensal'] = $cassosso_mensal;
        $vars['arimba_acumulado'] = $arimba_acumulado;
        $vars['caraculo_acumulado'] = $caraculo_acumulado;
        $vars['cassosso_acumulado'] = $cassosso_acumulado;
        $vars['totalGeral_mensal'] = $totalGeral_mensal;
        $vars['totalGeral_acumulado'] = $totalGeral_acumulado;
        // dados gráfico
        $vars['arimbaGraficoMensal'] = $arimbaGraficoMensal;
        $vars['caraculoGraficoMensal'] = $caraculoGraficoMensal;
        $vars['cassossoGraficoMensal'] = $cassossoGraficoMensal;
        $vars['arimbaGraficoAcumulado'] = $arimbaGraficoAcumulado;
        $vars['caraculoGraficoAcumulado'] = $caraculoGraficoAcumulado;
        $vars['cassossoGraficoAcumulado'] = $cassossoGraficoAcumulado;
        $vars['label1'] = 'arimba';
        $vars['label2'] = 'caraculo';
        $vars['label3'] = 'cassosso';

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    private function get_custos($mes)
    {
        include 'src/Auxiliares/globals.php';

        $tabela = 'ano_'.$ano;

        $query = "SELECT x.cind, SUM(x.valor) as valor,x.mes, x.ano
                  FROM
                      (SELECT `cind`,
                    	   SUM(`valor`) AS valor,
                    	   month(`data`) AS mes,
                         year(`data`) AS ano
                      FROM `custos`
                      LEFT JOIN `familias`
                      ON custos.familia = familias.familia
                      WHERE  YEAR(custos.data) IN (?) AND MONTH(custos.data)
                    	BETWEEN 1 AND ?
                      GROUP BY mes, cind
                      UNION ALL
                      SELECT `cind`,
                             SUM(`h_normais` * $tabela + `h_extras` * $tabela) AS valor,
                             MONTH(`data`) AS mes,
                             year(`data`) AS ano
                      FROM `folha_ponto`
                      LEFT JOIN `colaboradores`
                      ON `num_mec` = `n_mec`
                      WHERE  YEAR(`data`) IN (?)
                      GROUP BY mes, cind) as x
                  GROUP BY x.mes, x.cind
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute([$ano, $mes, $ano]);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $dados = $vars['row'];

            foreach ($dados as $key => $value) {
                foreach ($cisRelatorioMensal as $key1 => $value1) {
                    if ($value->cind === $value1) {
                        $custo[$key1][$value->mes] = $value->valor;
                    }
                }
            }
            foreach ($custo as $key => $value) {
                for ($i = 1; $i < $mes+1; $i++) {
                    if (!isset($value[$i])) {
                        $custo[$key][$i] = 0;
                    }
                }
            }

            return $custo;
        } else {
            $vars['row'] = [];

            return $vars['row'];
        }
    }

    private function get_fornecimento($cIndustrial, $tipo, $tipo2)
    {
        include 'src/Auxiliares/globals.php';

        $cindus = 'importacao_'.$cIndustrial;

        $query = "SELECT ROUND(SUM(
                             CASE
                             WHEN '$tipo' IN ('GTO', 'SPA')
                             THEN `peso` * `valor_in_ton`
                             ELSE `peso` * `valor_ex_ton` * (1-`desco`)
                             END
                         )) AS `total`,
                         SUM(`peso` / `baridade`) AS m3,
                         MONTH(`data`) AS mes
                  FROM $cindus
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agr_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_bar_ton_id` = `agr_id`
                  LEFT JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE  `tipo_doc` IN ('$tipo', '$tipo2') AND `nome_agr` IN ($lista_agregados)
                  AND YEAR(`data`) IN (?) AND MONTH(`data`) BETWEEN 1 AND 12
                  GROUP BY `mes`
                  ORDER by `mes`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute([$ano]);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            return $vars['row'];
        } else {
            $vars['row'] = [];
            return $vars['row'];
        }
    }

    private function get_fornecimentoGlobal($cIndustrial)
    {
        include 'src/Auxiliares/globals.php';

        $cindus = 'importacao_'.$cIndustrial;

        $query = "SELECT SUM(x.total) as total, SUM(x.m3) as m3, x.mes as mes
                  FROM (SELECT SUM(`peso` * `valor_in_ton`) AS total,
                         SUM(`peso` / `baridade`) AS m3,
                         MONTH(`data`) AS mes
                  FROM $cindus
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agr_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_bar_ton_id` = `agr_id`
                  LEFT JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE  `tipo_doc` IN ('GTO','SPA') AND `nome_agr` IN ($lista_agregados)
                  AND YEAR(`data`) IN (?) AND MONTH(`data`) BETWEEN 1 AND 12
                  GROUP BY `mes`
                  UNION ALL
                  SELECT SUM(`peso` * `valor_in_ton`) AS total,
                         SUM(`peso` / `baridade`) AS m3,
                         MONTH(`data`) AS mes
                  FROM $cindus
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agr_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_bar_ton_id` = `agr_id`
                  LEFT JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE  `tipo_doc` IN ('GR', 'VD') AND `nome_agr` IN ($lista_agregados)
                  AND YEAR(`data`) IN (?) AND MONTH(`data`) BETWEEN 1 AND 12
                  GROUP BY `mes`) AS x
                  GROUP BY x.mes
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute([$ano, $ano]);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            return $vars['row'];
        } else {
            $vars['row'] = [];
            return $vars['row'];
        }
    }

    private function get_producao($cIndustrial)
    {
        include 'src/Auxiliares/globals.php';

        // if (!in_array($cIndustrial, $this->listaProducoes)) {
        //     return [];
        // }

        $cindus = 'producoes_'.$cIndustrial;

        $query = "SELECT
                         MONTH(`data_in`) AS `mes`,
                         ROUND(SUM(`qt` / `baridade`),2) AS `m3`,
                         ROUND(SUM(`valor_in_ton` * `qt`),2) AS `total`
                  FROM `$cindus`
                  LEFT JOIN `agregados`
                  ON `agr_id` = `cod_agr`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `cod_agr`
                  JOIN `baridades`
                  ON `cod_agr` = `agregado_id`
                  WHERE `nome_agre` IN ($lista_agregados) AND YEAR(`data_in`) IN (?)
                  AND MONTH(`data_in`) BETWEEN 1 and 12
                  GROUP BY `mes`
                  ORDER by `mes`
                 ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$ano]);
        $rows->execute($params);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            // Separar cada uma das linhas da query, por nome do agregado
            return $vars['row'];
        } else {
            return [];
        }
    }

    private function get_stock($cIndustrial)
    {
        include 'src/Auxiliares/globals.php';

        $cindus = 'producoes_'.$cIndustrial;

        $query = "SELECT
                         MONTH(`data_in`) AS `mes`,
                         ROUND(SUM(`qt` / `baridade`),2) AS `m3`,
                         ROUND(SUM(`valor_in_ton` * `qt`),2) AS `total`
                  FROM `$cindus`
                  LEFT JOIN `agregados`
                  ON `agr_id` = `cod_agr`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `cod_agr`
                  JOIN `baridades`
                  ON `cod_agr` = `agregado_id`
                  WHERE `nome_agre` IN ($lista_agregados) AND YEAR(`data_in`) IN (?)
                  AND MONTH(`data_in`) BETWEEN 1 and 12
                  GROUP BY `mes`
                  ORDER by `mes`
                 ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$ano]);
        $rows->execute($params);

        $array = array();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            // Separar cada uma das linhas da query, por nome do agregado
        }

        return $vars['row'];
    }

    public function operacional($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

        $mes = $request->getAttribute('item');

        // Dados de Custos

        #Custos mensais
        $custos = $this->get_custos($mes);

        #soma dos custos
        foreach ($cisRelatorioMensal as $key => $value) {
            $temp = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $custos[$key][$i];
                $custosTotal[$key][$i] = $temp;
            }
        }

        #Produção por CI
        $temporary = 0;
        foreach ($cisRelatorioMensal as $key => $value) {
            $producao[$key] = $this->get_producao($key);
            if (empty($producao[$key])) {
                $temporary += 1;
            }
        }

        if ($temp >= count($cisRelatorioMensal)) {
            #Preencher meses sem valores
          for ($i = 0; $i < $mes; $i++) {
              foreach ($producao as $key => $value) {
                  foreach ($value as $indice => $valor) {
                      if ($valor->mes === $i+1) {
                          $prodOrdenado[$key][$i+1] = $valor->total;
                          $prodM3Ordenado[$key][$i+1] = $valor->m3;
                      }
                  }
              }
          }
            for ($i = 1; $i < $mes+1; $i++) {
                foreach ($prodOrdenado as $key => $value) {
                    if (empty($value[$i])) {
                        $prodOrdenado[$key][$i] = 0;
                        $prodM3Ordenado[$key][$i] = 0;
                    }
                }
            }

          // Dados Acumulados
          foreach ($prodOrdenado as $key => $value) {
              $temp = 0;
              for ($i = 1; $i < $mes+1; $i++) {
                  $temp += $value[$i];
                  $prodAcumudada[$key][$i] = $temp;
              }
          }
            foreach ($prodM3Ordenado as $key => $value) {
                $temp = 0;
                for ($i = 1; $i < $mes+1; $i++) {
                    $temp += $value[$i];
                }
                $prodM3Acumudada[$key] = $temp;
            }

          // Resultados Operacionais

          foreach ($prodOrdenado as $key => $value) {
              $resOperacional[$key] = $value[$mes] - $custosTotal[$key][$mes];
          }
            foreach ($prodAcumudada as $key => $value) {
                $resOperacionalAc[$key] = $value[$mes] - $custosTotal[$key][$mes];
            }

          # CI Operacional
          foreach ($prodOrdenado as $key => $value) {
              if ($value[$mes] > 0) {
                  $ciOperacional[$key][$mes] = $custos[$key][$mes] / $value[$mes];
              } else {
                  $ciOperacional[$key][$mes] = 0;
              }
          }
            foreach ($prodAcumudada as $key => $value) {
                if ($prodAcumudada > 0) {
                    $ciOperacionalAc[$key] = $custosTotal[$key][$mes] / $value[$mes];
                } else {
                    $ciOperacional[$key] = 0;
                }
            }

            foreach ($cisRelatorioMensal as $key => $value) {
                $tempCustos = 0;
                $tempProd = 0;
                ${$key} = array();
                for ($i = 1; $i < $mes+1; $i++) {
                    if ($prodOrdenado > 0) {
                        $tempCustos += $custos[$key][$i];
                        $tempProd += $prodOrdenado[$key][$i];

                        ${$key}[$i-1] = $tempCustos / $tempProd;
                        if (${$key}[$i-1] > 3) {
                            ${$key}[$i-1] = 3;
                        }
                    } else {
                        ${$key}[$i-1] = 0;
                    }
                }
            }
        } else {
            $vars['page'] = 'resultadooperacional';
            $vars['title'] = 'Resultado Operacional';
            $vars['mes'] = $mes;
            $vars['mes_titulo'] = $lista_meses[$mes - 1];

            return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
        }

        // Fornecimento
        foreach ($cisRelatorioMensal as $key => $value) {
            $fornInterno[$key] = $this->get_fornecimento($key, 'GTO', 'SPA');
        }
        foreach ($cisRelatorioMensal as $key => $value) {
            $fornExterno[$key] = $this->get_fornecimento($key, 'GR', 'VD');
        }

        // Fornecimento
        foreach ($cisRelatorioMensal as $key => $value) {
            $fornecimento[$key] = $this->get_fornecimentoGlobal($key);
        }
        foreach ($fornecimento as $key => $value) {
            foreach ($value as $key2 => $value2) {
                for ($i = 0; $i < $mes; $i++) {
                    $fornOrdenado[$key][$value2->mes] = (array)$value2;
                }
            }
        }

        for ($i = 1; $i < $mes+1; $i++) {
            foreach ($fornOrdenado as $key => $value) {
                if ($fornOrdenado[$key][$i] === null) {
                    $fornOrdenado[$key][$i] = array(
                      'total' => 0,
                      'm3' => 0,
                      'mes' => $i
                    );
                }
            }
        }

        # Fornecimento Acumulado
        foreach ($fornOrdenado as $key => $value) {
            $temp = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $value[$i]['total'];
                $fornOrdenadoAc[$key][$i] = $temp;
            }
        }

        # Valor stock inicio do ano
        foreach ($cisRelatorioMensal as $key => $value) {
            $valorStockJaneiro[$key] = 0;
            foreach ($stock[$key][$ano] as $agr => $qt) {
                $valorStockJaneiro[$key] += $qt * $this->getPreco($agr);
            }
        }
        foreach ($cisRelatorioMensal as $key => $value) {
            for ($i = 1; $i < $mes+1; $i++) {
                $stocki[$key][$i-1] = $valorStockJaneiro[$key] + $prodAcumudada[$key][$i] - $fornOrdenadoAc[$key][$i];
            }
        }

        # Diferença de stock
        foreach ($cisRelatorioMensal as $key => $value) {
            $diffStock[$key][1] = $stocki[$key][0] - $valorStockJaneiro[$key];
        }
        foreach ($stocki as $key => $value) {
            $diffStockAc[$key] = $value[$mes-1] - $valorStockJaneiro[$key];
            for ($i = 1; $i < $mes; $i++) {
                $diffStock[$key][$i+1] = $value[$i] - $value[$i-1];
            }
        }

        // Rodapés - Mensais

        # Produção
        $rodapeProdM3 = 0;
        foreach ($prodM3Ordenado as $key => $value) {
            $rodapeProdM3 += $value[$mes];
        }
        $rodapeProd = 0;
        foreach ($prodOrdenado as $key => $value) {
            $rodapeProd += $value[$mes];
        }

        # Custos
        $rodapeCustos = 0;
        foreach ($custos as $key => $value) {
            $rodapeCustos += $value[$mes];
        }

        # Resultado operacional
        $rodapeRO = 0;
        foreach ($resOperacional as $key => $value) {
            $rodapeRO += $value;
        }

        # CI Operacional
        $rodapeCI = 0;
        if ($rodapeProd > 0) {
            $rodapeCI = $rodapeCustos / $rodapeProd;
        } else {
            $rodapeCI = 0;
        }

        # Stock mês Janeiro
        $rodapeStkJaneiro = 0;
        foreach ($valorStockJaneiro as $key => $value) {
            $rodapeStkJaneiro += $value;
        }

        # Stock mês anterior
        if ($mes > 1) {
            $rodapeStkAnterior = 0;
            foreach ($stocki as $key => $value) {
                $rodapeStkAnterior += $value[$mes-2];
            }
        }

        # Stock mês actual
            $rodapeStkActual = 0;
        foreach ($stocki as $key => $value) {
            $rodapeStkActual += $value[$mes-1];
        }

        # Variação de stock
        if ($mes == 1) {
            $rodapeVariacaoStock = $rodapeStkActual - $rodapeStkJaneiro;
        } else {
            $rodapeVariacaoStock = $rodapeStkActual - $rodapeStkAnterior;
        }

        // Rodapés - Acumulados

        # Produção
        $rodapeProdM3Ac = 0;
        foreach ($prodM3Acumudada as $key => $value) {
            $rodapeProdM3Ac += $value;
        }
        $rodapeProdAc = 0;
        foreach ($prodAcumudada as $key => $value) {
            $rodapeProdAc += $value[$mes];
        }

        # Custos
        $rodapeCustosAc = 0;
        foreach ($custosTotal as $key => $value) {
            $rodapeCustosAc += $value[$mes];
        }

        # Resultado operacional
        foreach ($ciOperacionalAc as $key => $value) {
            $rodapeROAc = $rodapeProdAc - $rodapeCustosAc;
        }

        # CI Operacional
        $rodapeCIAc = 0;
        if ($rodapeProd > 0) {
            $rodapeCIAc = $rodapeProdAc / $rodapeCustosAc;
        } else {
            $rodapeCIAc = 0;
        }

        # Variação de stock
            $rodapeVariacaoStockAc = $rodapeStkActual - $rodapeStkJaneiro;



        // dump($rodapeStkActual);

        $vars['page'] = 'resultadooperacional';
        $vars['title'] = 'Resultado Operacional';
        $vars['mes'] = $mes;

        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        // Quantidade produzida
        $vars['quantidadeProduzida'] = $prodM3Ordenado;
        $vars['quantidadeProduzidaAcumulada'] = $prodM3Acumudada;
        // Valor Produção
        $vars['valorProduzida'] = $prodOrdenado;
        $vars['valorProduzidaAcumulada'] = $prodAcumudada;
        // Custos da operação
        $vars['totalCustos'] = $custos;
        $vars['totalCustosAcumulados'] = $custosTotal;
        // Resultado operacional
        $vars['resultadoOperacional'] = $resOperacional;
        $vars['resultadoOperacionalAcumulado'] = $resOperacionalAc;
        // CI operacional
        $vars['ciOperacional'] = $ciOperacional;
        $vars['ciOperacionalAc'] = $ciOperacionalAc;
        // Stock mês
        $vars['valorStockMesAnterior'] = $stocki;
        // dump($stocki);
        $vars['valorStockJaneiro'] = $valorStockJaneiro;
        $vars['valorStockMes'] = $stocki;
        // Variação de stocks
        $vars['variacaoMensal'] = $diffStock;
        $vars['variacaoAcumulada'] = $diffStockAc;

        $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
        // Rodapé da tabela
        $vars['somaTotalCustosAcumulados'] = $rodapeCustosAc;
        $vars['somaTotalCustos'] = $rodapeCustos;
        $vars['somaQuantidadeProduzida'] = $rodapeProdM3;
        $vars['somaQuantidadeProduzidaAcumulada'] = $rodapeProdM3Ac;
        $vars['somaValorProduzida'] = $rodapeProd;
        $vars['somaValorProduzidaAcumulada'] = $rodapeProdAc;
        $vars['somaResultadoOperacional'] = $rodapeRO;
        $vars['somaResultadoOperacionalAcumulado'] = $rodapeROAc;
        $vars['mediaCIOperacional'] = $rodapeCI;
        $vars['mediaCIOperacionalAcumulado'] = $rodapeCIAc;
        $vars['somaValorStockMesAnterior'] = $rodapeStkAnterior;
        $vars['somaValorStockJaneiro'] = $rodapeStkJaneiro;
        $vars['somaValorStockMes'] = $rodapeStkActual;
        $vars['somaVariacaoMensal'] = $rodapeVariacaoStock;
        $vars['somaVariacaoAcumulada'] = $rodapeVariacaoStockAc;

        // dados gráfico
        foreach ($cisRelatorioMensal as $key => $value) {
            $vars[$key.'GraficoCI'] = ${$key};
        }
        foreach ($stocki as $key => $value) {
            $vars[$key.'GraficoStock'] = $value;
        }
        $vars['title_grafico'] = 'CI Operacional Acumulado';
        $vars['label1'] = 'arimba';
        $vars['label2'] = 'caraculo';
        $vars['label3'] = 'cassosso';

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    public function facturacao($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $mes = $request->getAttribute('item');

        for ($i = 1; $i <= $mes; ++$i) {
            foreach ($cisRelatorioMensal as $key => $value) {
                $fornecimentoInterno[$key][$i] = $this->getFornecimento($key, 'GTO', 'SPA', $i, $i);
                $fornecimentoExterno[$key][$i] = $this->getFornecimento($key, 'GR', 'VD', $i, $i);
            }
        }

        // Somatório dos fornecimentos de todos agregados
        foreach ($fornecimentoInterno as $ci => $value) {
            foreach ($value as $mes => $dados) {
                $fornecimentoInternoTotal[$ci][$mes] = 0;
                $fornecimentoExternoTotal[$ci][$mes] = 0;
                foreach ($dados as $nome => $valor) {
                    $fornecimentoInternoTotal[$ci][$mes] += $fornecimentoInterno[$ci][$mes][$nome]['total'];
                    $fornecimentoExternoTotal[$ci][$mes] += $fornecimentoExterno[$ci][$mes][$nome]['total'];
                }
            }
        }

        // Total de fornecimento interno e externo
        for ($i = 1; $i <= $mes; ++$i) {
            $totalFI[$i] = 0;
            $totalFE[$i] = 0;
            foreach ($fornecimentoInternoTotal as $ci => $dados) {
                $totalFI[$i] += $fornecimentoInternoTotal[$ci][$i];
                $totalFE[$i] += $fornecimentoExternoTotal[$ci][$i];
            }
        }

        // Total do mês
        $totalMes = array();
        for ($i = 1; $i <= $mes; ++$i) {
            $totalMes[$i] = $totalFI[$i] + $totalFE[$i];
        }

        // Dados do rodapé

        // Subtotais dos CI
        foreach ($fornecimentoInternoTotal as $ci => $dados) {
            $rodapeInterno[$ci] = 0;
            $rodapeExterno[$ci] = 0;
            for ($i = 1; $i <= $mes; ++$i) {
                $rodapeInterno[$ci] += $fornecimentoInternoTotal[$ci][$i];
                $rodapeExterno[$ci] += $fornecimentoExternoTotal[$ci][$i];
            }
        }

        // Soma dos subtotais dos CI
        foreach ($rodapeInterno as $ci => $value) {
            $totalRodape[$ci] = $rodapeInterno[$ci] + $rodapeExterno[$ci];
        }

        // Subtotais dos totais
            $rodapeTotaisFI = 0;
        $rodapeTotaisFE = 0;
        for ($i = 1; $i <= $mes; ++$i) {
            $rodapeTotaisFI += $totalFI[$i];
            $rodapeTotaisFE += $totalFE[$i];
        }

        // Total Geral
        $rodapeTotalGeral = $rodapeTotaisFI + $rodapeTotaisFE;

        $vars['page'] = 'facturacao';
        $vars['title'] = 'Mapa Facturação';
        $vars['ci'] = $cisRelatorioMensal;
        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['meses'] = $lista_meses;
        // Dados
        $vars['fornecimentoInternoTotal'] = $fornecimentoInternoTotal;
        $vars['fornecimentoExternoTotal'] = $fornecimentoExternoTotal;
        $vars['totalFI'] = $totalFI;
        $vars['totalFE'] = $totalFE;
        $vars['totalMes'] = $totalMes;
        $vars['rodapeInterno'] = $rodapeInterno;
        $vars['rodapeExterno'] = $rodapeExterno;
        $vars['rodapeTotalGeral'] = $rodapeTotalGeral;
        $vars['rodapeTotaisFI'] = $rodapeTotaisFI;
        $vars['rodapeTotaisFE'] = $rodapeTotaisFE;
        $vars['totalRodape'] = $totalRodape;

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    public function resultado($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

        $mes = $request->getAttribute('item');

        #Custos mensais
        $custos = $this->get_custos($mes);

        #soma dos custos
        foreach ($cisRelatorioMensal as $key => $value) {
            $temp = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $custos[$key][$i];
                $custosTotal[$key][$i] = $temp;
            }
        }

        # Acumulado de custos
        foreach ($custosTotal as $key => $value) {
            $custosAcumulados[$key] = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $custosAcumulados[$key] += $custos[$key][$i];
            }
        }

        #Fornecimentos internos por CI
        $interno = 'GTO';
        $interno2 = 'SPA';
        foreach ($cisRelatorioMensal as $key => $value) {
            $fornecimentoInterno[$key] = $this->get_fornecimento($key, $interno, $interno2);
        }

        #Preencher meses sem valores
        for ($i = 0; $i < $mes; $i++) {
            foreach ($fornecimentoInterno as $key => $value) {
                foreach ($value as $indice => $valor) {
                    if ($valor->mes === $i+1) {
                        $fornInternoOrdenado[$key][$i+1] = $valor->total;
                        $fornInternoOrdenadoM3[$key][$i+1] = $valor->m3;
                    }
                }
            }
        }

        for ($i = 1; $i < $mes+1; $i++) {
            foreach ($fornInternoOrdenado as $key => $value) {
                if (empty($value[$i])) {
                    $fornInternoOrdenado[$key][$i] = 0;
                    $fornInternoOrdenadoM3[$key][$i] = 0;
                }
            }
        }

        # Fornecimentos externo por CI
        $externo = 'GR';
        $externo2 = 'VD';
        foreach ($cisRelatorioMensal as $key => $value) {
            $fornecimentoExterno[$key] = $this->get_fornecimento($key, $externo, $externo2);
        }

        #Preencher meses sem valores
        for ($i = 0; $i < $mes; $i++) {
            foreach ($fornecimentoExterno as $key => $value) {
                foreach ($value as $indice => $valor) {
                    if ($valor->mes === $i+1) {
                        $fornExternoOrdenado[$key][$i+1] = $valor->total;
                        $fornExternoOrdenadoM3[$key][$i+1] = $valor->m3;
                    }
                }
            }
        }

        for ($i = 1; $i < $mes+1; $i++) {
            foreach ($fornExternoOrdenado as $key => $value) {
                if (!isset($value[$i])) {
                    $fornExternoOrdenado[$key][$i] = 0;
                    $fornExternoOrdenadoM3[$key][$i] = 0;
                }
            }
        }

        # Acumulado dos fornecimentos Internos e Externos
        foreach ($cisRelatorioMensal as $key => $value) {
            $temp = 0;
            $temp2 = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $fornInternoOrdenado[$key][$i];
                $temp2 += $fornExternoOrdenado[$key][$i];
                $fornecimentosInternoAcumulado[$key] = $temp;
                $fornecimentosExternoAcumulado[$key] = $temp2;
            }
        }

        # Soma dos fornecimentos externos com os internos Acumulados
        foreach ($cisRelatorioMensal as $key => $value) {
            $temp = 0;
            $temp2 = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $fornTotal[$key][$i] = $fornInternoOrdenado[$key][$i] + $fornExternoOrdenado[$key][$i];
                $fornTotalM3[$key][$i] = $fornInternoOrdenadoM3[$key][$i] + $fornExternoOrdenadoM3[$key][$i];
                $temp += $fornTotalM3[$key][$i];
                $fornTotalM3Acumulado[$key] = $temp;
                $temp2 += $fornTotal[$key][$i];
                $fornTotalAcumulado[$key] = $temp2;
            }
        }

        #Acumulado dos fornecimentos
        foreach ($cisRelatorioMensal as $key => $value) {
            $temp = 0;
            $temp2 = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $fornTotal[$key][$i];
                $temp2 += $fornTotalM3[$key][$i];
                $fornecimentosTotal[$key] = $temp;
                $fornecimentosTotalM3[$key] = $temp2;
            }
        }

        # Percentagem dos fornecimentos internos e externos
        foreach ($cisRelatorioMensal as $ci => $value) {
            if ($fornTotal[$ci] === 0) {
                $percentagemFI[$ci] = 0;
                $percentagemFE[$ci] = 0;
            } else {
                $percentagemFI[$ci] = $fornInternoOrdenado[$ci][$mes] / $fornTotal[$ci][$mes] * 100;
                $percentagemFE[$ci] = $fornExternoOrdenado[$ci][$mes] / $fornTotal[$ci][$mes] * 100;
            }
            if ($fornTotalAcumulado[$ci] === 0) {
                $percentagemAcumuladaFI[$ci] = 0;
                $percentagemAcumuladaFE[$ci] = 0;
            } else {
                $percentagemAcumuladaFI[$ci] = $fornecimentosInternoAcumulado[$ci] / $fornTotalAcumulado[$ci] * 100;
                $percentagemAcumuladaFE[$ci] = $fornecimentosExternoAcumulado[$ci] / $fornTotalAcumulado[$ci] * 100;
            }
        }

        # Médias de preço
        foreach ($cisRelatorioMensal as $ci => $value) {
            if ($fornTotalM3[$ci][$mes] === 0) {
                $mediaMensal[$ci] = 0;
            } else {
                $mediaMensal[$ci] = $fornTotal[$ci][$mes] / $fornTotalM3[$ci][$mes];
            }
            if ($fornecimentosTotalM3[$ci] === 0) {
                $mediaAcumulada[$ci] = 0;
            } else {
                $mediaAcumulada[$ci] = $fornecimentosTotal[$ci] / $fornecimentosTotalM3[$ci];
            }
        }
        foreach ($fornecimentosTotal as $key => $value) {
            for ($i = 1; $i < $mes+1; $i++) {
                if ($fornecimentosTotal > 0) {
                    $racioCI[$key][$i-1] = round($custosTotal[$key][$i] / $fornTotal[$key][$i], 1);
                    $resultadoFornecimento[$key][$i-1] = round($fornTotal[$key][$i] - $custos[$key][$i], 2);
                } else {
                    $racioCI[$key][$i-1] = 0;
                    $resultadoFornecimento[$key][$i-1] = round($fornTotal[$key][$i] - $custos[$key][$i], 2);
                }
            }
        }

        foreach ($fornTotalAcumulado as $key => $value) {
            if ($fornTotalAcumulado > 0) {
                $racioCIAcumulado[$key] = round($custosAcumulados[$key] / $fornTotalAcumulado[$key], 1);
                $resultadoFornecimentoAcumulado[$key] = round($fornTotalAcumulado[$key][$mes] - $custosAcumulados[$key], 2);
            } else {
                $racioCIAcumulado = 0;
                $resultadoFornecimentoAcumulado[$key] = round($fornTotalAcumulado[$key] - $custosAcumulados[$key], 2);
            }
        }

        # Valores acumulativos de fornecimento por mês
        foreach ($fornTotal as $key => $value) {
            $temp = 0;
            for ($i = 1; $i < $mes+1; $i++) {
                $temp += $value[$i];
                $forn[$key][$i] = $temp;
            }
        }

        foreach ($cisRelatorioMensal as $key => $value) {
            for ($i = 1; $i < $mes+1; $i++) {
                if ($forn[$key][$i] > 0) {
                    ${$key}[$i-1] = round($custosTotal[$key][$i] / $forn[$key][$i], 1);
                } else {
                    ${$key}[$i-1] = 0;
                }
            }
        }
        // RODAPÉS

        // Mensal

        # Quantidades vendidas
        $rodapeM3 = 0;
        foreach ($fornTotalM3 as $key => $value) {
            $rodapeM3 += $value[$mes];
        }

        # Valor fornecido Internamente
        $rodapeFornInterno = 0;
        foreach ($fornInternoOrdenado as $key => $value) {
            $rodapeFornInterno += $value[$mes];
        }

        # Valor fornecido Externamente
        $rodapeFornExterno = 0;
        foreach ($fornExternoOrdenado as $key => $value) {
            $rodapeFornExterno += $value[$mes];
        }

        # Total fornecido
        $rodapeTotalFornecido = $rodapeFornInterno + $rodapeFornExterno;

        # Média preço
        if ($rodapeM3 > 0) {
            $rodapeMediaM3 = $rodapeTotalFornecido / $rodapeM3;
        } else {
            $rodapeMediaM3 = 0;
        }

        #total custos Mensal
        $totalDeCustos = 0;
        foreach ($custosTotal as $key => $value) {
            $totalDeCustos += $value[$mes];
        }

        #média de percentagens
        if ($rodapeTotalFornecido > 0) {
            $rodapePercFI = $rodapeFornInterno / $rodapeTotalFornecido * 100;
            $rodapePercFE = $rodapeFornExterno / $rodapeTotalFornecido * 100;
        } else {
            $rodapePercFI = 0;
            $rodapePercFE = 0;
        }

        # Média de CIF
        if ($rodapeTotalFornecido > 0) {
            $rodapeCIF = $totalDeCustos / $rodapeTotalFornecido;
        } else {
            $rodapeCIF = 0;
        }


        # Resultado facturação total
        $rodapeResultadoAc = $rodapeTotalFornecido - $totalDeCustos;

        // Acumulado

        # Quantidades vendidas
        $rodapeM3Acumulado = 0;
        foreach ($fornecimentosTotalM3 as $key => $value) {
            $rodapeM3Acumulado += $value;
        }

        # Valor fornecido Internamente
        $rodapeFornInternoAc = 0;
        foreach ($fornecimentosInternoAcumulado as $key => $value) {
            $rodapeFornInternoAc += $value;
        }

        # Valor fornecido Externamente
        $rodapeFornExternoAc = 0;
        foreach ($fornecimentosExternoAcumulado as $key => $value) {
            $rodapeFornExternoAc += $value;
        }

        # Total fornecido
        $rodapeTotalFornecidoAc = $rodapeFornInternoAc + $rodapeFornExternoAc;

        # Média preço
        if ($rodapeM3Acumulado > 0) {
            $rodapeMediaM3Ac = $rodapeTotalFornecidoAc / $rodapeM3Acumulado;
        } else {
            $rodapeMediaM3Ac = 0;
        }

        #média de percentagens
            if ($rodapeTotalFornecidoAc > 0) {
                $rodapePercAcFI = $rodapeFornInternoAc / $rodapeTotalFornecidoAc * 100;
            } else {
                $rodapePercAcFI = 0;
            }
        if ($rodapeTotalFornecidoAc > 0) {
            $rodapePercAcFE = $rodapeFornExternoAc / $rodapeTotalFornecidoAc * 100;
        } else {
            $rodapePercAcFE = 0;
        }

        #total custos Acumulado
        $totalDeCustosAc = 0;
        foreach ($custosAcumulados as $key => $value) {
            $totalDeCustosAc += $value;
        }

        # Média de CIF
        if ($rodapeTotalFornecidoAc > 0) {
            $rodapeCIFAc = $totalDeCustosAc / $rodapeTotalFornecidoAc;
        } else {
            $rodapeCIFAc = 0;
        }

        # Resultado facturação total
        $rodapeResultado = $rodapeTotalFornecidoAc - $totalDeCustosAc;

        $vars['page'] = 'resultadofacturacao';
        $vars['title'] = 'Resultado Mediante Facturação';
        $vars['ci'] = $cisNome;
        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['meses'] = $lista_meses;
        $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
        $vars['percentagemFI'] = $percentagemFI;
        $vars['percentagemAcumuladaFI'] = $percentagemAcumuladaFI;
        $vars['percentagemFE'] = $percentagemFE;
        $vars['percentagemAcumuladaFE'] = $percentagemAcumuladaFE;

        $vars['resultadoFornecimento'] = $resultadoFornecimento;
        $vars['mes'] = $mes;
        $vars['mesMenosUm'] = $mes -1 ;
        $vars['fornExternoOrdenado'] = $fornExternoOrdenado;
        $vars['fornInternoOrdenado'] = $fornInternoOrdenado;
        $vars['fornTotal'] = $fornTotal;
        $vars['racioCI'] = $racioCI;
        $vars['fornTotalM3'] = $fornTotalM3;
        $vars['mediaMensal'] = $mediaMensal;
        $vars['mediaAcumulada'] = $mediaAcumulada;
        $vars['fornTotalM3Acumulado'] = $fornTotalM3Acumulado;
        $vars['fornTotalAcumulado'] = $fornTotalAcumulado;
        $vars['fornecimentosInternoAcumulado'] = $fornecimentosInternoAcumulado;
        $vars['fornecimentosExternoAcumulado'] = $fornecimentosExternoAcumulado;
        $vars['resultadoFornecimentoAcumulado'] = $resultadoFornecimentoAcumulado;
        $vars['racioCIAcumulado'] = $racioCIAcumulado;


        // Rodapés
        $vars['rodapeM3'] = $rodapeM3;
        $vars['rodapeM3Acumulado'] = $rodapeM3Acumulado;
        $vars['rodapeFornExterno'] = $rodapeFornExterno;
        $vars['rodapeFornInterno'] = $rodapeFornInterno;
        $vars['rodapeFornInternoAc'] = $rodapeFornInternoAc;
        $vars['rodapeFornExternoAc'] = $rodapeFornExternoAc;
        $vars['rodapeTotalFornecido'] = $rodapeTotalFornecido;
        $vars['rodapeTotalFornecidoAc'] = $rodapeTotalFornecidoAc;
        $vars['rodapeMediaM3'] = $rodapeMediaM3;
        $vars['rodapeMediaM3Ac'] = $rodapeMediaM3Ac;
        $vars['rodapePercFI'] = $rodapePercFI;
        $vars['rodapePercFE'] = $rodapePercFE;
        $vars['rodapePercAcFI'] = $rodapePercAcFI;
        $vars['rodapePercAcFE'] = $rodapePercAcFE;
        $vars['rodapeCIF'] = $rodapeCIF;
        $vars['rodapeCIFAc'] = $rodapeCIFAc;

        $vars['rodapeResultado'] = $rodapeResultado;
        $vars['rodapeResultadoAc'] = $rodapeResultadoAc;

        // dados gráfico
        foreach ($cisRelatorioMensal as $key => $value) {
            $vars[$key.'GraficoCI'] = ${$key};
        }
        foreach ($resultadoFornecimento as $key => $value) {
            $vars[$key.'GraficoStock'] = $value;
        }
        $vars['title_grafico'] = 'CI Mediante Facturação - Acumulado';
        $vars['label1'] = 'arimba';
        $vars['label2'] = 'caraculo';
        $vars['label3'] = 'cassosso';

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }

    public function vendas($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $mes = $request->getAttribute('item');

        foreach ($cisRelatorioMensal as $ci => $value) {
            $valoresVD[$ci] = $this->getFornecimento($ci, 'VD', 'VD', $mes, $mes);
            $valoresGR[$ci] = $this->getFornecimento_clientes($ci, 'GR', 'GR', $mes, $mes);
            $valoresGTO[$ci] = $this->getFornecimento($ci, 'GTO', 'SPA', $mes, $mes);
        }

        // Organização dos valores das GR
        foreach ($valoresGR as $ci => $value) {
            foreach ($value as $cliente => $valor) {
                $vendaGR[$cliente][$ci] = $valor;
            }
        }

        foreach ($vendaGR as $cliente => $value) {
            foreach ($cisRelatorioMensal as $ci => $valor) {
                if (!isset($vendaGR[$cliente][$ci])) {
                    $vendaGR[$cliente][$ci]['m3'] = 0;
                    $vendaGR[$cliente][$ci]['total'] = 0;
                }
            }
        }

        // Organização dos valores das VD e GTO
        foreach ($cisRelatorioMensal as $ci => $value) {
            $totalVDm3[$ci] = 0;
            $totalGTOm3[$ci] = 0;
            $totalVDtotal[$ci] = 0;
            $totalGTOtotal[$ci] = 0;
            foreach ($lista_array_agregados as $nome => $valor) {
                $totalVDm3[$ci] += $valoresVD[$ci][$nome]['m3'];
                $totalGTOm3[$ci] += $valoresGTO[$ci][$nome]['m3'];
                $totalVDtotal[$ci] += $valoresVD[$ci][$nome]['total'];
                $totalGTOtotal[$ci] += $valoresGTO[$ci][$nome]['total'];
            }
        }

        // Totais por cliente
        foreach ($vendaGR as $cliente => $valor) {
            $somaM3[$cliente] = 0;
            $somaTotal[$cliente] = 0;
            foreach ($cisRelatorioMensal as $ci => $value) {
                $somaM3[$cliente] += $vendaGR[$cliente][$ci]['m3'];
                $somaTotal[$cliente] += $vendaGR[$cliente][$ci]['total'];
            }
        }

        $somaVDm3 = 0;
        $somaVDtotal = 0;
        $somaGTOm3 = 0;
        $somaGTOtotal = 0;
        foreach ($cisRelatorioMensal as $ci => $value) {
            $somaVDm3 += $totalVDm3[$ci];
            $somaVDtotal += $totalVDtotal[$ci];
            $somaGTOm3 += $totalGTOm3[$ci];
            $somaGTOtotal += $totalGTOtotal[$ci];
        }

        // Totais do rodapé
        foreach ($cisRelatorioMensal as $ci => $value) {
            $rodapeM3[$ci] = 0;
            $rodapeTotal[$ci] = 0;
            foreach ($vendaGR as $cliente => $value) {
                $rodapeM3[$ci] += $vendaGR[$cliente][$ci]['m3'];
                $rodapeTotal[$ci] += $vendaGR[$cliente][$ci]['total'];
            }
            $rodapeM3[$ci] += $totalGTOm3[$ci] + $totalVDm3[$ci];
            $rodapeTotal[$ci] += $totalGTOtotal[$ci] + $totalVDtotal[$ci];
        }

        $rodapeTotalM3 = 0;
        foreach ($somaM3 as $cliente => $value) {
            $rodapeTotalM3 += $value;
        }

        $rodapeTotalTotal = 0;
        foreach ($somaTotal as $cliente => $value) {
            $rodapeTotalTotal += $value;
        }

        $vars['page'] = 'vendas';
        $vars['title'] = 'Mapa de Facturação';
        $vars['ci'] = $cisNome;
        $vars['mes_titulo'] = $lista_meses[$mes - 1];
        $vars['meses'] = $lista_meses;
        $vars['cisRelatorioMensal'] = $cisRelatorioMensal;

        $vars['vendaGR'] = $vendaGR;
        $vars['totalVDm3'] = $totalVDm3;
        $vars['totalGTOm3'] = $totalGTOm3;
        $vars['totalVDtotal'] = $totalVDtotal;
        $vars['totalGTOtotal'] = $totalGTOtotal;
        $vars['somaM3'] = $somaM3;
        $vars['somaTotal'] = $somaTotal;
        $vars['somaVDm3'] = $somaVDm3;
        $vars['somaVDtotal'] = $somaVDtotal;
        $vars['somaGTOm3'] = $somaGTOm3;
        $vars['somaGTOtotal'] = $somaGTOtotal;
        // rodapé
        $vars['rodapeM3'] = $rodapeM3;
        $vars['rodapeTotal'] = $rodapeTotal;
        $vars['rodapeTotalM3'] = $rodapeTotalM3;
        $vars['rodapeTotalTotal'] = $rodapeTotalTotal;

        return $this->view->render($response, 'relatorios/mensais/'.$vars['page'].'.twig', $vars);
    }
}
