<?php

namespace Src\Controllers;

use Src\Auxiliares\Query as Query;
use Illuminate\Database\Capsule\Manager as DB;

/**
 *
 */
class DResultadosAction extends Action
{
    public function dresultados($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        $sql = new Query();

        $vendasExternas = $sql::getSQLVendas("GR", "VD");

        $vendasInternas = $sql::getSQLVendas("GTO", "SPA");

        # Total vendido (interno + externo)
        for ($i=1; $i < 13; $i++) {
            $totalVendas[$i] = $vendasExternas[$i] + $vendasInternas[$i];
        }

        $somatorioTotalVendas = 0;
        for ($i=1; $i < 13; $i++) {
            $somatorioTotalVendas += $totalVendas[$i];
        }

        $totalVendasExternas = 0;
        $totalVendasInternas = 0;
        for ($i=1; $i < 13; $i++) {
            $totalVendasExternas += $vendasExternas[$i];
            $totalVendasInternas += $vendasInternas[$i];
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
        $producoes = $sql::getSQLProducoes("PRO", "ENT");

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

        // Familias pertencentes a FSE
        $fse = array( "Combustíveis" => 0,
                      "Conservação e Reparação" => 0,
                      "Contecioso e Notariado" => 0,
                      "Ferram. e Utens. Desg. Rápido" => 0,
                      "Material de Protecção e Segurança" => 0,
                      "Material Eléctrico" => 0,
                      "Outros Custos Vários" => 0,
                      "Publicidade e Propaganda" => 0,
                      "Subcontratos/SubEmpreitadas" => 0,
                      "Comunicação" => 0,
                      "Deslocações e Estadas" => 0,
                      "Material de Escritório" => 0,
                      "Material Informático" => 0,
                      "Vigilância e Segurança" => 0,
                      "Rendas e Alugueres" => 0,
                      "Água" => 0,
                      "Seguros" => 0,
                      "Livros e Documentação Técnica" => 0,
                      "Despesas Representação" => 0,
                      "Trabalhos Especializados" => 0,
                      "Artigos para Oferta" => 0,
                    );

        // Familias pertencentes a Pessoal
        $pessoal = array( "Alimentação" => 0,
                          "Despesas de Saúde" => 0,
                          "Limpeza. Higiene e Conforto" => 0,
                          "Alojamento" => 0,
                          "Salários Expatriados" => 0,
                          "Salários Nacionais" => 0,
                          "Trabalhos Especializados" => 0,
                          "Horas Extras e Prémios" => 0,

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
            foreach ($fse as $key => $value) {
                $totalFSE[$key] = 0;
            }


            for ($i=1; $i < 13; $i++) {
                $totalFSEMensal[$i] = 0;
                $totalMateriaisDiversos += round($custosGeraisOrd[$i]['Materiais Diversos']);
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
                foreach ($fse as $key => $value) {
                    $totalFSEMensal[$i] += round($custosGeraisOrd[$i][$key]);
                }
                foreach ($totalFSE as $key => $value) {
                    $totalFSE[$key] += round($custosGeraisOrd[$i][$key]);
                }
            }

            $somaTotalCustosFinanceiros = $totalImpostos + $totalAmortizacoes;

            $totalEquipamento = 0;
            $totalEquipamento += round($totalEquipamentoInterno + $totalEquipamentoExterno);

            $totalTransporte = 0;
            $totalTransporte += round($totalTransporteInterno + $totalTransporteExterno);

            $somaTotalFSE = 0;
            foreach ($totalFSE as $key => $value) {
                $somaTotalFSE += round($totalFSE[$key]);
            }

            $maoObraNacional = $sql::getSQL_MaoDeObra("N");

            $maoObraExpatriada = $sql::getSQL_MaoDeObra("E");

            # Adicionar a mao de obra ao array dos custos
            for ($i=1; $i < 13; $i++) {
                $custosGeraisOrd[$i]["Salários Expatriados"] += round($maoObraExpatriada[$i]);
                $custosGeraisOrd[$i]["Salários Nacionais"] += round($maoObraNacional[$i]);
            }

            foreach ($pessoal as $key => $value) {
                $totalPessoal[$key] = 0;
            }

            for ($i=1; $i < 13; $i++) {
                $totalPessoalMensal[$i] = 0;
                # Somatórios do grupo de Mão de Obra
                foreach ($pessoal as $key => $value) {
                    $totalPessoalMensal[$i] += round($custosGeraisOrd[$i][$key]);
                }
                foreach ($totalPessoal as $key => $value) {
                    $totalPessoal[$key] += round($custosGeraisOrd[$i][$key]);
                }
            }

            $somaTotalPessoal = 0;
            foreach ($totalPessoal as $key => $value) {
                $somaTotalPessoal += round($value);
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

            $valorStockInicial = $sql::getstock();


            for ($i=1; $i < 13; $i++) {
                if ($i == 1) {
                    $vendasInternasAcumuladas[$i] = $vendasInternas[$i];
                    $vendasExternasAcumuladas[$i] = $vendasExternas[$i];
                    $producaoAcumulada[$i] = $producoes[$i];
                } else {
                    $vendasInternasAcumuladas[$i] = $vendasInternas[$i] + $vendasInternasAcumuladas[$i-1];
                    $vendasExternasAcumuladas[$i] = $vendasExternas[$i] + $vendasExternasAcumuladas[$i-1];
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
            $vars['vendasExternas'] = $vendasExternas;
            $vars['vendasInternas'] = $vendasInternas;
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
}
