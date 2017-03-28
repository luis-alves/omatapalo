<?php

namespace Src\Controllers;

use Src\Auxiliares\Query as Query;
use Src\Models\VendasArimba as VendasArimba;
use Src\Models\Compras as Compras;
use Src\Models\MaoDeObra as MaoDeObra;

/**
 *
 */
class DResultadosAction extends Action
{
    private $fse = array( "Combustíveis",
                          "Conservação e Reparação",
                          "Contecioso e Notariado",
                          "Ferram. e Utens. Desg. Rápido",
                          "Material de Protecção e Segurança",
                          "Material Eléctrico",
                          "Outros Custos Vários",
                          "Publicidade e Propaganda",
                          "Subcontratos/SubEmpreitadas",
                          "Comunicação",
                          "Deslocações e Estadas",
                          "Material de Escritório",
                          "Material Informático",
                          "Vigilância e Segurança",
                          "Rendas e Alugueres",
                          "Água",
                          "Seguros",
                          "Livros e Documentação Técnica",
                          "Despesas Representação",
                          "Trabalhos Especializados",
                          "Artigos para Oferta",
                        );

    // Familias pertencentes a Pessoal
    private $pessoal = array( "Alimentação",
                              "Despesas de Saúde",
                              "Limpeza. Higiene e Conforto",
                              "Alojamento",
                              "Salários Expatriados",
                              "Salários Nacionais",
                              "Trabalhos Especializados",
                              "Horas Extras e Prémios",
                        );

    public function DResultados($request, $response)
    {
        include 'src/Auxiliares/globals.php';
        // include 'src/Auxiliares/helpers.php';

        $sql = new Query();

        $vendasExternas = $sql::getSQLVendas("GR", "VD");
        $vendasInternas = $sql::getSQLVendas("GTO", "SPA");

        # Agrupar por meses
        for ($i = 1; $i < 13; $i++) {
            $vendasInternasOrd[$i] = 0;
            foreach ($vendasInternas as $indice=> $objecto) {
                if ($objecto->mes == $i) {
                    $vendasInternasOrd[$i] += $objecto->total;
                }
            }
        }

        for ($i=1; $i <= 12; $i++) {
            if (empty($vendasInternasOrd[$i])) {
                $vendasInternasOrd[$i] = 0;
            }
        }

        for ($i = 1; $i < 13; $i++) {
            $vendasExternasOrd[$i] = 0;
            foreach ($vendasExternas as $indice=> $objecto) {
                if ($objecto->mes == $i) {
                    $vendasExternasOrd[$i] += $objecto->total;
                }
            }
        }

        for ($i=1; $i <= 12; $i++) {
            if (empty($vendasExternasOrd[$i])) {
                $vendasExternasOrd[$i] = 0;
            }
        }

        # Total vendido (interno + externo)
        for ($i=1; $i < 13; $i++) {
            $totalVendas[$i] = $vendasExternasOrd[$i] + $vendasInternasOrd[$i];
        }

        $somatorioTotalVendas = 0;
        for ($i=1; $i < 13; $i++) {
            $somatorioTotalVendas += $totalVendas[$i];
        }

        $totalVendasExternas = 0;
        $totalVendasInternas = 0;
        for ($i=1; $i < 13; $i++) {
            $totalVendasExternas += $vendasExternasOrd[$i];
            $totalVendasInternas += $vendasInternasOrd[$i];
        }

        # Percentagens das vendas
        if ($somatorioTotalVendas > 0) {
            $percVendasExternas = $totalVendasExternas / $somatorioTotalVendas * 100;
            $percVendasInternas = $totalVendasInternas / $somatorioTotalVendas * 100;
        } else {
            $percVendasExternas = 0;
            $percVendasInternas = 0;
        }

        $percTotalVendas = $percVendasExternas + $percVendasInternas;


        # Dados da produção
        $producoesOrd = $sql::getSQLProducoes("PRO", "ENT");

        # Ordenar por array de meses (On^2)
        #
        for ($i=0; $i < 12; $i++) {
            $producoes[$i+1] = 0;
            foreach ($producoesOrd as $key => $value) {
                if ($value->mes - 1 == $i) {
                    $producoes[$value->mes] += $value->total;
                }
            }
        }

        # Inserir meses sem valores (O n.n^2)
        #
        for ($i=1; $i <= 12; $i++) {
            if (empty($producoes[$i])) {
                $producoes[$i] = 0;
            }
        }

        $totalProducoes = 0;
        $proveitosOperacionais = array();
        $totalProveitosOperacionais = 0;
        $percTotalProducoes = 0;

        for ($i=1; $i < 13; $i++) {
            $totalProducoes += $producoes[$i];
        }

        if ($totalProducoes > 0) {
            for ($i=1; $i < 13; $i++) {
                $proveitosOperacionais[$i] = ($producoes[$i] / $totalProducoes * 100)."%";
            }
        } else {
            for ($i=1; $i < 13; $i++) {
                $proveitosOperacionais[$i] = (0)."%";
            }
        }

        $percTotalProducoes = $totalProducoes / $totalProducoes * 100;

        for ($i=1; $i < 13; $i++) {
            $totalProveitosOperacionais += $proveitosOperacionais[$i];
        }

        $custosGerais = $sql::getSQL_custos();

        // Inicializar todas as variáveis possiveis de custos
        $familasCustos = array( "Alimentação" => 0,
                                "Combustíveis" => 0,
                                "Conservação e Reparação" => 0,
                                "Contecioso e Notariado" => 0,
                                "Despesas de Saúde" => 0,
                                "Ferram. e Utens. Desg. Rápido" => 0,
                                "Limpeza. Higiene e Conforto" => 0,
                                "Materiais Diversos" => 0,
                                "Material de Protecção e Segurança" => 0,
                                "Material Eléctrico" => 0,
                                "Outros Custos Vários" => 0,
                                "Publicidade e Propaganda" => 0,
                                "Subcontratos/SubEmpreitadas" => 0,
                                "Transportes" => 0,
                                "Transportes Externos" => 0,
                                "Comunicação" => 0,
                                "Matérias-Primas" => 0,
                                "Alojamento" => 0,
                                "Salários Expatriados" => 0,
                                "Salários Nacionais" => 0,
                                "Deslocações e Estadas" => 0,
                                "Horas Extras e Prémios" => 0,
                                "Material de Escritório" => 0,
                                "Material Informático" => 0,
                                "Vigilância e Segurança" => 0,
                                "Rendas e Alugueres" => 0,
                                "Equipamento" => 0,
                                "Equipamento Externo" => 0,
                                "Água" => 0,
                                "Seguros" => 0,
                                "Livros e Documentação Técnica" => 0,
                                "Despesas Representação" => 0,
                                "Trabalhos Especializados" => 0,
                                "Artigos para Oferta" => 0,
                                "Amortizações" => 0,
                                "Impostos" => 0,
                                );

        $custosGeraisOrd = array(1=>array());

        for ($i=1; $i < 13; $i++) {
            $custosGeraisOrd[$i] = $familasCustos;
        }

        if (!empty($custosGerais)) {
            // Converter objecto para matriz multidimensional
            $arrayCustosGerais =json_decode(json_encode($custosGerais), true);

            for ($i=1; $i < 13; $i++) {
                foreach ($custosGerais as $numero => $value) {
                    if ($value->mes == $i) {
                        $custosGeraisOrd[$i][$value->familia] = $value->valor;
                    }
                }
            }

            // Somatórios de todos artigos (linhas)
            $totalMateriaisDiversos = 0;
            $totalMateriasPrimas = 0;
            $totalEquipamentoInterno = 0;
            $totalEquipamentoExterno = 0;
            $totalEquipamentoMensal = array();
            $totalTransporteInterno = 0;
            $totalTransporteExterno = 0;
            $totalImpostos = 0;
            $totalAmortizacoes = 0;
            $totalTransporteMensal = array();
            $totalCustosFinanceiro = array();
            foreach ($this->fse as $key) {
                $totalFSE[$key] = 0;
            }


            for ($i=1; $i < 13; $i++) {
                $totalFSEMensal[$i] = 0;
                $totalMateriaisDiversos += $custosGeraisOrd[$i]['Materiais Diversos'];
                $totalMateriasPrimas += round($custosGeraisOrd[$i]['Matérias-Primas']);
                $totalEquipamentoInterno += round($custosGeraisOrd[$i]['Equipamento']);
                $totalEquipamentoExterno += round($custosGeraisOrd[$i]['Equipamento Externo']);
                $totalEquipamentoMensal[$i] = $custosGeraisOrd[$i]['Equipamento'] + $custosGeraisOrd[$i]['Equipamento Externo'];
                $totalTransporteInterno += round($custosGeraisOrd[$i]['Transportes']);
                $totalTransporteExterno += round($custosGeraisOrd[$i]['Transportes Externos']);
                $totalTransporteMensal[$i] = $custosGeraisOrd[$i]['Transportes'] + $custosGeraisOrd[$i]['Transportes Externos'];
                $totalCustosFinanceiro[$i] = $custosGeraisOrd[$i]['Impostos'] + $custosGeraisOrd[$i]['Amortizações'];
                $totalImpostos += round($custosGeraisOrd[$i]['Impostos']);
                $totalAmortizacoes += round($custosGeraisOrd[$i]['Amortizações']);
                // Somatórios do grupo de Custos FSE
                foreach ($this->fse as $key) {
                    $totalFSEMensal[$i] += $custosGeraisOrd[$i][$key];
                }
            }

            foreach ($totalFSE as $key => $value) {
                for ($i = 1; $i < 13; $i++) {
                    $totalFSE[$key] += $custosGeraisOrd[$i][$key];
                }
            }
            // dump($totalFSE);
            $somaTotalCustosFinanceiros = $totalImpostos + $totalAmortizacoes;

            $totalEquipamento = 0;
            $totalEquipamento += round($totalEquipamentoInterno + $totalEquipamentoExterno);

            $totalTransporte = 0;
            $totalTransporte += round($totalTransporteInterno + $totalTransporteExterno);

            $somaTotalFSE = 0;
            foreach ($totalFSEMensal as $key => $value) {
                $somaTotalFSE += $totalFSEMensal[$key];
            }

            # Mão de obra
            $maoObraNacional = $sql::getSQL_MaoDeObra("N");
            // $maoObraNacionalOrd = MaoDeObra::selectRaw('month(data) as mes, data, nome_col as artigo, sum(h_normais * ano_2016 + h_extras * ano_2016) as valor')
            //                            ->leftjoin('colaboradores', 'num_mec', '=', 'n_mec')
            //                            ->where('cind', '=', '?')
            //                            ->where('ano_2016', '>', '?')
            //                            ->where('nacional', '=', '?')
            //                            ->whereYear('data', '?')
            //                            ->groupBy('mes')
            //                            ->setBindings([ $cisRelatorioMensal[$cAnalitico], 0, 'N', $ano])
            //                            ->get();
            //
            // foreach ($maoObraNacionalOrd as $key => $value) {
            //     $maoObraNacional[$value->mes] = $value->valor;
            // }
            //
            // dump($maoObraNacional);

            $maoObraExpatriada = $sql::getSQL_MaoDeObra("E");

            # Adicionar a mao de obra ao array dos custos
            for ($i=1; $i < 13; $i++) {
                $custosGeraisOrd[$i]["Salários Expatriados"] += $maoObraExpatriada[$i];
                $custosGeraisOrd[$i]["Salários Nacionais"] += $maoObraNacional[$i];
            }

            foreach ($this->pessoal as $key) {
                $totalPessoal[$key] = 0;
            }

            for ($i=1; $i < 13; $i++) {
                $totalPessoalMensal[$i] = 0;
                # Somatórios do grupo de Mão de Obra
                foreach ($this->pessoal as $key) {
                    $totalPessoalMensal[$i] += $custosGeraisOrd[$i][$key];
                }
                foreach ($totalPessoal as $key => $value) {
                    $totalPessoal[$key] += $custosGeraisOrd[$i][$key];
                }
            }

            $somaTotalPessoal = 0;
            foreach ($totalPessoal as $key => $value) {
                $somaTotalPessoal += $value;
            }

            # Cálculo dos totais de custos operacionais
            $totalCustosOperacionais = array();
            $totalOperacional = 0;
            for ($i=1; $i < 13; $i++) {
                $totalCustosOperacionais[$i] = $custosGeraisOrd[$i]['Materiais Diversos'] +
                                               $custosGeraisOrd[$i]['Matérias-Primas'] +
                                               $totalEquipamentoMensal[$i] +
                                               $totalTransporteMensal[$i] +
                                               $totalFSEMensal[$i] +
                                               $totalPessoalMensal[$i] +
                                               $totalCustosFinanceiro[$i];
                $totalOperacional += $totalCustosOperacionais[$i];
            }

            for ($i=1; $i < 13; $i++) {
            }

            # Cálculo de resultados operacionais
            $resultadosOperacionais = [];
            $totalResultadosOperacionais = 0;
            for ($i=1; $i < 13; $i++) {
                $resultadosOperacionais[$i] = $producoes[$i] - $totalCustosOperacionais[$i];
                $totalResultadosOperacionais += $resultadosOperacionais[$i];
            }

            $margemOperacionalSeca = array();
            $totalMargemOperacionalSeca = 0;
            for ($i=1; $i < 13; $i++) {
                if ($producoes[$i] == 0) {
                    $margemOperacionalSeca[$i] = 0;
                } else {
                    $margemOperacionalSeca[$i] = $resultadosOperacionais[$i] / $producoes[$i] * 100;
                    $totalMargemOperacionalSeca = $totalResultadosOperacionais / $totalProducoes * 100;
                }
            }

            # Resultados Mediante a Facturação
            $resultadoMedianteFacturacao = array();
            $totalResultadoMedianteFacturacao = 0;
            for ($i=1; $i < 13; $i++) {
                $resultadoMedianteFacturacao[$i] = $totalVendas[$i] - $totalCustosOperacionais[$i];
                $totalResultadoMedianteFacturacao += $resultadoMedianteFacturacao[$i];
            }

            $margemMedianteFacturacao = array();
            for ($i=1; $i < 13; $i++) {
                if ($totalVendas[$i] != 0) {
                    $margemMedianteFacturacao[$i] = $resultadoMedianteFacturacao[$i] / $totalVendas[$i] * 100;
                } else {
                    $margemMedianteFacturacao[$i] = 0;
                }
            }

            $totalMargemMedianteFacturacao = $totalResultadoMedianteFacturacao / $somatorioTotalVendas * 100;

            # Calcular o stock mensal

            $valorStockInicial = $sql::getStock();


            for ($i=1; $i < 13; $i++) {
                if ($i == 1) {
                    $vendasInternasAcumuladas[$i] = $vendasInternasOrd[$i];
                    $vendasExternasAcumuladas[$i] = $vendasExternasOrd[$i];
                    $producaoAcumulada[$i] = $producoes[$i];
                } else {
                    $vendasInternasAcumuladas[$i] = $vendasInternasOrd[$i] + $vendasInternasAcumuladas[$i-1];
                    $vendasExternasAcumuladas[$i] = $vendasExternasOrd[$i] + $vendasExternasAcumuladas[$i-1];
                    $producaoAcumulada[$i] = $producoes[$i] + $producaoAcumulada[$i-1];
                }
            }

            // Stocks no final de cada mês de
            for ($i=1; $i < 13; $i++) {
                $stockMensal[$i] = $valorStockInicial +
                                   $producaoAcumulada[$i] -
                                   $vendasExternasAcumuladas[$i] -
                                   $vendasInternasAcumuladas[$i];
            }

            # Média de stock mensal
            $mediaStockAnual = 0;

            for ($i=1; $i < 13; $i++) {
                $mediaStockAnual += $stockMensal[$i];
            }

            $mediaStockAnual = $mediaStockAnual / count($stockMensal);

            # Entradas Externas para Stock
            $entradasExternas = array();
            $totalEntradasExternas = 0;

            $entradasExternas = $sql::getSQLEntradas();

            for ($i=1; $i < 13; $i++) {
                $totalEntradasExternas += $entradasExternas[$i];
            }

            # Margem Operacional com Stock
            $margemComStock = array();

            for ($i=1; $i < 13; $i++) {
                $margemComStock[$i] = ($resultadosOperacionais[$i] + $stockMensal[$i] - ($entradasExternas[$i] * ($entradasExternas[$i] / $producoes[$i]))) / $producoes[$i] * 100;
            }

            $margemComStockGlobal = ($totalResultadosOperacionais + $mediaStockAnual - ($totalEntradasExternas * ($totalEntradasExternas / $totalProducoes))) / $totalProducoes * 100;

            $custosOperacionais = array();

            for ($i=1; $i < 13; $i++) {
                $custosOperacionais[$i] = $totalCustosOperacionais[$i] / $totalOperacional * 100;
            }

            # Calculo de percentagens
            $percMateriaisDiversos = $totalMateriaisDiversos / $totalOperacional * 100;
            $percMateriasPrimas = $totalMateriasPrimas / $totalOperacional * 100;
            //$percMateriasExternas = $ / $totalOperacional * 100;
            $percTotalEquipamento = $totalEquipamento / $totalOperacional * 100;
            $percEquipamentoInterno = $totalEquipamentoInterno / $totalOperacional * 100;
            $percEquipamentoExterno = $totalEquipamentoExterno / $totalOperacional * 100;
            $percTotalTransporte = $totalTransporte / $totalOperacional * 100;
            $percTransporteInterno = $totalTransporteInterno / $totalOperacional * 100;
            $percTransporteExterno = $totalTransporteExterno / $totalOperacional * 100;
            $percFSE = $somaTotalFSE / $totalOperacional * 100;
            $percSubcontratos = $totalFSE['Subcontratos/SubEmpreitadas'] / $totalOperacional * 100;
            $percElectricidade = $totalFSE['Material Eléctrico'] / $totalOperacional * 100;
            $percAgua = $totalFSE['Água'] / $totalOperacional * 100;
            $percComustiveis = $totalFSE['Combustíveis'] / $totalOperacional * 100;
            $percSeguros = $totalFSE['Seguros'] / $totalOperacional * 100;
            $percFerramentas = $totalFSE['Ferram. e Utens. Desg. Rápido'] / $totalOperacional * 100;
            $percConservacao = $totalFSE['Conservação e Reparação'] / $totalOperacional * 100;
            $percLivros = $totalFSE['Livros e Documentação Técnica'] / $totalOperacional * 100;
            $percEscritorio = $totalFSE['Material de Escritório'] / $totalOperacional * 100;
            $percInformatica = $totalFSE['Material Informático'] / $totalOperacional * 100;
            $percComunicacao = $totalFSE['Comunicação'] / $totalOperacional * 100;
            $percRendas = $totalFSE['Rendas e Alugueres'] / $totalOperacional * 100;
            $percProteccao = $totalFSE['Material de Protecção e Segurança'] / $totalOperacional * 100;
            $percVigilancia = $totalFSE['Vigilância e Segurança'] / $totalOperacional * 100;
            $percRepresentacao = $totalFSE['Despesas Representação'] / $totalOperacional * 100;
            $percEstadas = $totalFSE['Deslocações e Estadas'] / $totalOperacional * 100;
            $percEspecial = $totalFSE['Trabalhos Especializados'] / $totalOperacional * 100;
            $percOferta = $totalFSE['Artigos para Oferta'] / $totalOperacional * 100;
            $percContencioso = $totalFSE['Contecioso e Notariado'] / $totalOperacional * 100;
            $percPub = $totalFSE['Publicidade e Propaganda'] / $totalOperacional * 100;
            $percOutros = $totalFSE['Outros Custos Vários'] / $totalOperacional * 100;
            $percTotalPessoal = $somaTotalPessoal / $totalOperacional * 100;
            $percSalariosNacionais = $totalPessoal['Salários Nacionais'] / $totalOperacional * 100;
            $percSalariosExpatriados = $totalPessoal['Salários Expatriados'] / $totalOperacional * 100;
            $percAlojamento = $totalPessoal['Alojamento'] / $totalOperacional * 100;
            $percLimpeza = $totalPessoal['Limpeza. Higiene e Conforto'] / $totalOperacional * 100;
            $percAlimentacao = $totalPessoal['Alimentação'] / $totalOperacional * 100;
            $percSaude = $totalPessoal['Despesas de Saúde'] / $totalOperacional * 100;
            $percOutrosPessoal = $totalPessoal['Horas Extras e Prémios'] / $totalOperacional * 100;
            $percCustosFinanceiros = $somaTotalCustosFinanceiros / $totalOperacional * 100;
            $percImpostos = $totalImpostos / $totalOperacional * 100;
            $percAmortizacoes = $totalAmortizacoes / $totalOperacional * 100;
            $percTotalOperacional = $totalOperacional / $totalOperacional * 100;





            $vars['ano'] = $ano;
            $vars['page'] = 'relatorio';
            $vars['title'] = 'Demonstração de Resultados por Naturezas';
            $vars['meses'] = $meses;
            $vars['agregados_nome'] = $agregados_nome;
            $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
            $vars['codigoCI'] = $cisRelatorioMensal[$cAnalitico];
            $vars['nomeCI'] = strtoupper($cAnalitico);
            $vars['vendasExternas'] = $vendasExternasOrd;
            $vars['vendasInternas'] = $vendasInternasOrd;
            $vars['producoes'] = $producoes;
            $vars['custosGeraisOrd'] = $custosGeraisOrd;
            $vars['maoObraNacional'] = $maoObraNacional;
            $vars['maoObraExpatriada'] = $maoObraExpatriada;
            $vars['totalVendas'] = $totalVendas;
            $vars['totalProducoes'] = $totalProducoes;
            $vars['proveitosOperacionais'] = $proveitosOperacionais;
            $vars['totalProveitosOperacionais'] = $totalProveitosOperacionais;
            $vars['totalVendasExternas'] = $totalVendasExternas;
            $vars['totalVendasInternas'] = $totalVendasInternas;
            $vars['somatorioTotalVendas'] = $somatorioTotalVendas;
            $vars['totalMateriaisDiversos'] = $totalMateriaisDiversos;
            $vars['totalMateriasPrimas'] = $totalMateriasPrimas;
            $vars['totalEquipamentoInterno'] = $totalEquipamentoInterno;
            $vars['totalEquipamentoExterno'] = $totalEquipamentoExterno;
            $vars['totalEquipamento'] = $totalEquipamento;
            $vars['totalEquipamentoMensal'] = $totalEquipamentoMensal;
            $vars['totalTransporteInterno'] = $totalTransporteInterno;
            $vars['totalTransporteExterno'] = $totalTransporteExterno;
            $vars['totalTransporte'] = $totalTransporte;
            $vars['totalTransporteMensal'] = $totalTransporteMensal;
            $vars['totalFSEMensal'] = $totalFSEMensal;
            $vars['totalFSE'] = $totalFSE;
            $vars['somaTotalFSE'] = $somaTotalFSE;
            $vars['totalPessoalMensal'] = $totalPessoalMensal;
            $vars['totalPessoal'] = $totalPessoal;
            $vars['somaTotalPessoal'] = $somaTotalPessoal;
            $vars['totalImpostos'] = $totalImpostos;
            $vars['totalAmortizacoes'] = $totalAmortizacoes;
            $vars['totalCustosFinanceiro'] = $totalCustosFinanceiro;
            $vars['somaTotalCustosFinanceiros'] = $somaTotalCustosFinanceiros;
            $vars['totalCustosOperacionais'] = $totalCustosOperacionais;
            $vars['totalOperacional'] = $totalOperacional;
            $vars['resultadosOperacionais'] = $resultadosOperacionais;
            $vars['totalResultadosOperacionais'] = $totalResultadosOperacionais;
            $vars['margemOperacionalSeca'] = $margemOperacionalSeca;
            $vars['totalMargemOperacionalSeca'] = $totalMargemOperacionalSeca;
            $vars['resultadoMedianteFacturacao'] = $resultadoMedianteFacturacao;
            $vars['totalResultadoMedianteFacturacao'] = $totalResultadoMedianteFacturacao;
            $vars['margemMedianteFacturacao'] = $margemMedianteFacturacao;
            $vars['totalMargemMedianteFacturacao'] = $totalMargemMedianteFacturacao;
            $vars['stockMensal'] = $stockMensal;
            $vars['mediaStockAnual'] = $mediaStockAnual;
            $vars['entradasExternas'] = $entradasExternas;
            $vars['totalEntradasExternas'] = $totalEntradasExternas;
            $vars['margemComStock'] = $margemComStock;
            $vars['margemComStockGlobal'] = $margemComStockGlobal;
            $vars['custosOperacionais'] = $custosOperacionais;

            #percentagens totais
            $vars['percVendasExternas'] = $percVendasExternas;
            $vars['percVendasInternas'] = $percVendasInternas;
            $vars['percTotalVendas'] = $percTotalVendas;
            $vars['percTotalProducoes'] = $percTotalProducoes;
            $vars['percTotalProveitosOperacionais'] = $percTotalProveitosOperacionais;
            $vars['percMateriasPrimas'] = $percMateriasPrimas;
            $vars['percMateriaisDiversos'] = $percMateriaisDiversos;
            $vars['percMateriasExternas'] = $percMateriasExternas;
            $vars['percTotalEquipamento'] = $percTotalEquipamento;
            $vars['percEquipamentoExterno'] = $percEquipamentoExterno;
            $vars['percEquipamentoInterno'] = $percEquipamentoInterno;
            $vars['percTotalTransporte'] = $percTotalTransporte;
            $vars['percTransporteExterno'] = $percTransporteExterno;
            $vars['percTransporteInterno'] = $percTransporteInterno;
            $vars['percFSE'] = $percFSE;
            $vars['percSubcontratos'] = $percSubcontratos;
            $vars['percElectricidade'] = $percElectricidade;
            $vars['percAgua'] = $percAgua;
            $vars['percComustiveis'] = $percComustiveis;
            $vars['percSeguros'] = $percSeguros;
            $vars['percFerramentas'] = $percFerramentas;
            $vars['percConservacao'] = $percConservacao;
            $vars['percLivros'] = $percLivros;
            $vars['percEscritorio'] = $percEscritorio;
            $vars['percInformatica'] = $percInformatica;
            $vars['percComunicacao'] = $percComunicacao;
            $vars['percRendas'] = $percRendas;
            $vars['percProteccao'] = $percProteccao;
            $vars['percVigilancia'] = $percVigilancia;
            $vars['percRepresentacao'] = $percRepresentacao;
            $vars['percEstadas'] = $percEstadas;
            $vars['percEspecial'] = $percEspecial;
            $vars['percOferta'] = $percOferta;
            $vars['percContencioso'] = $percContencioso;
            $vars['percPub'] = $percPub;
            $vars['percOutros'] = $percOutros;
            $vars['percTotalPessoal'] = $percTotalPessoal;
            $vars['percSalariosNacionais'] = $percSalariosNacionais;
            $vars['percSalariosExpatriados'] = $percSalariosExpatriados;
            $vars['percAlojamento'] = $percAlojamento;
            $vars['percLimpeza'] = $percLimpeza;
            $vars['percAlimentacao'] = $percAlimentacao;
            $vars['percSaude'] = $percSaude;
            $vars['percOutrosPessoal'] = $percOutrosPessoal;
            $vars['percCustosFinanceiros'] = $percCustosFinanceiros;
            $vars['percImpostos'] = $percImpostos;
            $vars['percAmortizacoes'] = $percAmortizacoes;
            $vars['percTotalOperacional'] = $percTotalOperacional;

            return $this->view->render($response, 'relatorios/dresultados/' . $vars['page'] .'.twig', $vars);
        } else {
            $vars['ano'] = $ano;
            $vars['page'] = 'relatorio';
            $vars['title'] = 'Demonstração de Resultados por Naturezas';
            $vars['meses'] = $meses;
            $vars['agregados_nome'] = $agregados_nome;
            $vars['cisRelatorioMensal'] = $cisRelatorioMensal;
            $vars['codigoCI'] = $cisRelatorioMensal[$cAnalitico];
            $vars['nomeCI'] = strtoupper($cAnalitico);
            $vars['vendasExternas'] = 0;
            $vars['vendasInternas'] = 0;
            $vars['producoes'] = 0;
            $vars['custosGeraisOrd'] = 0;
            $vars['maoObraNacional'] = 0;
            $vars['maoObraExpatriada'] = 0;
            $vars['totalVendas'] = 0;
            $vars['totalProducoes'] = 0;
            $vars['proveitosOperacionais'] = 0;
            $vars['totalProveitosOperacionais'] = 0;
            $vars['totalVendasExternas'] = 0;
            $vars['totalVendasInternas'] = 0;
            $vars['somatorioTotalVendas'] = 0;
            $vars['percVendasExternas'] = 0;
            $vars['percVendasInternas'] = 0;
            $vars['percTotalVendas'] = 0;
            $vars['totalMateriaisDiversos'] = 0;
            $vars['totalMateriasPrimas'] = 0;
            $vars['totalEquipamentoInterno'] = 0;
            $vars['totalEquipamentoExterno'] = 0;
            $vars['totalEquipamento'] = 0;
            $vars['totalEquipamentoMensal'] = 0;
            $vars['totalTransporteInterno'] = 0;
            $vars['totalTransporteExterno'] = 0;
            $vars['totalTransporte'] = 0;
            $vars['totalTransporteMensal'] = 0;
            $vars['totalFSEMensal'] = 0;
            $vars['totalFSE'] = 0;
            $vars['somaTotalFSE'] = 0;
            $vars['totalPessoalMensal'] = 0;
            $vars['totalPessoal'] = 0;
            $vars['somaTotalPessoal'] = 0;
            $vars['totalImpostos'] = 0;
            $vars['totalAmortizacoes'] = 0;
            $vars['totalCustosFinanceiro'] = 0;
            $vars['somaTotalCustosFinanceiros'] = 0;
            $vars['totalCustosOperacionais'] = 0;
            $vars['totalOperacional'] = 0;
            $vars['resultadosOperacionais'] = 0;
            $vars['totalResultadosOperacionais'] = 0;
            $vars['margemOperacionalSeca'] = 0;
            $vars['totalMargemOperacionalSeca'] = 0;
            $vars['resultadoMedianteFacturacao'] = 0;
            $vars['totalResultadoMedianteFacturacao'] = 0;
            $vars['margemMedianteFacturacao'] = 0;
            $vars['totalMargemMedianteFacturacao'] = 0;
            $vars['stockMensal'] = 0;
            $vars['mediaStockAnual'] = 0;
            $vars['entradasExternas'] = 0;
            $vars['totalEntradasExternas'] = 0;
            $vars['margemComStock'] = 0;
            $vars['margemComStockGlobal'] = 0;
            $vars['percTotalProducoes'] = 0;
            $vars['percTotalProveitosOperacionais'] = 0;
            $vars['custosOperacionais'] = 0;


            return $this->view->render($response, 'relatorios/dresultados/' . $vars['page'] .'.twig', $vars);
        }
    }

    /*
     *  Retorna todas as vendas efectuadas no ano em questão
    */
    public function facturacao($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        $vendasInternas = Query::getSQLVendas('GTO', 'SPA');
        $vendasExternas = Query::getSQLVendas('VD', 'GR');
        // $j= 0;
        foreach ($vendasInternas as $key => $value) {
            $total += $value->total;
            $j++;
        }
        // dump($vendasInternas);
      //   # Eloquent ORM é demaseado lento
      //   $vendasInternas = VendasArimba::selectRaw('*,
      //                                      round(peso / baridade,2) as m3,
      //                                      round(valor_in_ton * baridade,1) as preco_m3,
      //                                      round(valor_in_ton * peso,2) as total
      //                                      ')
      //                           ->leftjoin('agregados', 'nome_agre', '=', 'nome_agr')
      //                           ->leftjoin('baridades', 'agregado_id', '=', 'agr_id')
      //                           ->leftjoin('obras', 'id_obra', '=', 'obra')
      //                           ->leftjoin('valorun_interno_ton', 'agr_bar_id', '=', 'agr_id')
      //                           ->leftjoin('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agr_id')
      //                           ->whereIn('tipo_doc', ['GR', 'VD', 'SPA', 'GTO'])
      //                           ->whereIn('nome_agr', $lista_agregados_array)
      //                           ->whereYear('data', $ano)
      //                           ->get();
      //
      // # Eloquent ORM é demaseado lento
      // $vendasExternas = VendasArimba::selectRaw('*,
      //                                    round(peso / baridade,2) as m3,
      //                                    round(valor_ex_ton * baridade,1) as preco_m3,
      //                                    round(valor_ex_ton * peso,2) as total
      //                                    ')
      //                         ->leftjoin('agregados', 'nome_agre', '=', 'nome_agr')
      //                         ->leftjoin('baridades', 'agregado_id', '=', 'agr_id')
      //                         ->leftjoin('obras', 'id_obra', '=', 'obra')
      //                         ->leftjoin('valorun_interno_ton', 'agr_bar_id', '=', 'agr_id')
      //                         ->leftjoin('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agr_id')
      //                         ->whereIn('tipo_doc', ['GR', 'VD', 'SPA', 'GTO'])
      //                         ->whereIn('nome_agr', $lista_agregados_array)
      //                         ->whereYear('data', $ano)
      //                         ->get();
      //   foreach ($vendasInternas as $key => $value) {
      //       $total += $value->total;
      //   }
      //   dump($total);
        $vars['page'] = 'facturacao';
        $vars['title'] = 'Vendas';
        $vars['vendasInternas'] = $vendasInternas;
        $vars['vendasExternas'] = $vendasExternas;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna o valor da produção efectuadas no ano em questão
    */
    public function producao($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        // require 'src/Auxiliares/helpers.php';

        $producoes = Query::getSQLProducoes('PRO', 'ENT');

        # Eloquent ORM é demaseado lento
        // $vendas = VendasArimba::selectRaw('*,
        //                                    round(peso / baridade,2) as m3,
        //                                    round(valor_in_ton * baridade,1) as preco_m3,
        //                                    round(valor_ex_ton * baridade,1) as preco_vd,
        //                                    round(valor_in_ton * peso,2) as total_m3,
        //                                    round(valor_ex_ton * peso,2) as total_v_m3
        //                                    ')
        //                         ->leftjoin('agregados', 'nome_agre', '=', 'nome_agr')
        //                         ->leftjoin('baridades', 'agregado_id', '=', 'agr_id')
        //                         ->leftjoin('obras', 'id_obra', '=', 'obra')
        //                         ->leftjoin('valorun_interno_ton', 'agr_bar_id', '=', 'agr_id')
        //                         ->leftjoin('valorun_externo_ton', 'agr_bar_ton_id', '=', 'agr_id')
        //                         ->whereIn('tipo_doc', ['GR', 'VD', 'SPA', 'GTO'])
        //                         ->whereIn('nome_agr', $lista_agregados_array)
        //                         ->whereYear('data', $ano)
        //                         ->get();

        $vars['page'] = 'producao';
        $vars['title'] = 'DR - Produção';
        $vars['producoes'] = $producoes;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas as comparas de materiais diversos efectuadas no ano em questão
    */
    public function matDiversos($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->where('familia', '=', 'Materiais Diversos')
                                ->whereYear('data', $ano)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Materiais Diversos';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas as compras de matérias primas efectuadas no ano em questão
    */
    public function matPrimas($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->where('familia', '=', 'Matérias-Primas')
                                ->whereYear('data', $ano)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Materias Primas';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas os custos com equipamentos efectuados no ano em questão
    */
    public function equipamentos($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $fields = ['Equipamento', 'Equipamento Externo'];
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->whereIn('familia', $fields)
                                ->whereYear('data', $ano)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Equipamento';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas os custos com transportes efectuados no ano em questão
    */
    public function transportes($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $fields = ['Transportes', 'Transportes Externos'];
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->whereIn('familia', $fields)
                                ->whereYear('data', $ano)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Transportes';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas os custos FSE efectuados no ano em questão
    */
    public function custosFse($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->whereIn('familia', $this->fse)
                                ->whereYear('data', $ano)
                                // ->whereMonth('data', 5)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - FSE';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas os custos com pessoal efectuados no ano em questão
    */
    public function custosPessoal($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $custosDiversos = Compras::selectRaw('data, artigo, valor')
                                   ->where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                   ->whereIn('familia', $this->pessoal)
                                   ->whereYear('data', $ano)->get()
                                   ;

        $listanac = ["N", "E"];
        $custosSalarios = MaoDeObra::selectRaw('month(data) as mes, data, nome_col as artigo, sum(h_normais * ano_2016 + h_extras * ano_2016) as valor')
                                   ->leftjoin('colaboradores', 'num_mec', '=', 'n_mec')
                                   ->where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                   ->where('ano_2016', '>', 0)
                                   ->whereIn('nacional', $listanac)
                                   ->whereYear('data', $ano)->groupBy('mes')->get();

        // for ($i=1; $i < 13; $i++) {
        //     $salarios[$i] = 0;
        // }
        // for ($i=1; $i < 13; $i++) {
        //     foreach ($custosDiversos as $key => $value) {
        //         if (date('m', strtotime($value->data)) == $i) {
        //             $salarios[$i] += $value->valor;
        //         }
        //     }
        // }

        // $resultado = $custosDiversos->union($custosSalarios)->get();

        $sql = new Query();
        $custosGerais = $sql::getSQL_custos();

        for ($i=0; $i < count($custosSalarios); $i++) {
            foreach ($custosSalarios as $key => $value) {
                $ponto[$i] = array('data'=>$custosSalarios[$i]->data, 'artigo'=>$custosSalarios[$i]->artigo, 'valor'=>$custosSalarios[$i]->valor);
            }
        }

        for ($i=0; $i < count($custosDiversos); $i++) {
            foreach ($custosDiversos as $key => $value) {
                $ponto2[$i] = array('data'=>$custosDiversos[$i]->data, 'artigo'=>$custosDiversos[$i]->artigo, 'valor'=>$custosDiversos[$i]->valor);
            }
        }

        $final = array_merge($ponto, $ponto2);

        // dump($final);

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Custos Pessoal';
        $vars['listaCustos'] = $final;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }

    /*
     *  Retorna todas os custos financeiros efectuados no ano em questão
    */
    public function financeiro($request, $response)
    {
        require 'src/Auxiliares/globals.php';
        require 'src/Auxiliares/helpers.php';

        # Eloquent ORM
        $fields = ['Impostos', 'Amortizações'];
        $custosDiversos = Compras::where('cind', '=', $cisRelatorioMensal[$cAnalitico])
                                ->whereIn('familia', $fields)
                                ->whereYear('data', $ano)
                                ->get();

        $vars['page'] = 'custosDR';
        $vars['title'] = 'DR - Financeiro';
        $vars['listaCustos'] = $custosDiversos;
        return $this->view->render($response, '/relatorios/dresultados/mapas/' . $vars['page'] .'.twig', $vars);
    }
}
