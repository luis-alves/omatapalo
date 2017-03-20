<?php

include 'c:/xampp/htdocs/omatapalo_v3/src/Auxiliares/globals.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$ano = 2016;
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
          WHERE `nome_agr` IN ($lista_agregados) AND YEAR(`data`) IN ('$ano')
          GROUP BY `nome_agr_corr`, MONTH(`data`)
          ORDER by `nome_agr_corr`
          ";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                    if ($value[$i]['nome'] === $agr ) {
                        $producao[$key][$i]['nome'] = $real[1];
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

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                        if ($value[$i]['nome'] === $agr ) {
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

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                        if ($value[$i]['nome'] === $agr ) {
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
            $totalFacturaInterna[$i] += $value[$i]['m3'] * $value[$i]['pu'] * $cambio[$ano][$i-1];
        }
    }

    for ($i=1; $i <= 12; $i++) {
        foreach ($vExterna as $key => $value) {
            $totalFacturaExterna[$i] += $value[$i]['m3'] * $value[$i]['pu'] * $cambio[$ano][$i-1];
        }
    }

    for ($i=1; $i <= 12 ; $i++) {
        $totalFacturacao[$i] = $totalFacturaInterna[$i] + $totalFacturaExterna[$i];
    }



    // Preço médio interno

    //$pmi = array();
    for ($i=1; $i <= 12 ; $i++) {
        if ($totalVendasInternas[$i] == 0)
            $pmi[$i] = 0;
        else
            $pmi[$i] = $totalFacturaInterna[$i] / $totalVendasInternas[$i];
    }

    // Preço médio externo

    $pme = $totalVendasInternas;
    for ($i=1; $i <= 12 ; $i++) {
        if ($totalVendasExternas[$i] === 0)
            $pme[$i] = 0;
        else
            $pme[$i] = $totalFacturaExterna[$i] / $totalVendasExternas[$i];
    }

    // Média de preços unitários mensais

    $mediaPrecos = $totalVendasInternas;

    for ($i = 1; $i <= 12 ; $i++) {
        if ($totalVendasInternas[$i] + $totalVendasExternas[$i] == 0) {
            $mediaPrecos[$i] = 0;
        }else {
            $mediaPrecos[$i] = ($totalVendasInternas[$i] * $pmi[$i] + $totalVendasExternas[$i] * $pme[$i]) /
                               ($totalVendasInternas[$i] + $totalVendasExternas[$i]);
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

    $mediaPrecosGrafico = implode(',',$mediaPrecosGrafico);
    // dump($mediaPrecosGrafico);

} else {
    $totalVendasInternas = 0;
    $totalVendasExternas = 0;
    $totalProducao = 0;
    $mediaPrecos = 0;
    $pmi = 0;
    $pme = 0;
    $mediaPrecosGrafico = 0;
}
 ?>

 <!DOCTYPE html>

 <html lang="pt">

     <head>
         <!-- The above 3 meta tags *must* come first in the head; any other head
              content must come *after* these tags -->
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta http-equiv="X-UA-Compatible" content="IE=edge">
         <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
         <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW, NOARCHIVE">
         <!-- <link rel='favicon icon' href='/home/luisalves/webapps/marizealves/omatapalo_v3/public/img/favicon.ico' type='image/x-icon'/ > -->



         <title> omatapalo_v3, SA </title>

         <!-- jquery para colocar a cor vermelha no navbar -->
         <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/
              jquery.min.js"></script> -->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/jquery-1.11.3.min.js"></script>
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/scripts.js"></script>

         <!--  BOOTSTRAP -->
         <link href="c:/xampp/htdocs/omatapalo_v3/public/css/bootstrap.min.css" rel="stylesheet"/>
         <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/
              bootstrap/3.3.5/css/bootstrap-theme.min.css"> -->
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/css/bootstrap-theme.min.css">
         <link href="c:/xampp/htdocs/omatapalo_v3/public/css/style_dpgmi.css" rel="stylesheet" type="text/css"/>
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/bootstrap.min.js"></script>


         <!-- jquery para choosen ver: https://harvesthq.github.io/chosen/
              e dataTables ver: https//www.datatables.net
              também para datetimepicker ver:https://eonasdan.github.io/
              bootstrap-datetimepicker/ -->
         <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/
              chosen/1.4.2/chosen.css"> -->
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/css/chosen.css">

         <!-- <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/
              base/jquery-ui.css"> -->
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/css/jquery-ui.css">
         <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/
         bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" />-->
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/css/bootstrap-datetimepicker.min.css" />
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/js/DataTables-1.10.12/media/css/dataTables.bootstrap.min.css" />

         <!-- <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/
              3.1.2/css/fixedHeader.dataTables.min.css" /> -->
         <link rel="stylesheet" href="c:/xampp/htdocs/omatapalo_v3/public/css/fixedHeader.dataTables.min.css" />
         <!--
         <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/
         jquery.dataTables.min.css" />
         -->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/Chart2.5.js"></script>
         <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/
              moment.min.js"></script>-->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/moment.min.js"></script>
         <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/
             bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js">
         </script>-->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/bootstrap-datetimepicker.min.js"></script>
         <!-- <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>-->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/jquery-ui.js"></script>
         <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/
              chosen.jquery.js"></script>-->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/chosen.jquery.js"></script>
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/DataTables-1.10.12/media/js/jquery.dataTables.min.js">
         </script>
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/DataTables-1.10.12/media/js/dataTables.bootstrap.min.js">
         </script>
         <!-- <script src="https://cdn.datatables.net/fixedheader/3.1.2/js/
              dataTables.fixedHeader.min.js"></script>-->
         <script src="c:/xampp/htdocs/omatapalo_v3/public/js/FixedHeader-3.1.2/js/dataTables.fixedHeader.min.js">
         </script>
         <!-- Bootstrap -->
         <!-- <link rel="stylesheet" href="public/css/bootstrap.min.css" > -->
         <!-- <script src="public/css/bootsrtrap.min.js"></script> -->
     </head>
     <body>
         <div class="centrar">
             <div>
                 <!-- <img alt="Logo" src="/home/luisalves/webapps/marizealves/omatapalo_v3/public/img/oma.svg"
                      style="width:100px" > -->
                <img alt="Logo" src="c:/xampp/htdocs/omatapalo_v3/public/img/omapng.png" width="100px">

             </div>
             <div>
                 <h3><font style="color:#003686">omatapalo_v3 - Engenharia e Construção, S.A.</font></h3>
             </div>
             <div class="colorir">
                 <h1>MAPA RESUMO DE PPIAM</h1>
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
                        <th class="text-center" style="vertical-align:middle;">Designação</th>
                        <th class="text-center" colspan="1">Janeiro</th>
                        <th class="text-center" colspan="1">Fevereiro</th>
                        <th class="text-center" colspan="1">Março</th>
                        <th class="text-center" colspan="1">Abril</th>
                        <th class="text-center" colspan="1">Maio</th>
                        <th class="text-center" colspan="1">Junho</th>
                        <th class="text-center" colspan="1">Julho</th>
                        <th class="text-center" colspan="1">Agosto</th>
                        <th class="text-center" colspan="1">Setembro</th>
                        <th class="text-center" colspan="1">Outubro</th>
                        <th class="text-center" colspan="1">Novembro</th>
                        <th class="text-center" colspan="1">Dezembro</th>
                    </tr>
                </thead>

                <tfoot>
                </tfoot>

                <tbody>
                        <tr>
                            <td class="text-center">Preço Médio Interno</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($pmi[$i],0,",",".") ?></td>
                            <?php endfor; ?>

                        </tr>
                        <tr>
                            <td class="text-center">Preço Médio Externo</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($pme[$i],0,",",".") ?></td>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <td class="text-center">Produção</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($totalProducao[$i],0,",",".") ?></td>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <td class="text-center">Qt. Forn. Internamente</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($totalVendasInternas[$i],0,",",".") ?></td>
                            <?php endfor; ?>
                        </tr>

                        <tr>
                            <td class="text-center">Qt. Forn. Externamente</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($totalVendasExternas[$i],0,",",".") ?></td>
                            <?php endfor; ?>
                        </tr>

                        <tr>
                            <td class="text-center">Valor Médio Global de Venda</td>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <td class="text-center"><?= number_format($mediaPrecos[$i],0,",",".") ?></td>
                            <?php endfor; ?>
                        </tr>
                </tbody>
            </table>
        </div>
        <div class="" style="width:100$;text-align:center;">


        <div>
            <h2 ><span class="label label-primary">Preço Médio de Venda Ponderado - Anual</span></h2>
        </div>
        <div style="width:340mm;height:72mm;display:inline-block;">
            <canvas id="myChart" height="72mm" width="340mm"></canvas>
        </div>
        </div>
    </body>
    <script type="text/javascript">
        var data = {
            labels : ["Janeiro","Fevereiro","Março",
                      "Abril","Maio","Junho",
                      "Julho","Agosto","Setembro",
                      "Outubro","Novembro","Dezembro"],
            datasets : [
               {
                label : "Média de preços",
                backgroundColor : "rgba(252,233,79,0.3)",
                borderColor : "rgba(82,75,25,1)",
                pointBorderColor : "rgba(166,152,51,1)",
                pointBackgroundColor : "#fff",
                pointHoverBackgroundColor: "rgba(252,233,79,1)",
                data : [<?= $mediaPrecosGrafico ?>]
                },
            ]
        }

        //Get context with jQuery - using jQuery's .get() method.
        var ctx = $("#myChart").get(0).getContext("2d");
        // ctx.canvas.width = 800;
        // ctx.canvas.height = 200;
        new Chart(ctx,{
            type:'line',
            data:data,
            options: {
                legend: {
                    position:'bottom'
                },
            },
        });


    </script>
</html>
