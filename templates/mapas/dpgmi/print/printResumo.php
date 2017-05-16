<?php
include 'src/Auxiliares/globals.php';

$cindus = 'producoes_'.$cAnalitico;
$placeholders = str_repeat('?, ', count($lista_agregados_array) - 1) . '?';

$query = "SELECT `nome_agre` AS `nome`,
                 MONTH(`data_in`) AS `mes`,
                 `qt` AS `m3`,
                 ROUND(`valor_in_ton` * `baridade`,2) AS `pu`,
                 `qt` * ROUND(`valor_in_ton` * `baridade`) AS `total`
          FROM `$cindus`
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

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$stmt = $conn->prepare($query);
$params = array_merge($lista_agregados_array, [$ano]);
$stmt->execute($params);

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
        $totalExtraido[$i] += (round($value[$i]['m3'] / 1.88) * $royalty * $custoExtraccao) / $cambio[$_SESSION['ano']][$i-1];
    }
}

// Rodapé - Total Volume Extraido

$rodapeVolExtraido = 0;

for ($i=1; $i <= 12; $i++) {
    $rodapeVolExtraido += ($totalExtraido[$i]);
}


$vars['page'] = 'mapas/dpgmi/resumo';
$vars['title'] = 'MAPA RESUMO DE CUSTOS';
$vars['print'] = 'printResumo';

 ?>

 <!DOCTYPE html>

 <html lang="pt">

     <head>
         <?= '<link href= '. $rootDir.'\omatapalo\public\css\style_dpgmi.css'." rel='stylesheet'/>" ?>

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
                <?= "<img alt='Logo' width='100px' src=".$rootDir.'\omatapalo\public\img\omapng.png'. ">" ?>

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
                        <th rowspan="2">Mês</th>
                        <th colspan="2">Investimentos</th>
                        <th colspan="6">Operacionais</th>
                    </tr>
                    <tr>
                        <th>Bens <br>(USD)</th>
                        <th>Equipamentos <br>(USD)</th>
                        <th>Taxas e Impostos <br>(USD)</th>
                        <th>Pessoal <br>(USD)</th>
                        <th>Transportes <br>(USD)</th>
                        <th>Serviços de Terceiros <br>(USD)</th>
                        <th>Combustivel e Lubrif. <br>(USD)</th>
                        <th>Outros Custos <br>(USD)</th>

                    </tr>
                </thead>
                <tfoot>
                    <th>Totais</th>
                    <th></th>
                    <th></th>
                    <th><?= number_format($rodapeVolExtraido, 2, ",", ".") ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tfoot>

                <tbody>
                    <?php include 'src/Auxiliares/globals.php'; ?>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <tr>
                            <td class="dpgm_estreito"><?= $lista_meses[$i-1] ?></td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito"><?= number_format($totalExtraido[$i], 2, ",", ".") ?></td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito">-</td>
                            <td class="dpgm_estreito">-</td>
                        </tr>
                    <?php endfor ?>


                    </tr>
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
