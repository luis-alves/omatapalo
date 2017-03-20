<?php

namespace Src\Controllers;

/**
 *
 */
final class TabelasAction extends Action
{

    public function form ($request, $response)
    {
        include 'src/Auxiliares/globals.php';


        $cindus = 'importacao_'.$cAnalitico;

        $query = "SELECT DISTINCT nome_cliente FROM $cindus ORDER BY nome_cliente";
        $clientes = $this->db->prepare($query);
        $clientes->execute();

        if ($clientes->rowCount() > 0) {
            $clientes = $clientes->fetchAll(\PDO::FETCH_OBJ);
        }

        $query = "SELECT DISTINCT no_obra FROM obras ORDER BY no_obra";
        $obras = $this->db->prepare($query);
        $obras->execute();

        if ($obras->rowCount() > 0) {
            $obras = $obras->fetchAll(\PDO::FETCH_OBJ);
        }

        $vars['clientes'] = [];
        foreach ($clientes as $key => $value) {
            array_push($vars['clientes'], $value->nome_cliente);
        }

        $vars['obras'] = [];
        foreach ($obras as $key => $value) {
            array_push($vars['obras'], $value->no_obra);
        }

        # Listar agregados por centro industrial
        $nomeCI = $cAnalitico.'Agregados';
        $nomeCE = $$nomeCI;
        foreach ($nomeCE as $key => $nome) {
            $agregadosDoCi[] = $nome;
        }
        // dump($agregadosDoCi);


        $vars['page'] = 'tabelas';
        $vars['title'] = 'Tabelas';
        $vars['agregadosDoCi'] = $agregadosDoCi;

        return $this->view->render($response, 'tabelas/balanca/form.twig', $vars);

    }

    public function toneladas ($request, $response)
    {
        include 'src/Auxiliares/globals.php';


        //
        // Obter os preços
        //
        $query = "SELECT `valor_ex_ton`, `nome_agre` FROM `agregados`
                    JOIN `valorun_externo_ton`
                    ON `agr_bar_ton_id` = `ID`
                    GROUP BY `nome_agre`";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
        }

    }

    public function balanca($request, $response)
    {
        $vars['page'] = 'balanca';
        $vars['title'] = 'Mapa Balança';


        if (!isset($_POST['data_inicio']) OR !isset($_POST['data_final'])) {

            $vars['page'] = 'balanca/form';
            $vars['erro'] = 'Selecione uma data';
            $vars['title'] = 'Formulário Balança';
            $_SESSION['erro'] = null;

            $cindus = "importacao_".$cAnalitico;

            $query = "SELECT DISTINCT nome_cliente FROM importacao_arimba ORDER BY nome_cliente";
            $clientes = $this->db->prepare($query);
            $clientes->execute();

            if ($clientes->rowCount() > 0) {
                $vars['clientes'] = $clientes->fetchAll(\PDO::FETCH_OBJ);
            }

            $query = "SELECT DISTINCT no_obra FROM obras ORDER BY no_obra";
            $obras = $this->db->prepare($query);
            $obras->execute();

            if ($obras->rowCount() > 0) {
                $vars['obras'] = $obras->fetchAll(\PDO::FETCH_OBJ);
            }

            return $response->withRedirect('balanca/form');

        } else {

            $data_inicial = $_POST['data_inicio'];
            $data_final = $_POST['data_final'];

            if (!empty($_POST['check'])) {
                $check = $_POST['check'];
                $_SESSION['erro'] = null;

            } else {
                $_SESSION['erro'] = 'Não seleccionou um tipo de documento';
                return $response->withRedirect('balanca/form');
                // return $response->withRedirect($this->router->pathFor('view',[], $vars));

            }

            // reformat data to presente to sql making it like ('GTO', 'GR')
            // from array(0 => 'GTO', 1 => 'GR')
            // implode() method from http://stackoverflow.com/questions/
            // 12230338/mysql-where-not-in-name-array

            $new_array = rtrim('('.implode(', ', $check).')', ',');

            if (!empty($_POST['agr'])) {
                $agr = $_POST['agr'];
                $_SESSION['erro'] = null;
            } else {
                $_SESSION['erro'] = 'Não seleccionou um agregado';
                return $response->withRedirect('balanca/form');
            }

            if (!empty($_POST['check']) && !empty($_POST['agr']) && empty($_POST['data_inicio'])){
                $_SESSION['erro'] = 'Não seleccionou uma data';
                return $response->withRedirect('balanca/form');
            }

            $new_agr = rtrim('('.implode(', ', $agr).')', ',');

            switch (true) {
                case empty($_POST['clientes']) && empty($_POST['obras']):

                    $query = "SELECT `data`, `tipo_doc`, `num_doc`, `nome_cliente`, `no_obra`,
                                     `obra`, `nome_obra`, `nome_agr_corr`, `peso`,
                                      ROUND((`peso`/`baridade`),2) AS `m3`,
                                      ROUND(`valor_in_ton`*`baridade`,2) AS `preco_m3`,
                                      ROUND(ROUND((`peso`/`baridade`),2)*ROUND(`valor_in_ton`*`baridade`,2),2) AS `total_m3`,
                                      ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2) AS `preco_vd`,
                                      ROUND(ROUND((`peso`/`baridade`),2) * ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2),2) AS `total_v_m3`
                              FROM `importacao_arimba`
                              LEFT JOIN `centros_analiticos`
                              ON `ca_id` = `obra`
                              JOIN `agregados`
                              ON `nome_agr` = `nome_agre`
                              JOIN `baridades`
                              ON `agr_id` = `agregado_id`
                              JOIN `valorun_interno_ton`
                              ON `agr_bar_id` = `agregado_id`
                              JOIN `valorun_externo_ton`
                              ON `agr_bar_ton_id` = `agregado_id`
                              JOIN `obras`
                              ON `obra` = `id_obra`
                              WHERE  `data` BETWEEN '$data_inicial' AND '$data_final' AND
                                     `tipo_doc` IN $new_array AND `nome_agr` IN $new_agr
                              ORDER by `data`, `num_doc`";
                    break;

                case !empty($_POST['clientes']) && empty($_POST['obras']):
                    $array_cliente = $_POST['clientes'];
                    $new_cliente = rtrim('('."'".implode("'".", '", $array_cliente)."'".')', ',');

                    $query = "SELECT `data`, `tipo_doc`, `num_doc`, `nome_cliente`, `no_obra`,
                                     `obra`, `nome_obra`, `nome_agr_corr`, `peso`,
                                      ROUND((`peso`/`baridade`),2) AS `m3`,
                                      ROUND(`valor_in_ton`*`baridade`,2) AS `preco_m3`,
                                      ROUND(ROUND((`peso`/`baridade`),2)*ROUND(`valor_in_ton`*`baridade`,2),2) AS `total_m3`,
                                      ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2) AS `preco_vd`,
                                      ROUND(ROUND((`peso`/`baridade`),2) * ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2),2) AS `total_v_m3`
                                  FROM `importacao_arimba`
                                  LEFT JOIN `centros_analiticos`
                                  ON `ca_id` = `obra`
                                  JOIN `agregados`
                                  ON `nome_agr` = `nome_agre`
                                  JOIN `baridades`
                                  ON `agr_id` = `agregado_id`
                                  JOIN `valorun_interno_ton`
                                  ON `agr_bar_id` = `agregado_id`
                                  JOIN `valorun_externo_ton`
                                  ON `agr_bar_ton_id` = `agregado_id`
                                  LEFT JOIN `obras`
                                  ON `obra` = `id_obra`
                                  WHERE  `data` BETWEEN '$data_inicial' AND '$data_final' AND `tipo_doc` IN $new_array AND `nome_agr` IN $new_agr AND `nome_cliente` IN $new_cliente
                                  ORDER by `data`, `num_doc`";
                    break;

                case !empty($_POST['clientes']) && !empty($_POST['obras']):
                    $array_obras = $_POST['obras'];
                    $new_obra = rtrim('('."'".implode("'".", '", $array_obras)."'".')', ',');
                    //dump($new_cliente);
                    $array_cliente = $_POST['clientes'];
                    $new_cliente = rtrim('('."'".implode("'".", '", $array_cliente)."'".')', ',');

                    $query = "SELECT `data`, `tipo_doc`, `num_doc`, `nome_cliente`, `no_obra`,
                                     `obra`, `nome_obra`, `nome_agr_corr`, `peso`,
                                      ROUND((`peso`/`baridade`),2) AS `m3`,
                                      ROUND(`valor_in_ton`*`baridade`,2) AS `preco_m3`,
                                      ROUND(ROUND((`peso`/`baridade`),2)*ROUND(`valor_in_ton`*`baridade`,2),2) AS `total_m3`,
                                      ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2) AS `preco_vd`,
                                      ROUND(ROUND((`peso`/`baridade`),2) * ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2),2) AS `total_v_m3`
                              FROM `importacao_arimba`
                              LEFT JOIN `centros_analiticos`
                              ON `ca_id` = `obra`
                              JOIN `agregados`
                              ON `nome_agr` = `nome_agre`
                              JOIN `baridades`
                              ON `agr_id` = `agregado_id`
                              JOIN `valorun_interno_ton`
                              ON `agr_bar_id` = `agregado_id`
                              JOIN `valorun_externo_ton`
                              ON `agr_bar_ton_id` = `agregado_id`
                              LEFT JOIN `obras`
                              ON `obra` = `id_obra`
                              WHERE  `data` BETWEEN '$data_inicial' AND '$data_final' AND
                                     `tipo_doc` IN $new_array AND `nome_agr` IN $new_agr AND
                                     `nome_cliente` IN $new_cliente
                              AND `nome_obra` IN $new_obra
                              ORDER by `data`, `num_doc`";
                        break;

                case empty($_POST['clientes']) && !empty($_POST['obras']):
                    $array_obras = $_POST['obras'];
                    $new_obra = rtrim('('."'".implode("'".", '", $array_obras)."'".')', ',');
                    $query = "SELECT `data`, `tipo_doc`, `num_doc`, `nome_cliente`, `no_obra`,
                                     `obra`, `nome_obra`, `nome_agr_corr`, `peso`,
                                      ROUND((`peso`/`baridade`),2) AS `m3`,
                                      ROUND(`valor_in_ton`*`baridade`,2) AS `preco_m3`,
                                      ROUND(ROUND((`peso`/`baridade`),2)*ROUND(`valor_in_ton`*`baridade`,2),2) AS `total_m3`,
                                      ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2) AS `preco_vd`,
                                      ROUND(ROUND((`peso`/`baridade`),2) * ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2),2) AS `total_v_m3`
                                  FROM `importacao_arimba`
                                  LEFT JOIN `centros_analiticos`
                                  ON `ca_id` = `obra`
                                  JOIN `agregados`
                                  ON `nome_agr` = `nome_agre`
                                  JOIN `baridades`
                                  ON `agr_id` = `agregado_id`
                                  JOIN `valorun_interno_ton`
                                  ON `agr_bar_id` = `agregado_id`
                                  JOIN `valorun_externo_ton`
                                  ON `agr_bar_ton_id` = `agregado_id`
                                  LEFT JOIN `obras`
                                  ON `obra` = `id_obra`
                                  WHERE  `data` BETWEEN '$data_inicial' AND '$data_final' AND
                                         `tipo_doc` IN $new_array AND `nome_agr` IN $new_agr AND
                                         `no_obra` IN $new_obra
                                  ORDER by `data`, `num_doc`";
                    break;
            }

            $rows = $this->db->prepare($query);
            $rows->execute();

            if ($rows->rowCount() > 0) {
                $vars['valores'] = $rows->fetchAll(\PDO::FETCH_OBJ);
            }

            $vars['page'] = 'balanca/balanca';
            $vars['title'] = 'Mapa Balança';

            return $this->view->render($response, 'tabelas/balanca/balanca.twig', $vars);
        }
    }




}
