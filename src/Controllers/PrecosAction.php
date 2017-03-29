<?php

namespace Src\Controllers;

use PDO;

/**
 *
 */
final class PrecosAction extends Action
{
    public function getSQL_interno($cInd, $tipoTabela, $destino, $mesActual, $unidade)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

        $cambioDoDia = $cambio[$_SESSION['ano']][$mesActual];
        $cIndus = $lista_agregados_array;


        $placeholders = str_repeat('?, ', count($cIndus) - 1) . '?';

        $query = "SELECT `nome_agr_corr` AS `brita`,
                        CASE
                        WHEN ? = 'usd' AND ? = 'externos' AND ? = 'toneladas'
                        THEN ROUND(`valor_ex_ton`,2)
                        WHEN ? = 'usd' AND ? = 'externos' AND ? = 'm3'
                        THEN ROUND(`valor_ex_ton` * `baridade`,2)
                        WHEN ? = 'usd' AND ? = 'internos' AND ? = 'toneladas'
                        THEN ROUND(`valor_in_ton`,2)
                        WHEN ? = 'usd' AND ? = 'internos' AND ? = 'm3'
                        THEN ROUND(`valor_in_ton` * `baridade`,2)

                        WHEN ? = 'aoa' AND ? = 'externos' AND ? = 'toneladas'
                        THEN TRIM(ROUND(`valor_ex_ton` * ?))+0
                        WHEN ? = 'aoa' AND ? = 'externos' AND ? = 'm3'
                        THEN TRIM(ROUND(`valor_ex_ton` * `baridade` * ?))+0
                        WHEN ? = 'aoa' AND ? = 'internos' AND ? = 'toneladas'
                        THEN TRIM(ROUND(`valor_in_ton` * ?))+0
                        WHEN ? = 'aoa' AND ? = 'internos' AND ? = 'm3'
                        THEN TRIM(ROUND(`valor_in_ton` * `baridade` * ?))+0
                        END AS `preco`
                  FROM `agregados`
                  LEFT JOIN `valorun_interno_ton`
                  ON `agr_id` = `agr_bar_id`
                  LEFT JOIN `valorun_externo_ton`
                  ON `agr_id` = `agr_bar_ton_id`
                  LEFT JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  WHERE `nome_agre` IN ($placeholders)
                  GROUP BY `nome_agr_corr`
                  ORDER BY `nome_agr_corr`
                 ";

        $rows = $this->db->prepare($query);
        $params = array_merge([$tipoTabela, $destino, $unidade,
                               $tipoTabela, $destino, $unidade,
                               $tipoTabela, $destino, $unidade,
                               $tipoTabela, $destino, $unidade,

                               $tipoTabela, $destino, $unidade, $cambioDoDia,
                               $tipoTabela, $destino, $unidade, $cambioDoDia,
                               $tipoTabela, $destino, $unidade, $cambioDoDia,
                               $tipoTabela, $destino, $unidade, $cambioDoDia],

                               $cIndus);
        $rows->execute($params);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);
        }
        return $vars['row'];
    }

    public function preco($request, $response)
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
            $listaPrecosArray[] = array('brita' => $value->brita, 'preco' => $value->preco);
        }

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
