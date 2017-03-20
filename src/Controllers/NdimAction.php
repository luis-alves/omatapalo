<?php

namespace Src\Controllers;

/**
 *
 */
final class NdimAction extends Action
{

    public function geral($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        //
        // Obter dados das Vendas internas
        //

        $mes = $request->getAttribute('item');
        $indus = 'importacao_'.$cAnalitico;

        $query = "SELECT `no_obra` AS `nome`,
                          `id_obra` AS `id`
                  FROM `$indus`
                  JOIN `obras`
                  ON `obra` = `id_obra`
                  WHERE  `nome_agr` IN ($lista_agregados) AND `tipo_doc` IN ('GTO', 'PSA') AND YEAR(`data`) IN ('$ano') AND MONTH(`data`) IN ($mes)
                  GROUP BY `no_obra`
                  ORDER by `no_obra`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);


            $comprimento = count($vars['row']);

            foreach ($vars['row'] as $key => $value) {
                $properties[] = get_object_vars($value);
            }


            $vars['page'] = 'mapas/ndim/resumo';
            $vars['title'] = 'NDIM - NOTA DE DÉBITO INTERNO MENSAL';
            $vars['obras'] = $properties;
            $vars['mes'] = $mes;
            $vars['comprimento'] = $comprimento;
            $vars['erro'] = null;

            return $this->view->render($response, $vars['page'].'.twig', $vars);

        } else {

            $vars['title'] = 'NDIM - NOTA DE DÉBITO INTERNO MENSAL';
            $vars['erro'] = 'Não existem dados';
            $vars['page'] = 'mapas/ndim/resumo';

            return $this->view->render($response, $vars['page'].'.twig', $vars);
        }

    }

    public function dados($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        //
        // Obter dados das Vendas internas
        //

        $obra = $request->getAttribute('item');
        $mes = $request->getAttribute('mes');
        $ano_ndim = $request->getAttribute('ano');
        $indus = 'importacao_'.$cAnalitico;

        $cambioMes = $cambio[$ano_ndim][$mes-1];

        $query = "SELECT `nome_agr_corr` AS `nomeBrita`,
                         `no_obra` AS `nomeObra`,
                         `obra`,
                         MONTH(`data`) AS `mes`,
                         SUM(ROUND(`peso` / `baridade`,2)) AS `m3`,
                         Avg(round(`valor_in_ton` * `baridade` * $cambioMes)) AS `pu`,
                         ROUND(SUM(ROUND(`peso` / `baridade`,2)) * avg(ROUND(`valor_in_ton` * `baridade` * $cambioMes,2)),2) AS `total`
                  FROM `$indus`
                  JOIN `agregados`
                  ON `nome_agre` = `nome_agr`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  JOIN `valorun_interno_ton`
                  ON `agr_id` = `agr_bar_id`
                  JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE `nome_agre` IN ($lista_agregados) AND `tipo_doc` IN ('GTO', 'SPA') AND YEAR(`data`) IN ('$ano_ndim')
                        AND MONTH(`data`) IN ($mes) AND `obra` IN ('$obra')
                  GROUP BY `nome_agr_corr`
                  ORDER by `nome_agr_corr`
                 ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $numeroDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano_ndim);

            $quantBritas = count($vars['row']);

            foreach ($vars['row'] as $key => $value) {
                $britas[] = get_object_vars($value);
            }

            $_SESSION['britas'] = $britas;

            $totalBritas = 0;
            foreach ($britas as $key => $value) {
                $totalBritas += $value['total'];
            }

            $vars['page'] = 'mapas/ndim/geral';
            $vars['title'] = 'NDIM - Resumo';
            $vars['print'] = 'printGeral';
            $vars['mes'] = $mes;
            $vars['ano_ndim'] = $ano_ndim;
            $vars['obra'] = $obra;
            $vars['britas'] = $britas;
            $vars['nomeObra'] = $vars['row'][0]->nomeObra;
            $vars['cindus'] = $cisRelatorioMensal[$cAnalitico];
            $vars['cAnalitico'] = ucFirst($cAnalitico);
            $vars['numeroDiasMes'] = $numeroDiasMes;
            $vars['quantBritas'] = $quantBritas;
            $vars['totalBritas'] = $totalBritas;
            $vars['erro'] = null;


            return $this->view->render($response, $vars['page'].'.twig', $vars);


        } else {
            $vars['page'] = 'mapas/ndim/geral';
            $vars['title'] = 'NDIM - Resumo';
            $vars['erro'] = 'sem dados';

            return $this->view->render($response, $vars['page'].'.twig', $vars);
        }

    }

    public function detalhado($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        //
        // Obter dados das Vendas internas
        //

        $obra = $request->getAttribute('item');
        $mes = $request->getAttribute('mes');
        $ano_ndim = $request->getAttribute('ano');
        $indus = 'importacao_'.$cAnalitico;

        $cambioMes = $cambio[$ano_ndim][$mes-1];

        $query = "SELECT `nome_agr_corr` AS `nomeBrita`,
                         `no_obra` AS `nomeObra`,
                         `num_doc` AS `guia`,
                         `data`,
                         `obra`,
                         MONTH(`data`) AS `mes`,
                         ROUND(`peso` / `baridade`,2) AS `m3`,
                         ROUND(`valor_in_ton` * `baridade` * $cambioMes) AS `pu`,
                         ROUND(`peso` / `baridade`,2) * ROUND(`valor_in_ton` * `baridade` * $cambioMes,2) AS `total`
                  FROM `$indus`
                  JOIN `agregados`
                  ON `nome_agre` = `nome_agr`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  JOIN `valorun_interno_ton`
                  ON `agr_id` = `agr_bar_id`
                  JOIN `obras`
                  ON `id_obra` = `obra`
                  WHERE `nome_agre` IN ($lista_agregados) AND `tipo_doc` IN ('GTO', 'SPA') AND YEAR(`data`) IN ('$ano_ndim')
                        AND MONTH(`data`) IN ($mes) AND `obra` IN ('$obra')
                  ORDER by `data`
                 ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $numeroDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano_ndim);

            $quantBritas = count($vars['row']);

            foreach ($vars['row'] as $key => $value) {
                $britas[] = get_object_vars($value);
            }

            $_SESSION['britas'] = $britas;

            $totalBritas = 0;
            foreach ($britas as $key => $value) {
                $totalBritas += $value['total'];
            }

            $vars['page'] = 'mapas/ndim/detalhado';
            $vars['title'] = 'NDIM - Detalhado';
            $vars['print'] = 'printDetalhado';
            $vars['mes'] = $mes;
            $vars['ano_ndim'] = $ano_ndim;
            $vars['obra'] = $obra;
            $vars['britas'] = $britas;
            $vars['nomeObra'] = $vars['row'][0]->nomeObra;
            $vars['cindus'] = $cisRelatorioMensal[$cAnalitico];
            $vars['cAnalitico'] = ucFirst($cAnalitico);
            $vars['numeroDiasMes'] = $numeroDiasMes;
            $vars['quantBritas'] = $quantBritas;
            $vars['totalBritas'] = $totalBritas;
            $vars['erro'] = null;


            return $this->view->render($response, $vars['page'].'.twig', $vars);


        } else {
            $vars['page'] = 'mapas/ndim/detalhado';
            $vars['title'] = 'NDIM - Detalhado';
            $vars['erro'] = 'sem dados';

            return $this->view->render($response, $vars['page'].'.twig', $vars);
        }




    }

}
