<?php

namespace Src\Controllers;

/**
 *
 */
final class MapaDpgmiAction extends Action
{
    public function getSQL($tipoDoc1, $tipoDoc2, $preco)
    {
        include 'src/Auxiliares/globals.php';

        $placeholders = str_repeat('?, ', count($lista_agregados_array) - 1) . '?';

        $tipos = [$tipoDoc1, $tipoDoc2];

        $placeholders2 = str_repeat('?, ', count($tipos) - 1) . '?';

        if ($tipoDoc1 === 'PRO') {
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
            $query = "SELECT `nome_agr` AS `nome`,
                             MONTH(`data`) AS `mes`,
                             (ROUND(SUM(`peso` / `baridade`))) AS `m3`,
                             ROUND((AVG($preco * `baridade`)),2) AS `pu`,
                             ROUND((SUM(`peso` / `baridade`)) * ROUND((AVG($preco * `baridade` * (1-`desco`))))) AS `total`
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
                      WHERE  `tipo_doc` IN ($placeholders2) AND `nome_agr` IN ($placeholders) AND YEAR(`data`) IN (?)
                      GROUP BY `nome_agr_corr`, MONTH(`data`)
                      ORDER by `nome_agr_corr`
                      ";

            $rows = $this->db->prepare($query);
            $params = array_merge($tipos, $lista_agregados_array, [$ano]);
            $rows->execute($params);
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

            // 1- ordenar, 2- preencher e 3- renomear a cada array de producao
            foreach ($array as $key => $value) {
                for ($i = 1; $i <= 12; $i++) {
                    if (!isset($value[$i])) {
                        $arrayAgregados[$key][$i] = [
                            'nome' => $key,
                            'mes' => $i,
                            'm3' => 0,
                            'pu' => 0,
                            'total' => 0,
                        ];
                    } else {
                        $arrayAgregados[$key][$i] = $value[$i];
                    }
                }

                foreach ($arrayAgregados as $key => $value) {
                    foreach ($multiarray_agregados as $agr => $real) {
                        for ($i=1; $i <= 12 ; $i++) {
                            if ($value[$i]['nome'] === $agr) {
                                $arrayAgregados[$key][$i]['nome'] = $real[1];
                            }
                        }
                    }
                }
            }
        } else {
            $arrayAgregados = [];
        }
        return $arrayAgregados;
    }

    public function geral($request, $response)
    {

        // Obter o caminho do $request
        $uri=$request->getUri();
        $path=$uri->getPath();

        $path = explode('/', $path);
        $pagina = $path[2];

        $mes = $request->getAttribute('pagina');


        switch (true) {
            case $pagina == 'producao':
                $this->producao();
                return $this->view->render($response, 'mapas/dpgmi/producao.twig', $this->producao());
                break;
            case $pagina == 'taxas':
                $this->taxas();
                return $this->view->render($response, 'mapas/dpgmi/taxas.twig', $this->taxas());
                break;
            case $pagina == 'forcatrabalho':
                $this->forcaTrabalho();
                if ($this->forcaTrabalho() == 'ERRO') {
                    return $this->view->render($response, 'mapas/dpgmi/erro.twig', $this->erro());
                } else {
                    return $this->view->render($response, 'mapas/dpgmi/forcaTrabalho.twig', $this->forcaTrabalho());
                }
                break;
            case $pagina == 'combustiveis':
                $this->combustiveis();
                return $this->view->render($response, 'mapas/dpgmi/combustiveis.twig', $this->combustiveis());
                break;
            case $pagina == 'resumo':
                $this->resumo();
                return $this->view->render($response, 'mapas/dpgmi/resumo.twig', $this->resumo());
                break;
            case $pagina == 'faixasEtarias':
                $this->faixas($mes);
                if ($this->faixas($mes) == 'ERRO') {
                    return $this->view->render($response, 'mapas/dpgmi/erro.twig', $this->erro());
                } else {
                    return $this->view->render($response, 'mapas/dpgmi/faixasetarias.twig', $this->faixas($mes));
                }

                break;

            default:
                $this->erro();
                return $this->view->render($response, 'mapas/dpgmi/erro.twig', $this->erro());
                break;
        }
    }

    public function producao()
    {
        include 'src/Auxiliares/globals.php';

        $producao = $this->getSQL('PRO', 'ENT', 'valor_in_ton');
        $vInterna = $this->getSQL('GTO', 'SPA', 'valor_in_ton');
        $vExterna = $this->getSQL('VD', 'GR', 'valor_ex_ton');

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

        // Volume Extraido

        for ($i=1; $i <= 12; $i++) {
            $totalIntacto[$i] = round($totalProducao[$i] / 1.88);
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

        // Média de preços unitários mensais

        $mediaPrecos = $totalVendasInternas;

        for ($i = 1; $i <= 12 ; $i++) {
            if ($totalVendas[$i] == 0) {
                $mediaPrecos[$i] = 0;
            } else {
                $mediaPrecos[$i] = $totalFacturacao[$i] / $totalVendas[$i];
            }
        }

        // Rodapé - Total Volume Extraido

        $rodapeVolExtraido = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeVolExtraido += ($totalIntacto[$i]);
        }

        // Rodapé - Total Volume Transformado

        $rodapeVolTrans = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeVolTrans += ($totalProducao[$i]);
        }

        // Rodapé - Total Volume comercializado

        $rodapeVolComer = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeVolComer += ($totalVendas[$i]);
        }

        // Rodapé - Total Facturação

        $rodapeFactura = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeFactura += ($totalFacturacao[$i]);
        }

        // Rodapé - Média preço venda

        $rodapePU = 0;

        if ($rodapeVolComer == 0) {
            $rodapePU = 0;
        } else {
            $rodapePU = $rodapeFactura / $rodapeVolComer ;
        }

        $vars['totalVendas'] = $totalVendas;
        $vars['totalProducao'] = $totalProducao;
        $vars['totalFacturacao'] = $totalFacturacao;
        $vars['mediaPrecos'] = $mediaPrecos;
        $vars['rodapeVolExtraido'] = $rodapeVolExtraido;
        $vars['rodapeVolTrans'] = $rodapeVolTrans;
        $vars['rodapeVolComer'] = $rodapeVolComer;
        $vars['rodapeFactura'] = $rodapeFactura;
        $vars['rodapePU'] = $rodapePU;
        $vars['ci'] = ucfirst($cAnalitico);
        $vars['totalIntacto'] = $totalIntacto;

        $vars['mes'] = $lista_meses;


        $vars['page'] = 'mapas/dpgmi/producao';
        $vars['title'] = 'MAPA RESUMO DE PRODUÇÃO E COMERCIALIZAÇÃO';
        $vars['print'] = 'printProducao';

        return $vars;
    }

    public function taxas()
    {
        include 'src/Auxiliares/globals.php';

        $producao = $this->getSQL('PRO', 'ENT', 'valor_in_ton');

        $totalExtraido = array(1 => 0,
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

        // Volume produzido por mês

        for ($i=1; $i <= 12; $i++) {
            foreach ($producao as $key => $value) {
                $totalExtraido[$i] += round($value[$i]['m3'] / 1.88) * $royalty * $custoExtraccao;
            }
        }

        // Rodapé - Total Volume Extraido

        $rodapeVolExtraido = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeVolExtraido += ($totalExtraido[$i]);
        }

        $vars['totalExtraido'] = $totalExtraido;
        $vars['rodapeVolExtraido'] = $rodapeVolExtraido;

        $vars['page'] = 'mapas/dpgmi/taxas';
        $vars['title'] = 'MAPA RESUMO DE TAXAS E IMPOSTOS';
        $vars['print'] = 'printTaxas';
        $vars['lista_meses'] = $lista_meses;

        return $vars;
    }

    public function forcaTrabalho()
    {
        include 'src/Auxiliares/globals.php';


        $query = "SELECT `nome_col`,
                         `data_nasc`,
                         MONTH(`data`) AS data,
                         `nacional`,
                         `sexo`
                  FROM `colaboradores`
                  LEFT JOIN `folha_ponto`
                  ON `num_mec` = `n_mec`
                  WHERE YEAR(`data`) = ?
                  GROUP BY `nome_col`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute([$ano]);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            // Inicializar arrays que conterão o numero de idades
            $nac_masculino = array(0 => 0, 1 => 0, 2 => 0, 3 => 0,
                                   4 => 0, 5 => 0, 6 => 0, 7 => 0,
                                   8 => 0, 9 => 0, 10 => 0, 11 => 0);
            $nac_feminino = array(0 => 0, 1 => 0, 2 => 0, 3 => 0,
                                   4 => 0, 5 => 0, 6 => 0, 7 => 0,
                                   8 => 0, 9 => 0, 10 => 0, 11 => 0);
            $exp_masculino = array(0 => 0, 1 => 0, 2 => 0, 3 => 0,
                                   4 => 0, 5 => 0, 6 => 0, 7 => 0,
                                   8 => 0, 9 => 0, 10 => 0, 11 => 0);
            $exp_feminino = array(0 => 0, 1 => 0, 2 => 0, 3 => 0,
                                   4 => 0, 5 => 0, 6 => 0, 7 => 0,
                                   8 => 0, 9 => 0, 10 => 0, 11 => 0);

           // Separar os colaboradores por idade/Sexo/mês de trabalho
           foreach ($vars['row'] as $key => $value) {
               switch (true) {
                   // Nacional masculino
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 1:
                           $nac_masculino[0] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 2:
                           $nac_masculino[1] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 3:
                           $nac_masculino[2] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 4:
                           $nac_masculino[3] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 5:
                           $nac_masculino[4] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 6:
                           $nac_masculino[5] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 7:
                           $nac_masculino[6] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 8:
                           $nac_masculino[7] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 9:
                           $nac_masculino[8] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 10:
                           $nac_masculino[9] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 11:
                           $nac_masculino[10] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'M' and $value->data == 12:
                           $nac_masculino[11] += 1;
                           break;
                   // Nacional feminino
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 1:
                           $nac_feminino[0] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 2:
                           $nac_feminino[1] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 3:
                           $nac_feminino[2] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 4:
                           $nac_feminino[3] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 5:
                           $nac_feminino[4] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 6:
                           $nac_feminino[5] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 7:
                           $nac_feminino[6] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 8:
                           $nac_feminino[7] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 9:
                           $nac_feminino[8] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 10:
                           $nac_feminino[9] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 11:
                           $nac_feminino[10] += 1;
                           break;
                   case $value->nacional == 'N' && $value->sexo == 'F' and $value->data == 12:
                           $nac_feminino[11] += 1;
                           break;
                   // Expatriado masculino
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 1:
                           $exp_masculino[0] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 2:
                           $exp_masculino[1] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 3:
                           $exp_masculino[2] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 4:
                           $exp_masculino[3] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 5:
                           $exp_masculino[4] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 6:
                           $exp_masculino[5] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 7:
                           $exp_masculino[6] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 8:
                           $exp_masculino[7] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 9:
                           $exp_masculino[8] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 10:
                           $exp_masculino[9] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 11:
                           $exp_masculino[10] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'M' and $value->data == 12:
                           $exp_masculino[11] += 1;
                           break;
                   // Expatriado feminino
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 1:
                           $exp_feminino['jan'] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 2:
                           $exp_feminino[1] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 3:
                           $exp_feminino[2] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 4:
                           $exp_feminino[3] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 5:
                           $exp_feminino[4] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 6:
                           $exp_feminino[5] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 7:
                           $exp_feminino[6] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 8:
                           $exp_feminino[7] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 9:
                           $exp_feminino[8] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 10:
                           $exp_feminino[9] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 11:
                           $exp_feminino[10] += 1;
                           break;
                   case $value->nacional == 'E' && $value->sexo == 'F' and $value->data == 12:
                           $exp_feminino[11] += 1;
                           break;

                   default:
                       break;
               }
           }

            for ($i=0; $i < 12; $i++) {
                $nac_duplo[$i] = $nac_masculino[$i] + $nac_feminino[$i];
                $exp_duplo[$i] = $exp_masculino[$i] + $exp_feminino[$i];
                $fem_duplo[$i] = $nac_feminino[$i] + $exp_feminino[$i];
                $mas_duplo[$i] = $nac_masculino[$i] + $exp_masculino[$i];
                $todos_colaboradores[$i] = $nac_feminino[$i] + $exp_feminino[$i] +
                                          $nac_masculino[$i] + $exp_masculino[$i];
            }
        } else {
            $vars['page'] = 'mapas/dpgmi/erro';
            $vars['title'] = 'ERRO';
            $vars['print'] = 'erro';

            return $vars['title'];
        }

        $vars['nac_masculino'] = $nac_masculino;
        $vars['nac_feminino'] = $nac_feminino;
        $vars['nac_duplo'] = $nac_duplo;
        $vars['exp_masculino'] = $exp_masculino;
        $vars['exp_feminino'] = $exp_feminino;
        $vars['exp_duplo'] = $exp_duplo;
        $vars['fem_duplo'] = $fem_duplo;
        $vars['mas_duplo'] = $mas_duplo;
        $vars['todos_colaboradores'] = $todos_colaboradores;
        $vars['lista_meses'] = $lista_meses;


        $vars['page'] = 'mapas/dpgmi/forcaTrabalho';
        $vars['title'] = 'MAPA DE FORÇA DE TRABALHO';
        $vars['print'] = 'printForcaTrabalho';

        return $vars;
    }

    public function combustiveis()
    {
        include 'src/Auxiliares/globals.php';

        $vars['page'] = 'mapas/dpgmi/combustiveis';
        $vars['title'] = 'MAPA CONSUMO COMBUSTIVEIS E LUBRIFICANTES';
        $vars['print'] = 'printCombustiveis';

        return $vars;
    }

    public function resumo()
    {
        include 'src/Auxiliares/globals.php';

        $producao = $this->getSQL('PRO', 'ENT', 'valor_in_ton');

        $totalExtraido = array(1 => 0,
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

        // Volume produzido por mês

        for ($i=1; $i <= 12; $i++) {
            foreach ($producao as $key => $value) {
                $totalExtraido[$i] += (round($value[$i]['m3'] / 1.88) * $royalty * $custoExtraccao) /  $cambio[$_SESSION['ano']][$i-1];
            }
        }

        // Rodapé - Total Volume Extraido

        $rodapeVolExtraido = 0;

        for ($i=1; $i <= 12; $i++) {
            $rodapeVolExtraido += ($totalExtraido[$i]);
        }

        $vars['totalExtraido'] = $totalExtraido;
        $vars['rodapeVolExtraido'] = $rodapeVolExtraido;

        $vars['page'] = 'mapas/dpgmi/resumo';
        $vars['title'] = 'MAPA RESUMO DE CUSTOS';
        $vars['print'] = 'printResumo';
        $vars['lista_meses'] = $lista_meses;

        return $vars;
    }

    public function faixas($pagina)
    {
        include 'src/Auxiliares/globals.php';

        $mes = (int)$pagina;

        $mesNumero = $mes;


        $query = "SELECT `nome_col`,
                         `data_nasc`,
                         MONTH(`data`) AS data,
                         `nacional`,
                         `sexo`
                  FROM `colaboradores`
                  LEFT JOIN `folha_ponto`
                  ON `num_mec` = `n_mec`
                  WHERE MONTH(`data`) = ? AND YEAR(`data`) = ?
                  GROUP BY `nome_col`
                  ";

        $rows = $this->db->prepare($query);
        $rows->execute([$mes, $ano]);

        if ($rows->rowCount() > 0) {
            $vars['row'] = $rows->fetchAll(\PDO::FETCH_OBJ);

            foreach ($vars['row'] as $key => $value) {
                $value->idade = floor((time() - strtotime($value->data_nasc)) / 31556926);
            }

            // separar os tipos de dados que se quer obtar
            $agrupamento = array('array_n_m_21', 'array_n_m_25', 'array_n_m_30',
                                 'array_n_m_40', 'array_n_m_45', 'array_n_m_50',
                                 'array_n_m_55', 'array_n_m_60',
                                 'array_n_f_21', 'array_n_f_25', 'array_n_f_30',
                                 'array_n_f_40', 'array_n_f_45', 'array_n_f_50',
                                 'array_n_f_55', 'array_n_f_60',
                                 'array_e_m_21', 'array_e_m_25', 'array_e_m_30',
                                 'array_e_m_40', 'array_e_m_45', 'array_e_m_50',
                                 'array_e_m_55', 'array_e_m_60',
                                 'array_e_f_21', 'array_e_f_25', 'array_e_f_30',
                                 'array_e_f_40', 'array_e_f_45', 'array_e_f_50',
                                 'array_e_f_55', 'array_e_f_60',
                             );

            foreach ($agrupamento as $key) {
                ${$key} = 0;
            }

            // Separar os colaboradores por idade/Sexo/nacionalidade
            foreach ($vars['row'] as $key => $value) {
                //dump($vars);
                switch (true) {
                    case $value->nacional === 'N' && $value->sexo === 'M' && $value->idade <= 21:
                            $array_n_m_21 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 25:
                            $array_n_m_25 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 30:
                            $array_n_m_30 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 40:
                            $array_n_m_40 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 45:
                            $array_n_m_45 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 50:
                            $array_n_m_50 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 55:
                            $array_n_m_55 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade > 55:
                            $array_n_m_60 += 1;
                            break;
                    // Nacional feminino
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 21:
                            $array_n_f_21 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 25:
                            $array_n_f_25 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 30:
                            $array_n_f_30 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 40:
                            $array_n_f_40 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 45:
                            $array_n_f_45 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 50:
                            $array_n_f_50 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 55:
                            $array_n_f_55 += 1;
                            break;
                    case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade > 55:
                            $array_n_f_60 += 1;
                            break;
                    // Expatriados
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 21:
                            $array_e_m_21 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 25:
                            $array_e_m_25 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 30:
                            $array_e_m_30 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 40:
                            $array_e_m_40 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 45:
                            $array_e_m_45 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 50:
                            $array_e_m_50 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 55:
                            $array_e_m_55 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade > 55:
                            $array_e_m_60 += 1;
                            break;
                    // Expatriado  feminino
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 21:
                            $array_e_f_21 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 25:
                            $array_e_f_25 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 30:
                            $array_e_f_30 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 40:
                            $array_e_f_40 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 45:
                            $array_e_f_45 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 50:
                            $array_e_f_50 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 55:
                            $array_e_f_55 += 1;
                            break;
                    case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade > 55:
                            $array_e_f_60 += 1;
                            break;
                    default:
                        break;
                }
            }

            $faixas_etarias = array('f_21' => array('nm' => $array_n_m_21, 'em' => $array_e_m_21,
                                                    'nf' => $array_n_f_21, 'ef' => $array_e_f_21),
                                    'f_25' => array('nm' => $array_n_m_25, 'em' => $array_e_m_25,
                                                    'nf' => $array_n_f_25, 'ef' => $array_e_f_25),
                                    'f_30' => array('nm' => $array_n_m_30, 'em' => $array_e_m_30,
                                                    'nf' => $array_n_f_30, 'ef' => $array_e_f_30),
                                    'f_40' => array('nm' => $array_n_m_40, 'em' => $array_e_m_40,
                                                    'nf' => $array_n_f_40, 'ef' => $array_e_f_40),
                                    'f_45' => array('nm' => $array_n_m_45, 'em' => $array_e_m_45,
                                                    'nf' => $array_n_f_45, 'ef' => $array_e_f_45),
                                    'f_50' => array('nm' => $array_n_m_50, 'em' => $array_e_m_50,
                                                    'nf' => $array_n_f_50, 'ef' => $array_e_f_50),
                                    'f_55' => array('nm' => $array_n_m_55, 'em' => $array_e_m_55,
                                                    'nf' => $array_n_f_55, 'ef' => $array_e_f_55),
                                    'f_60' => array('nm' => $array_n_m_60, 'em' => $array_e_m_60,
                                                    'nf' => $array_n_f_60, 'ef' => $array_e_f_60),
                                );

            $faixas = ['18 - 21 anos','22 - 25 anos',
                      '26 - 30 anos',  '31 - 40 anos',
                      '41 - 45 anos', '46 - 50 anos',
                      '51 - 55 anos',  '56 - 60 anos'];

            // totais parciais de cada faixa
            $ate_21 = $faixas_etarias['f_21']['nm'] + $faixas_etarias['f_21']['em'] +
                     $faixas_etarias['f_21']['nf'] + $faixas_etarias['f_21']['ef'];
            $ate_25 = $faixas_etarias['f_25']['nm'] + $faixas_etarias['f_25']['em'] +
                     $faixas_etarias['f_25']['nf'] + $faixas_etarias['f_25']['ef'];
            $ate_30 = $faixas_etarias['f_30']['nm'] + $faixas_etarias['f_30']['em'] +
                     $faixas_etarias['f_30']['nf'] + $faixas_etarias['f_30']['ef'];
            $ate_40 = $faixas_etarias['f_40']['nm'] + $faixas_etarias['f_40']['em'] +
                     $faixas_etarias['f_40']['nf'] + $faixas_etarias['f_40']['ef'];
            $ate_45 = $faixas_etarias['f_45']['nm'] + $faixas_etarias['f_45']['em'] +
                     $faixas_etarias['f_45']['nf'] + $faixas_etarias['f_45']['ef'];
            $ate_50 = $faixas_etarias['f_50']['nm'] + $faixas_etarias['f_50']['em'] +
                     $faixas_etarias['f_50']['nf'] + $faixas_etarias['f_50']['ef'];
            $ate_55 = $faixas_etarias['f_55']['nm'] + $faixas_etarias['f_55']['em'] +
                     $faixas_etarias['f_55']['nf'] + $faixas_etarias['f_55']['ef'];
            $ate_60 = $faixas_etarias['f_60']['nm'] + $faixas_etarias['f_60']['em'] +
                     $faixas_etarias['f_60']['nf'] + $faixas_etarias['f_60']['ef'];

            $parcial_array = [$ate_21, $ate_25, $ate_30, $ate_40, $ate_45, $ate_50, $ate_55, $ate_60];


            // Totais do rodapé da tabela
            $total_N_M = $faixas_etarias['f_21']['nm'] + $faixas_etarias['f_25']['nm'] + $faixas_etarias['f_30']['nm'] +
                        $faixas_etarias['f_40']['nm'] + $faixas_etarias['f_45']['nm'] +
                        $faixas_etarias['f_50']['nm'] + $faixas_etarias['f_55']['nm'] + $faixas_etarias['f_60']['nm'];

            $total_N_F = $faixas_etarias['f_21']['nf'] + $faixas_etarias['f_25']['nf'] + $faixas_etarias['f_30']['nf'] +
                         $faixas_etarias['f_40']['nf'] + $faixas_etarias['f_45']['nf'] +
                         $faixas_etarias['f_50']['nf'] + $faixas_etarias['f_55']['nf'] + $faixas_etarias['f_60']['nf'];

            $total_E_M = $faixas_etarias['f_21']['em'] + $faixas_etarias['f_25']['em'] + $faixas_etarias['f_30']['em'] +
                        $faixas_etarias['f_40']['em'] + $faixas_etarias['f_45']['em'] +
                        $faixas_etarias['f_50']['em'] + $faixas_etarias['f_55']['em'] + $faixas_etarias['f_60']['em'];

            $total_E_F = $faixas_etarias['f_21']['ef'] + $faixas_etarias['f_25']['ef'] + $faixas_etarias['f_30']['ef'] +
                        $faixas_etarias['f_40']['ef'] + $faixas_etarias['f_45']['ef'] +
                        $faixas_etarias['f_50']['ef'] + $faixas_etarias['f_55']['ef'] + $faixas_etarias['f_60']['ef'];

            $total_array = $ate_21 + $ate_25 + $ate_30 + $ate_40 + $ate_45 + $ate_50 + $ate_55 + $ate_60;



            switch (true) {
            case $mes == 1:
                $mes = 'janeiro';
                break;
            case $mes == 2:
                $mes = 'fevereiro';
                break;
            case $mes == 3:
                $mes = 'março';
            break;
            case $mes == 4:
                $mes = 'abril';
                break;
            case $mes == 5:
                $mes = 'maio';
                break;
            case $mes == 6:
                $mes = 'junho';
                break;
            case $mes == 7:
                $mes = 'julho';
                break;
            case $mes == 8:
                $mes = 'agosto';
                break;
            case $mes == 9:
                $mes = 'setembro';
                break;
            case $mes == 10:
                $mes = 'outubro';
                break;
            case $mes == 11:
                $mes = 'novembro';
                break;
            case $mes == 12:
                $mes = 'dezembro';
                break;

            default:
                break;
        }

            $var['mesNumero'] = $mesNumero;
            $var['mes'] = $mes;
            $var['faixas_etarias'] = $faixas_etarias;
            $var['faixas'] = $faixas;
            $var['parcial_array'] = $parcial_array;
            $var['total_N_M'] = $total_N_M;
            $var['total_N_F'] = $total_N_F;
            $var['total_E_M'] = $total_E_M;
            $var['total_E_F'] = $total_E_F;
            $var['total_array'] = $total_array;

            $var['page'] = 'mapas/dpgmi/faixasetarias';
            $var['title'] = 'MAPA RESUMO DE GRUPOS ETÁRIOS';
            $var['print'] = 'printFaixasEtarias';



            return  $var;
        } else {
            $vars['page'] = 'mapas/dpgmi/erro';
            $vars['title'] = 'ERRO';
            $vars['print'] = 'erro';

            return $vars['title'];
        }
    }

    public function erro()
    {
        $vars['page'] = 'mapas/dpgmi/erro';
        $vars['title'] = 'ERRO';
        $vars['print'] = 'erro';

        return $vars;
    }
}
