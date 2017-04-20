<?php

namespace Src\Controllers;

use Src\Models\VendasArimba;
use Illuminate\Database\Capsule\Manager as Capsule;
use DateTime;

/**
 *
 */
final class TabelasAction extends Action
{
    public function form($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

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
            $agregadosDoCi[] = [$nome => $key];
        }
        // dump($agregadosDoCi);


        $vars['page'] = 'tabelas';
        $vars['title'] = 'Tabelas';
        $vars['agregadosDoCi'] = $agregadosDoCi;

        return $this->view->render($response, 'tabelas/balanca/form.twig', $vars);
    }

    public function toneladas($request, $response)
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
        include 'src/Auxiliares/helpers.php';
        include 'src/Auxiliares/globals.php';

        $vars['page'] = 'balanca';
        $vars['title'] = 'Mapa Balança';

        if (!isset($_POST['data_inicio']) or !isset($_POST['data_final'])) {
            $vars['page'] = 'balanca/form';
            $vars['erro'] = 'Selecione uma data';
            $vars['title'] = 'Formulário Balança';
            $_SESSION['erro'] = null;

            $cindus = "importacao_".$cAnalitico;

            $query = "SELECT DISTINCT nome_cliente FROM `$cindus` ORDER BY nome_cliente";
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

            if (!empty($_POST['agr'])) {
                $agr = $_POST['agr'];
                $_SESSION['erro'] = null;
            } else {
                $_SESSION['erro'] = 'Não seleccionou um agregado';
                return $response->withRedirect('balanca/form');
            }

            if (!empty($_POST['check']) && !empty($_POST['agr']) && empty($_POST['data_inicio'])) {
                $_SESSION['erro'] = 'Não seleccionou uma data';
                return $response->withRedirect('balanca/form');
            }

            $todosTipos = array('GTO', 'SPA', 'VD', 'GR');

            $tipoDoc = rtrim("'".implode("'".", '", $check)."'", ',');
            $tipoDocArray = explode(", ", $tipoDoc);

            foreach ($check as $key => $value) {
                if (!in_array($value, $todosTipos)) {
                    $vars['valores'] = [];
                    $vars['page'] = 'balanca/balanca';
                    $vars['title'] = 'Mapa Balança';
                    dump($todosTipos);
                    return $this->view->render($response, 'tabelas/balanca/balanca.twig', $vars);
                }
            }

            if ($this->validateDate($data_inicial) === false || $this->validateDate($data_final) === false) {
                $vars['valores'] = [];
                $vars['page'] = 'balanca/balanca';
                $vars['title'] = 'Mapa Balança';
                dump($todosTipos);
                return $this->view->render($response, 'tabelas/balanca/balanca.twig', $vars);
            }

            // dump($tipoDocArray);
            switch (true) {
                case empty($_POST['clientes']) && empty($_POST['obras']):

                    $query = "SELECT `data`, `tipo_doc`, `num_doc`, `nome_cliente`, `no_obra`,
                                     `obra`, `nome_obra`, `nome_agr_corr`, `peso`,
                                      ROUND((`peso`/`baridade`),2) AS `m3`,
                                      ROUND(`valor_in_ton`*`baridade`,2) AS `preco_m3`,
                                      ROUND(ROUND((`peso`/`baridade`),2)*ROUND(`valor_in_ton`*`baridade`,2),2) AS `total_m3`,
                                      ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2) AS `preco_vd`,
                                      ROUND(ROUND((`peso`/`baridade`),2) * ROUND(`valor_ex_ton`*`baridade` * (1-`desco`),2),2) AS `total_v_m3`
                              FROM `$cindus`
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
                              WHERE `tipo_doc` IN ($tipoDoc) AND `data` BETWEEN '$data_inicial' AND '$data_final' AND `nome_agr` IN ($lista_agregados)
                              ORDER by `data`, `num_doc`";

                              $rows = $this->db->prepare($query);
                              $rows->execute();

                              if ($rows->rowCount() > 0) {
                                  $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
                                  $queryBalanca = $vars['row'];
                              } else {
                                  $queryBalanca = [];
                              }

                    break;

                case !empty($_POST['clientes']) && empty($_POST['obras']):
                    $array_cliente = $_POST['clientes'];

                    $queryBalanca = Capsule::table('importacao_'.$cAnalitico)->select(Capsule::raw('data, tipo_doc, num_doc, nome_cliente, no_obra,obra,
                                                             nome_obra, nome_agr_corr, peso,
                                                             ROUND(peso/baridade) AS m3,
                                                             ROUND(valor_in_ton * baridade,2) AS preco_m3,
                                                             ROUND(ROUND((peso/baridade),2)*ROUND(valor_in_ton*baridade,2),2) AS total_m3,
                                                             ROUND(valor_ex_ton*baridade * (1-desco),2) AS preco_vd,
                                                             ROUND(ROUND((peso/baridade),2) * ROUND(valor_ex_ton*baridade * (1-desco),2),2) AS total_v_m3'))
                                               ->leftjoin('centros_analiticos', 'ca_id', '=', 'obra')
                                               ->join('agregados', 'nome_agr', '=', 'nome_agre')
                                               ->join('baridades', 'agr_id', '=', 'agregado_id')
                                               ->join('valorun_interno_ton', 'agr_bar_id', '=', 'agregado_id')
                                               ->join('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agregado_id')
                                               ->join('obras', 'obra', '=', 'id_obra')
                                               ->whereBetween('data', [$data_inicial,$data_final])
                                               ->whereIn('tipo_doc', $check)
                                               ->whereIn('nome_agr', $lista_agregados_array)
                                               ->whereIn('nome_cliente', $array_cliente)
                                               ->get();

                    break;

                case !empty($_POST['clientes']) && !empty($_POST['obras']):
                    $array_obras = $_POST['obras'];
                    $array_cliente = $_POST['clientes'];

                    $queryBalanca = Capsule::table('importacao_'.$cAnalitico)->select(Capsule::raw('data, tipo_doc, num_doc, nome_cliente, no_obra,obra,
                                                             nome_obra, nome_agr_corr, peso,
                                                             ROUND(peso/baridade) AS m3,
                                                             ROUND(valor_in_ton * baridade,2) AS preco_m3,
                                                             ROUND(ROUND((peso/baridade),2)*ROUND(valor_in_ton*baridade,2),2) AS total_m3,
                                                             ROUND(valor_ex_ton*baridade * (1-desco),2) AS preco_vd,
                                                             ROUND(ROUND((peso/baridade),2) * ROUND(valor_ex_ton*baridade * (1-desco),2),2) AS total_v_m3'))
                                               ->leftjoin('centros_analiticos', 'ca_id', '=', 'obra')
                                               ->join('agregados', 'nome_agr', '=', 'nome_agre')
                                               ->join('baridades', 'agr_id', '=', 'agregado_id')
                                               ->join('valorun_interno_ton', 'agr_bar_id', '=', 'agregado_id')
                                               ->join('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agregado_id')
                                               ->join('obras', 'obra', '=', 'id_obra')
                                               ->whereBetween('data', [$data_inicial,$data_final])
                                               ->whereIn('tipo_doc', $check)
                                               ->whereIn('nome_agr', $lista_agregados_array)
                                               ->whereIn('nome_cliente', $array_cliente)
                                               ->whereIn('no_obra', $array_obras)
                                               ->get();

                    break;

                case empty($_POST['clientes']) && !empty($_POST['obras']):
                    $array_obras = $_POST['obras'];

                    $queryBalanca = Capsule::table('importacao_'.$cAnalitico)->select(Capsule::raw('data, tipo_doc, num_doc, nome_cliente, no_obra,obra,
                                                             nome_obra, nome_agr_corr, peso,
                                                             ROUND(peso/baridade) AS m3,
                                                             ROUND(valor_in_ton * baridade,2) AS preco_m3,
                                                             ROUND(ROUND((peso/baridade),2)*ROUND(valor_in_ton*baridade,2),2) AS total_m3,
                                                             ROUND(valor_ex_ton*baridade * (1-desco),2) AS preco_vd,
                                                             ROUND(ROUND((peso/baridade),2) * ROUND(valor_ex_ton*baridade * (1-desco),2),2) AS total_v_m3'))
                                               ->leftjoin('centros_analiticos', 'ca_id', '=', 'obra')
                                               ->join('agregados', 'nome_agr', '=', 'nome_agre')
                                               ->join('baridades', 'agr_id', '=', 'agregado_id')
                                               ->join('valorun_interno_ton', 'agr_bar_id', '=', 'agregado_id')
                                               ->join('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agregado_id')
                                               ->join('obras', 'obra', '=', 'id_obra')
                                               ->whereBetween('data', [$data_inicial,$data_final])
                                               ->whereIn('tipo_doc', $check)
                                               ->whereIn('nome_agr', $lista_agregados_array)
                                               ->whereIn('no_obra', $array_obras)
                                               ->get();

                  break;
            }


            $vars['valores'] = $queryBalanca;

            $vars['page'] = 'balanca/balanca';
            $vars['title'] = 'Mapa Balança';

            return $this->view->render($response, 'tabelas/balanca/balanca.twig', $vars);
        }
    }

    public function validateDate($date, $format = 'Y/m/d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
