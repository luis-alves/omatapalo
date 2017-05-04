<?php
include 'src/Auxiliares/globals.php';

$cindus = 'importacao_'.$cAnalitico;

$query = "SELECT `nome_agr` AS `nome`,
                  MONTH(`data`) AS `mes`,
                  (ROUND(SUM(`peso` / `baridade`))) AS `m3`,
                  ROUND((`valor_in_ton` * `baridade`),2) AS `pu`,
                  ROUND((SUM(`peso` / `baridade`)) * ROUND(`valor_in_ton` * `baridade`)) AS `total`
          FROM `$cindus`
          LEFT JOIN `centros_analiticos`
          ON `ca_id` = `obra`
          JOIN `agregados`
          ON `nome_agr` = `nome_agre`
          JOIN `baridades`
          ON `agr_id` = `agregado_id`
          JOIN `valorun_interno_ton`
          ON `agr_bar_id` = `agregado_id`
          WHERE  `tipo_doc` IN ('PRO', 'ENT') AND `nome_agr` IN ($lista_agregados) AND YEAR(`data`) IN ('$ano')
          GROUP BY `nome_agr_corr`, MONTH(`data`)
          ORDER by `nome_agr_corr`
          ";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$stmt = $conn->prepare($query);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);


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
          FROM `$cindus`
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


$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$stmt = $conn->prepare($query);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

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
          FROM `$cindus`
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


    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

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
        $totalFacturaInterna[$i] += $value[$i]['m3'] * $value[$i]['pu'];
    }
}

for ($i=1; $i <= 12; $i++) {
    foreach ($vExterna as $key => $value) {
        $totalFacturaExterna[$i] += $value[$i]['m3'] * $value[$i]['pu'];
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

for ($i=1; $i <= 12; $i++) {
    if ($rodapeVolComer == 0) {
        $rodapePU = 0;
    } else {
        $rodapePU = $rodapeFactura / $rodapeVolComer;
    }
}


$vars['page'] = 'mapas/dpgmi/producao';
$vars['title'] = 'Relatório Resumo Produção';
 ?>

 <!DOCTYPE html>

 <html lang="pt">

     <head>
         <!-- <link href="/Applications/XAMPP/xamppfiles/htdocs/omatapalo/public/css/style_dpgmi_server.css" rel="stylesheet"/> -->
         <link href="C:/xampp/htdocs/omatapalo/public/css/style_dpgmi.css" rel="stylesheet"/>

         <title>Mapa Impostos</title>
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta charset="UTF-8">
         <!-- <link rel='favicon2 icon' href='/Applications/XAMPP/xamppfiles/htdocs/omatapalo/public/img/favicon2.ico' type='image/x-icon'/ > -->
     </head>
     <body>
         <div class="centrar">
             <div>
                 <!-- <img alt="Logo" src="/home/luisalves/webapps/marizealves/omatapalo/public/img/oma.svg"
                      style="width:100px" > -->
                <!-- <img alt="Logo" src="/Applications/XAMPP/xamppfiles/htdocs/omatapalo/public/img/oma.svg"> -->
                <img style="width:100px;heigth:100px;" alt="Logo" src="C:/xampp/htdocs/omatapalo/public/img/omapng.png">

             </div>
             <div>
                 <h3><font style="color:#003686">omatapalo - Engenharia e Construção, S.A.</font></h3>
             </div>
             <div class="colorir">
                 <h1><?php echo $vars['title']; ?></h1>
             </div>
          </div>
          <div class="al_esquerda">
              <p><b>Ano:</b> <?= $ano ?></p>
              <p><b>Empresa:</b> <?php echo $nome_empresa; ?> </p>
          </div>
          <div class="al_direita">
              <p><b>Tipo de Recurso Mineral:</b> Agregado britado granitico</p>
          </div>
        <div>
            <table class="items" id="myTdable">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Localização</th>
                        <th class="text-center sum">Volume Extraido (m3)</th>
                        <th class="text-center sum">Volume Transformado (m3)</th>
                        <th class="text-center sum">Volume Comercializado (m3)</th>
                        <th class="text-center">Preço Médio (Akz/m3)</th>
                        <th class="text-center sum">Receita Bruta (Akz)</th>
                    </tr>
                </thead>
                <tfoot>
                    <th colspan="2">Totais</th>
                    <th><?= number_format($rodapeVolExtraido, 0, ",", ".") ?></th>
                    <th><?= number_format($rodapeVolTrans, 0, ",", ".") ?></th>
                    <th><?= number_format($rodapeVolComer, 0, ",", ".") ?></th>
                    <th><?= number_format($rodapePU, 0, ",", ".") ?></th>
                    <th><?= number_format($rodapeFactura, 0, ",", ".") ?></th>
                </tfoot>

                <tbody>
                    <?php

                    $lista_meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril',
                                   'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
                                   'Outubro', 'Novembro', 'Dezembro'];

                    for ($i = 1; $i <= 12; $i++) {
                        echo "<tr>";
                        echo "<td>". $lista_meses[$i-1] ."</td>";
                        echo "<td>"."P. Arimba"."</td>";
                        echo "<td>".number_format($totalProducao[$i] / 1.88, 0, ",", ".")."</td>";
                        echo "<td>".number_format($totalProducao[$i], 0, ",", ".")."</td>";
                        echo "<td>".number_format($totalVendas[$i], 0, ",", ".")."</td>";
                        echo "<td>".number_format($mediaPrecos[$i], 0, ",", ".")."</td>";
                        echo "<td>".number_format($totalFacturacao[$i], 0, ",", ".")."</td>";
                        echo "</tr>";
                    }

                     ?>
                </tbody>
            </table>
        </div>
        <div class="al_esquerda_footer" style="padding-top:10px;">
            <?php
                header('Content-Type: text/html; charset=iso-8859-1');
                setlocale(LC_ALL, 'pt_PT', 'pt_PT.iso-8859-1', 'pt_PT.utf-8', 'portuguese');
                date_default_timezone_set('Europe/Lisbon');
                echo "Local, " . strftime('%d de %B de %Y', strtotime(date('Y-m-d')));
            ?>

        </div>
        <div class="al_direita_footer">
            <p> Responsável pelo preeenchimento</p>
            <p> _______________________________</p>
        </div>
<br><br>
</body>

</html>
