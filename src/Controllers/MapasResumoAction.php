<?php
namespace Src\Controllers;

/**
 *
 */
final class MapasResumoAction extends Action
{
    public function mapas($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        include 'src/Auxiliares/helpers.php';

        $tipo = $request->getAttribute('item');


        if ($tipo === 'forninterno') {
            $op1 = 'GTO';
            $op2 = 'SPA';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'FORNECIMENTO INTERNO - PREÇO SECO';
            $label1 = 'Quantidade fornecida (m3)';
            $label2 = 'Valor facturado (USD)';
        } elseif ($tipo === 'fornexternoseco') {
            $op1 = 'VD';
            $op2 = 'GR';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'FORNECIMENTO EXTERNO - PREÇO SECO';
            $label1 = 'Quantidade fornecida (m3)';
            $label2 = 'Valor facturado - preço seco (USD)';
        } elseif ($tipo === 'fornexternovenda') {
            $op1 = 'VD';
            $op2 = 'GR';
            $preco = 'valor_ex_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'FORNECIMENTO EXTERNO - PREÇO DE VENDA';
            $label1 = 'Quantidade fornecida (m3)';
            $label2 = 'Valor facturado - preço venda (USD)';
        } elseif ($tipo === 'producao') {
            $op1 = 'PRO';
            $op2 = 'PRO';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'PRODUÇÃO - PREÇO SECO';
            $label1 = 'Quantidade produzida (m3)';
            $label2 = 'Valor produção (USD)';
        } elseif ($tipo === 'acertos') {
            $op1 = 'ACE';
            $op2 = 'ACE';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'ACERTOS DE STOCK - PREÇO SECO';
            $label1 = 'Quantidade de acerto de stock (m3)';
            $label2 = 'Valor de acerto (USD)';
        } elseif ($tipo === 'rejeitado') {
            $op1 = 'REJ';
            $op2 = 'REJ';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'MATERIAL REJEITADO - PREÇO SECO';
            $label1 = 'Quantidade rejeitada (m3)';
            $label2 = 'Valor rajeitado (USD)';
        } elseif ($tipo === 'oferecido') {
            $op1 = 'OFE';
            $op2 = 'OFE';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'MATERIAL OFERECIDO - PREÇO SECO';
            $label1 = 'Quantidade oferecida (m3)';
            $label2 = 'Valor oferecido (USD)';
        } elseif ($tipo === 'entradas') {
            $op1 = 'ENT';
            $op2 = 'ENT';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'ENTRADAS EXTERNAS - PREÇO SECO';
            $label1 = 'Quantidade recebida (m3)';
            $label2 = 'Valor recebido (USD)';
        } elseif ($tipo === 'entradastock') {
            $op1 = 'PRO';
            $op2 = 'ENT';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'ENTRADAS PARA STOCK - PREÇO SECO';
            $label1 = 'Quantidade adicionada (m3)';
            $label2 = 'Valor adicionado (USD)';
        } elseif ($tipo === 'producaomensal') {
            $op1 = 'PRO';
            $op2 = 'ENT';
            $preco = 'valor_in_ton';
            $vars['page'] = 'mapa';
            $vars['title'] = 'ENTRADAS PARA STOCK - PREÇO SECO';
            $label1 = 'Quantidade adicionada (m3)';
            $label2 = 'Valor adicionado (USD)';
        }

        $placeholders = str_repeat('?, ', count($lista_agregados_array) - 1) . '?';
        $tipos = [$op1, $op2];
        $placeholders2 = str_repeat('?, ', count($tipos) - 1) . '?';

        if ($op1 === 'PRO') {
            $query = "SELECT `nome_agre` AS `nome`,
                             MONTH(`data_in`) AS `mes`,
                             `qt` AS `m3`,
                             ROUND(`valor_in_ton` * `baridade`,2) AS `pu`,
                             `qt` * ROUND(`valor_in_ton` * `baridade`) AS `total`
                      FROM `producoes_arimba`
                      JOIN `agregados`
                      ON `agr_id` = `cod_agr`
                      JOIN `baridades`
                      ON `cod_agr` = `agregado_id`
                      JOIN `valorun_interno_ton`
                      ON `cod_agr` = `agr_bar_id`
                      WHERE `nome_agre` IN ($placeholders) AND YEAR(`data_in`) IN (?)
                      GROUP BY `nome_agr_corr`, MONTH(`data_in`)
                      ORDER by `nome_agr_corr`
                     ";

            $rows = $this->db->prepare($query);
            $params = array_merge($lista_agregados_array, [$ano]);
            $rows->execute($params);
        } else {
            // dump($preco);
            $rows =

            $query = "SELECT `nome_agr` AS `nome`,
                             MONTH(`data`) AS `mes`,
                             (ROUND(SUM(`peso` / `baridade`))) AS `m3`,
                             ROUND((AVG($preco * `baridade`)),2) AS `pu`,
                             ROUND((SUM(`peso` / `baridade`)) * ROUND((AVG($preco * `baridade`)))) AS `total`
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
                      WHERE  `tipo_doc` IN ($placeholders2) AND `nome_agr` IN ($placeholders) AND YEAR(`data`) IN (?)
                      GROUP BY `nome_agr_corr`, MONTH(data)
                      ORDER by `nome_agr_corr`
                      ";

            $rows = $this->db->prepare($query);
            $params = array_merge($tipos, $lista_agregados_array, [$ano]);
            $rows->execute($params);
            // dump($query);
        }



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

            // 1- ordenar, 2- preencher e 3- renomear a cada array de arrayValores
            foreach ($array as $key => $value) {
                for ($i = 1; $i <= 12; $i++) {
                    if (!isset($value[$i])) {
                        $arrayValores[$key][$i] = [
                            'nome' => $key,
                            'mes' => $i,
                            'm3' => 0,
                            'pu' => 0,
                            'total' => 0,
                        ];
                    } else {
                        $arrayValores[$key][$i] = $value[$i];
                    }
                }

                foreach ($arrayValores as $key => $value) {
                    foreach ($multiarray_agregados as $agr => $real) {
                        for ($i=1; $i <= 12 ; $i++) {
                            if ($value[$i]['nome'] === $agr) {
                                $arrayValores[$key][$i]['nome'] = $real[1];
                            }
                        }
                    }
                }
            }


            // Agregados existentes na base de dados
            $agregados = array();
            foreach ($arrayValores as $key => $value) {
                if (in_array($key, $agregados)) {
                    continue;
                } else {
                    array_push($agregados, $key);
                }
            }

            // Declaração da variavel quantidade anual
            foreach ($agregados as $key) {
                $qtAnual[$key] = 0;
                $factAnual[$key] = 0;
            }

            // quantidade fornecida por ano em m3
            $qtMensal = [];
            for ($i=1; $i <= 12 ; $i++) {
                $qtMensal[$i] = 0;
            }

            for ($i=1; $i <= 12 ; $i++) {
                foreach ($arrayValores as $key => $value) {
                    $qtMensal[$i] += $value[$i]['m3'];
                }
            }

            // quantidade fornecida por mês em m3
            foreach ($arrayValores as $key => $value) {
                for ($i=1; $i <= 12 ; $i++) {
                    $qtAnual[$key] += $value[$i]['m3'];
                }
            }

            // facturação por ano em akz
            $factMensal = [];
            for ($i=1; $i <= 12 ; $i++) {
                $factMensal[$i] = 0;
            }

            for ($i=1; $i <= 12 ; $i++) {
                foreach ($arrayValores as $key => $value) {
                    $factMensal[$i] += $value[$i]['m3'] * $value[$i]['pu'];
                }
            }

            // facturação por mês em akz
            foreach ($arrayValores as $key => $value) {
                for ($i=1; $i <= 12 ; $i++) {
                    $factAnual[$key] += $value[$i]['m3'] * $value[$i]['pu'];
                }
            }

            // Agregados em db com nome abreviados
            if (isset($qtAnual)) {
                $agrCorrectos = $qtAnual;
                foreach ($qtAnual as $key => $value) {
                    foreach ($agregados_nome as $agr => $agrNome) {
                        if ($key == $agrNome) {
                            $agrCorrectos[$key] = $agr;
                        }
                    }
                }
            } else {
                $agrCorrectos = [];
            }


            // Média de preços mensais
            for ($i=1; $i <= 12 ; $i++) {
                if ($qtMensal[$i] != 0) {
                    $media_precos[$i] = $factMensal[$i] / $qtMensal[$i];
                } else {
                    $media_precos[$i] = 0;
                }
            }


            // Média preços anuais
            foreach ($agrCorrectos as $key => $value) {
                if ($qtAnual[$key] != 0) {
                    $media_precos_anual[$key] = $factAnual[$key] / $qtAnual[$key];
                } else {
                    $media_precos_anual[$key] = 0;
                }
            }

            // Total dos totais anuais em m3
            $qtTotalAnual = array_sum($qtAnual);

            // Total dos totais anuais em kz
            $factTotalAnual = array_sum($factAnual);

            // Média dos totais anuais
            if ($qtAnual == 0) {
                $mediaAnualRodape = 0;
            } else {
                $mediaAnualRodape = round($factTotalAnual / $qtTotalAnual, 2);
            }

            // Formatar valores para inserir no gráfico
            for ($i=1; $i <= 12; $i++) {
                if ($qtMensal[$i] == 0) {
                    $qtMensalGrafico[$i-1] = null;
                } else {
                    $qtMensalGrafico[$i-1] = $qtMensal[$i];
                }
            }

            for ($i=1; $i <= 12; $i++) {
                if ($factMensal[$i] == 0) {
                    $factMensalGrafico[$i-1] = null;
                } else {
                    $factMensalGrafico[$i-1] = round($factMensal[$i], 0);
                }
            }

            $vars['arrayValores'] = $arrayValores;
            $vars['qtAnual'] = $qtAnual;
            $vars['qtMensal'] = $qtMensal;
            $vars['factAnual'] = $factAnual;
            $vars['factMensal'] = $factMensal;
            $vars['agrCorrectos'] = $agrCorrectos;
            $vars['media_precos'] = $media_precos;
            $vars['media_precos_anual'] = $media_precos_anual;
            $vars['qtTotalAnual'] = $qtTotalAnual;
            $vars['factTotalAnual'] = $factTotalAnual;
            $vars['mediaAnualRodape'] = $mediaAnualRodape;

            # gráfico
            $vars['qtMensalGrafico'] = $qtMensalGrafico;
            $vars['factMensalGrafico'] = $factMensalGrafico;
            $vars['label1'] = $label1;
            $vars['label2'] = $label2;
        } else {
            $vars['arrayValores'] = [];
            $vars['qtAnual'] = [];
            $vars['qtMensal'] = [];
            $vars['factAnual'] = [];
            $vars['factMensal'] = [];
            $vars['agrCorrectos'] = [];
            $vars['media_precos'] = [];
            $vars['media_precos_anual'] = 0;
            $vars['qtTotalAnual'] = 0;
            $vars['factTotalAnual'] = 0;
            $vars['mediaAnualRodape'] = 0;

            # gráfico
            $vars['qtMensalGrafico'] = 0;
            $vars['factMensalGrafico'] = 0;
            $vars['label1'] = $label1;
            $vars['label2'] = $label2;
        }

        return $this->view->render($response, 'mapas/mrpf/' . $vars['page'] .'.twig', $vars);
    }
}
