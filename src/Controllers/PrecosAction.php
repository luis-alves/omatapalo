<?php

namespace Src\Controllers;

/**
 *
 */
final class PrecosAction extends Action
{
    function getSQL_interno ($cInd, $tipoTabela, $destino, $mesActual, $unidade)
    {
        include 'src/Auxiliares/globals.php';

        $cambioDoDia = $cambio[$_SESSION['ano']][$mesActual];
        $cIndus = "'".implode("', '", array_keys($cInd[0]))."'";

        $query = "SELECT `nome_agr_corr` AS `brita`,
                        CASE
                        WHEN '$tipoTabela' = 'usd' AND '$destino' = 'externos' AND '$unidade' = 'toneladas'
                        THEN ROUND(`valor_ex_ton`,2)
                        WHEN '$tipoTabela' = 'usd' AND '$destino' = 'externos' AND '$unidade' = 'm3'
                        THEN ROUND(`valor_ex_ton` * `baridade`,2)
                        WHEN '$tipoTabela' = 'usd' AND '$destino' = 'internos' AND '$unidade' = 'toneladas'
                        THEN ROUND(`valor_in_ton`,2)
                        WHEN '$tipoTabela' = 'usd' AND '$destino' = 'internos' AND '$unidade' = 'm3'
                        THEN ROUND(`valor_in_ton` * `baridade`,2)

                        WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'externos' AND '$unidade' = 'toneladas'
                        THEN TRIM(ROUND(`valor_ex_ton` * $cambioDoDia))+0
                        WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'externos' AND '$unidade' = 'm3'
                        THEN TRIM(ROUND(`valor_ex_ton` * `baridade` * $cambioDoDia))+0
                        WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'internos' AND '$unidade' = 'toneladas'
                        THEN TRIM(ROUND(`valor_in_ton` * $cambioDoDia))+0
                        WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'internos' AND '$unidade' = 'm3'
                        THEN TRIM(ROUND(`valor_in_ton` * `baridade` * $cambioDoDia))+0
                        END AS `preco`
                  FROM `agregados`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_id` = `agr_bar_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_id` = `agr_bar_ton_id`
                  LEFT JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  WHERE `nome_agre` IN ($cIndus)
                  GROUP BY `nome_agr_corr`
                  ORDER BY `nome_agr_corr`
                 ";

         $rows = $this->db->prepare($query);
         $rows->execute();

         $array = array();

         if ($rows->rowCount() > 0) {
             $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
         }

         return $vars['row'];

    }

    function preco ($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $tipoTabela = $request->getAttribute('moeda');
        $destino = $request->getAttribute('destino');
        $unidade = $request->getAttribute('unidade');

        $mesActual = date('n');

        $cInd[] = ${$cAnalitico.'Agregados'};

        $cIndCapitais = strtoupper($cAnalitico);
        $destinoCapitais = strtoupper($destino);

        $listaPrecos = $this->getSQL_interno($cInd, $tipoTabela, $destino, $mesActual, $unidade);

        $numeroPrecos = count($listaPrecos);

        $listaPrecosArray = array();
        foreach ($listaPrecos as $key => $value) {
                // dump($value->brita);
                $listaPrecosArray[] = array('brita' => $value->brita, 'preco' => $value->preco);
        }

        // dump($listaPrecosArray);



        $vars['page'] = 'precos';
        $vars['cInd'] = $cInd;
        $vars['print'] = 'printPrecos';
        $vars['cIndCapitais'] = $cIndCapitais;
        $vars['mesActual'] = $lista_meses[$mesActual-1];
        $vars['tipoTabela'] = $tipoTabela;
        $vars['tipoTabela'] = $tipoTabela;
        $vars['destino'] = $destino;
        $vars['listaPrecosArray'] = $listaPrecosArray;
        $vars['numeroPrecos'] = $numeroPrecos;
        $vars['unidade'] = $unidade;
        $vars['destinoCapitais'] = $destinoCapitais;

        return $this->view->render($response, 'tabelas/precos/'.$vars['page'].'.twig', $vars);





    }











}




 ?>
