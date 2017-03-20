<?php

namespace Src\Controllers;

/**
 *
 */
final class PpiamAction extends Action
{
    public function mapa($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $query = "SELECT `nome_agre` AS `nome`,
                         MONTH(`data_in`) AS `mes`,
                         `qt` AS `m3`,
                         ROUND((`valor_in_ton` * `baridade`),2) AS `pu`,
                         `qt` * ROUND(`valor_in_ton` * `baridade`) AS `total`
                  FROM `producoes_arimba`
                  JOIN `agregados`
                  ON `agr_id` = `cod_agr`
                  JOIN `baridades`
                  ON `cod_agr` = `agregado_id`
                  JOIN `valorun_interno_ton`
                  ON `cod_agr` = `agr_bar_id`
                  WHERE `nome_agre` IN ($lista_agregados) AND YEAR(`data_in`) IN ('$ano')
                  GROUP BY `nome_agr_corr`, MONTH(`data_in`)
                  ORDER by `nome_agr_corr`
                 ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $array = array();
            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                foreach ($lista_array_agregados as $agregado => $real) {
                    if ($value->nome === $agregado) {
                        $array[$real][$value->mes] = [
                                'nome' => $value->nome,
                                'mes' => $value->mes,
                                'm3' => $value->m3,
                                'pu' => $value->pu,
                                'total' => $value->total,
                            ];
                    }
                }
            }

            // 1- ordenar, 2- preencher e 3- renomear a cada array de producao
            foreach ($array as $key => $value) {
                //d($value[7]['nome']);
                for ($i = 1; $i <= 12; $i++) {
                    if (!isset($value[$i])) {
                        $producao[$key][$i] = [
                            'nome' => $key,
                            'mes' => $i,
                            'm3' => 0,
                            'pu' => 0,
                            'total' => 0,
                        ];
                    } else {
                        $producao[$key][$i] = $value[$i];
                    }
                }

                foreach ($producao as $key => $value) {
                    foreach ($multiarray_agregados as $agr => $real) {
                        for ($i=1; $i <= 12 ; $i++) {
                            if ($value[$i]['nome'] === $agr) {
                                $producao[$key][$i]['nome'] = $real[1];
                            }
                        }
                    }
                }
            }
        }

        //
        // Obter dados das Vendas internas
        //

        $query = "SELECT `nome_agr` AS `nome`,
                          MONTH(`data`) AS `mes`,
                          (ROUND(SUM(`peso` / `baridade`))) AS `m3`,
                          ROUND((`valor_in_ton` * `baridade`),2) AS `pu`,
                          ROUND((SUM(`peso` / `baridade`)) * ROUND(`valor_in_ton` * `baridade`)) AS `total`
                  FROM `importacao_arimba`
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agregado_id`
                  WHERE  `tipo_doc` IN ('GTO', 'PSA') AND `nome_agr` IN ($lista_agregados) AND YEAR(`data`) IN ('$ano')
                  GROUP BY `nome_agr_corr`, MONTH(`data`)
                  ORDER by `nome_agr_corr`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $array = array();
            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                foreach ($lista_array_agregados as $agregado => $real) {
                    if ($value->nome === $agregado) {
                        $array[$real][$value->mes] = [
                                'nome' => $value->nome,
                                'mes' => $value->mes,
                                'm3' => $value->m3,
                                'pu' => $value->pu,
                                'total' => $value->total,
                            ];
                    }
                }
            }

            // 1- ordenar, 2- preencher e 3- renomear a cada array de producao
            foreach ($array as $key => $value) {
                for ($i = 1; $i <= 12; $i++) {
                    if (!isset($value[$i])) {
                        $vInterna[$key][$i] = [
                            'nome' => $key,
                            'mes' => $i,
                            'm3' => 0,
                            'pu' => 0,
                            'total' => 0,
                        ];
                    } else {
                        $vInterna[$key][$i] = $value[$i];
                    }
                }

                foreach ($vInterna as $key => $value) {
                    foreach ($multiarray_agregados as $agr => $real) {
                        for ($i=1; $i <= 12 ; $i++) {
                            if ($value[$i]['nome'] === $agr) {
                                $vInterna[$key][$i]['nome'] = $real[1];
                            }
                        }
                    }
                }
            }
        }

        //
        // Obter dados das Vendas externas
        //

        $query = "SELECT `nome_agr` AS `nome`,
                          MONTH(`data`) AS `mes`,
                          (ROUND(SUM(`peso` / `baridade`))) AS `m3`,
                          ROUND((`valor_in_ton` * `baridade`),2) AS `pu`,
                          ROUND((SUM(`peso` / `baridade`)) * ROUND(`valor_in_ton` * `baridade`)) AS `total`
                  FROM `importacao_arimba`
                  LEFT JOIN `centros_analiticos`
                  ON `ca_id` = `obra`
                  JOIN `agregados`
                  ON `nome_agr` = `nome_agre`
                  JOIN `baridades`
                  ON `agr_id` = `agregado_id`
                  JOIN `valorun_interno_ton`
                  ON `agr_bar_id` = `agregado_id`
                  WHERE  `tipo_doc` IN ('GR', 'VD') AND `nome_agr` IN ($lista_agregados) AND YEAR(`data`) IN ('$ano')
                  GROUP BY `nome_agr_corr`, MONTH(`data`)
                  ORDER by `nome_agr_corr`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute();

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            $array = array();
            // Separar cada uma das linhas da query, por nome do agregado
            foreach ($vars['row'] as $key => $value) {
                foreach ($lista_array_agregados as $agregado => $real) {
                    if ($value->nome === $agregado) {
                        $array[$real][$value->mes] = [
                                'nome' => $value->nome,
                                'mes' => $value->mes,
                                'm3' => $value->m3,
                                'pu' => $value->pu,
                                'total' => $value->total,
                            ];
                    }
                }
            }

            // 1- ordenar, 2- preencher e 3- renomear a cada array de producao
            foreach ($array as $key => $value) {
                //d($value[7]['nome']);
                for ($i = 1; $i <= 12; $i++) {
                    if (!isset($value[$i])) {
                        $vExterna[$key][$i] = [
                            'nome' => $key,
                            'mes' => $i,
                            'm3' => 0,
                            'pu' => 0,
                            'total' => 0,
                        ];
                    } else {
                        $vExterna[$key][$i] = $value[$i];
                    }
                }

                foreach ($vExterna as $key => $value) {
                    foreach ($multiarray_agregados as $agr => $real) {
                        for ($i=1; $i <= 12 ; $i++) {
                            if ($value[$i]['nome'] === $agr) {
                                $vExterna[$key][$i]['nome'] = $real[1];
                            }
                        }
                    }
                }
            }


        // Cálculo do volume comercializado por mês

        $totalVendasInternas = array(1 => 0,
                                     2 => 0,
                                     3 => 0,
                                     4 => 0,
                                     5 => 0,
                                     6 => 0,
                                     7 => 0,
                                     8 => 0,
                                     9 => 0,
                                     10 => 0,
                                     11 => 0,
                                     12 => 0
                                    );

            $totalVendasExternas = $totalVendasInternas;
            $totalProducao = $totalVendasInternas;
            $pmi = $totalVendasInternas;
            $pme = $totalVendasInternas;

            for ($i=1; $i <= 12; $i++) {
                foreach ($vInterna as $key => $value) {
                    $totalVendasInternas[$i] += $value[$i]['m3'];
                }
            }

            for ($i=1; $i <= 12; $i++) {
                foreach ($vExterna as $key => $value) {
                    $totalVendasExternas[$i] += $value[$i]['m3'];
                }
            }

            $totalVendas = array();
            for ($i=1; $i <= 12 ; $i++) {
                $totalVendas[$i] = $totalVendasInternas[$i] + $totalVendasExternas[$i];
            }

        // Volume produzido por mês

        if (isset($producao)) {
            for ($i=1; $i <= 12; $i++) {
                foreach ($producao as $key => $value) {
                    $totalProducao[$i] += $value[$i]['m3'];
                }
            }
        } else {
            for ($i=1; $i <= 12; $i++) {
                $totalProducao[$i] = 0;
            }
        }


        // Valor facturação mensal

        $totalFacturaInterna = $totalVendasInternas;
            $totalFacturaExterna = $totalVendasInternas;
            $totalFacturacao = $totalVendasInternas;

            for ($i=1; $i <= 12; $i++) {
                foreach ($vInterna as $key => $value) {
                    $totalFacturaInterna[$i] += $value[$i]['m3'] * $value[$i]['pu'] * $cambio[$_SESSION['ano']][$i-1];
                }
            }

            for ($i=1; $i <= 12; $i++) {
                foreach ($vExterna as $key => $value) {
                    $totalFacturaExterna[$i] += $value[$i]['m3'] * $value[$i]['pu'] * $cambio[$_SESSION['ano']][$i-1];
                }
            }

            for ($i=1; $i <= 12 ; $i++) {
                $totalFacturacao[$i] = $totalFacturaInterna[$i] + $totalFacturaExterna[$i];
            }



        // Preço médio interno

        //$pmi = array();
        for ($i=1; $i <= 12 ; $i++) {
            if ($totalVendasInternas[$i] == 0) {
                $pmi[$i] = 0;
            } else {
                $pmi[$i] = round($totalFacturaInterna[$i] / $totalVendasInternas[$i], 2);
            }
        }

        // Preço médio externo

        $pme = $totalVendasInternas;
            for ($i=1; $i <= 12 ; $i++) {
                if ($totalVendasExternas[$i] === 0) {
                    $pme[$i] = 0;
                } else {
                    $pme[$i] = $totalFacturaExterna[$i] / $totalVendasExternas[$i];
                }
            }

        // Média de preços unitários mensais

        $mediaPrecos = $totalVendasInternas;

            for ($i = 1; $i <= 12 ; $i++) {
                if ($totalVendasInternas[$i] + $totalVendasExternas[$i] == 0) {
                    $mediaPrecos[$i] = 0;
                } else {
                    $mediaPrecos[$i] = round(($totalVendasInternas[$i] * $pmi[$i] + $totalVendasExternas[$i] * $pme[$i]) /
                                   ($totalVendasInternas[$i] + $totalVendasExternas[$i]), 0);
                }
            }

        // Formatar valores para inserir no gráfico
        for ($i=0; $i < 12; $i++) {
            if ($mediaPrecos[$i+1] == 0) {
                break;
            } else {
                $mediaPrecosGrafico[$i] = $mediaPrecos[$i+1];
            }
        }


            $vars['totalVendasInternas'] = $totalVendasInternas;
            $vars['totalVendasExternas'] = $totalVendasExternas;
            $vars['totalProducao'] = $totalProducao;
            $vars['mediaPrecos'] = $mediaPrecos;
            $vars['pmi'] = $pmi;
            $vars['pme'] = $pme;
            $vars['mediaPrecosGrafico'] = $mediaPrecosGrafico;
        } else {
            $vars['totalVendasInternas'] = 0;
            $vars['totalVendasExternas'] = 0;
            $vars['totalProducao'] = 0;
            $vars['mediaPrecos'] = 0;
            $vars['pmi'] = 0;
            $vars['pme'] = 0;
            $vars['mediaPrecosGrafico'] = 0;
        }
        $vars['page'] = 'mapas/ppiam/ppiam';
        $vars['title'] = 'MAPA RESUMO DE PPIAM';
        $vars['print'] = 'printPpiam';

        return $this->view->render($response, 'mapas/ppiam/ppiam.twig', $vars);
    }
}
