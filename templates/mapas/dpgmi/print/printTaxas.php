<?php

include 'src/Auxiliares/globals.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
          WHERE  `tipo_doc` IN ('PRO', 'ENT') AND `nome_agr` IN ($lista_agregados) AND YEAR(`data`) IN ('2016')
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
                    if ($value[$i]['nome'] === $agr) {
                        $producao[$key][$i]['nome'] = $real[1];
                    }
                }
            }
        }
    }
}

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

$vars['title'] = 'MAPA RESUMO DE TAXAS E IMPOSTOS';
$vars['print'] = 'printTaxas';


 ?>


 <!DOCTYPE html>

 <html lang="pt">

     <head>
         <link href="/Applications/XAMPP/xamppfiles/htdocs/omatapalo_v3/public/css/style_dpgmi_server.css" rel="stylesheet"/>

         <title>Mapa Impostos</title>
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta charset="UTF-8">
         <!-- <link rel='favicon2 icon' href='/Applications/XAMPP/xamppfiles/htdocs/omatapalo_v3/public/img/favicon2.ico' type='image/x-icon'/ > -->
     </head>
     <body>
         <div class="centrar">
             <div>
                 <!-- <img alt="Logo" src="/home/luisalves/webapps/marizealves/omatapalo_v3/public/img/oma.svg"
                      style="width:100px" > -->
                <img alt="Logo" src="/Applications/XAMPP/xamppfiles/htdocs/omatapalo_v3/public/img/oma.svg">

             </div>
             <div>
                 <h3><font style="color:#003686">omatapalo_v3 - Engenharia e Construção, S.A.</font></h3>
             </div>
             <div class="colorir">
                 <h1><?php echo $vars['title']; ?></h1>
             </div>
          </div>
          <div class="al_esquerda">
              <p><b>Ano:</b> 2016</p>
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
                        <th colspan="3">Imposto Industrial</th>
                        <th colspan="3">Imposto Selo</th>
                        <th colspan="3">Royalty</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Valor (Akz)</th>
                        <th>Banco</th>
                        <th>Rep.Fiscal</th>
                        <th>Valor (Akz)</th>
                        <th>Banco</th>
                        <th>Rep.Fiscal</th>
                        <th>Valor (Akz)</th>
                        <th>Banco</th>
                        <th>Rep.Fiscal</th>
                    </tr>
                </thead>
                <tfoot>
                    <th>Totais</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th><?=  number_format($rodapeVolExtraido, 0, ",", ".") ?></th>
                    <th>-</th>
                    <th>-</th>
                </tfoot>

                <tbody>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <tr>
                            <?php include 'src/Auxiliares/globals.php'; ?>
                            <td><?= $lista_meses[$i-1] ?></td>
                            <td>Isento</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td><?= number_format($totalExtraido[$i], 0, ",", ".")?></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
         </div>
        <div class="al_esquerda">
            <?php
                header('Content-Type: text/html; charset=iso-8859-1');
                setlocale(LC_ALL, 'pt_PT', 'pt_PT.iso-8859-1', 'pt_PT.utf-8', 'portuguese');
                date_default_timezone_set('Europe/Lisbon');
                echo "Local, " . strftime('%d de %B de %Y', strtotime(date('Y-m-d')));
            ?>

        </div>
        <div class="al_direira">
            <p class="direita"> Responsável pelo preeenchimento</p>
            <p class="direita"> _______________________________</p>
        </div>
    </body>

</html>
