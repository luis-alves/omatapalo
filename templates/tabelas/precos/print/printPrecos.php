<?php

    include 'config/constants.php';
    include 'resources/auxiliar/helpers.php';


    $tipoTabela = $_SESSION['moeda'];
    $destino = $_SESSION['tipo'];
    $unidade = $_SESSION['unidade'];

    $mesActual = date('n');
    $cambioDoDia = $cambio[$_SESSION['ano']][$mesActual];

    $cIndArray = $cAnalitico.'Agregados';

    $cindList = [];
    foreach (${$cIndArray} as $key => $value) {
        array_push($cindList, $key);
    }
    $cindList = implode("'".','."'", $cindList);

    $cInd = $cAnalitico;


// dump($cindList);
    $cIndCapitais = strtoupper($cInd);
    $destinoCapitais = strtoupper($destino);

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT `nome_agr_corr` AS `brita`,
                    CASE
                    WHEN '$tipoTabela' = 'usd' AND '$destino' = 'externos' AND '$unidade' = 'toneladas'
                    THEN ROUND(`valor_ex_ton`,2)
                    WHEN '$tipoTabela' = 'usd' AND '$destino' = 'externos' AND '$unidade' = 'm3'
                    THEN ROUND(`valor_ex_ton` * `baridade`,2)
                    WHEN '$tipoTabela' = 'usd' AND '$destino' = 'internos' AND '$unidade' = 'toneladas'
                    THEN ROUND(`valor_in_ton`,2)
                    WHEN '$tipoTabela' = 'usd' AND '$destino' = 'internos' AND '$unidade' = 'm3'
                    THEN ROUND(`valor_in_ton` * `baridade`,2)

                    WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'externos' AND '$unidade' = 'toneladas'
                    THEN TRIM(ROUND(`valor_ex_ton` * $cambioDoDia))+0
                    WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'externos' AND '$unidade' = 'm3'
                    THEN TRIM(ROUND(`valor_ex_ton` * `baridade` * $cambioDoDia))+0
                    WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'internos' AND '$unidade' = 'toneladas'
                    THEN TRIM(ROUND(`valor_in_ton` * $cambioDoDia))+0
                    WHEN '$tipoTabela' = 'aoa' AND '$destino' = 'internos' AND '$unidade' = 'm3'
                    THEN TRIM(ROUND(`valor_in_ton` * `baridade` * $cambioDoDia))+0
                    END AS `preco`
              FROM `agregados`
              LEFT JOIN `valorun_interno_ton`
              ON `agr_id` = `agr_bar_id`
              LEFT JOIN `valorun_externo_ton`
              ON `agr_id` = `agr_bar_ton_id`
              LEFT JOIN `baridades`
              ON `agr_id` = `agregado_id`
              WHERE `nome_agre` IN (''' .$cindList. ''')
              GROUP BY `nome_agr_corr`
              ORDER BY `nome_agr_corr`
             ";

     $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $stmt = $conn->prepare($query);
     $stmt->execute();

     if ($stmt->rowCount() > 0) {
         $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $listaPrecos = $vars['row'];

        $numeroPrecos = count($listaPrecos);

        $listaPrecosArray = array();
        foreach ($listaPrecos as $key => $value) {
                // dump($value->brita);
                $listaPrecosArray[] = array('brita' => $value->brita, 'preco' => $value->preco);
        }
    }
    // dump($listaPrecosArray);




?>

 <!DOCTYPE html>
 <html>
     <head>
         <meta charset="utf-8">
         <title></title>
     </head>
     <body>
         <br><br>
         <div class="container">
             <div class="container">
                 <img src="C:/xampp/htdocs/omatapalo/public/img/omapng.png" alt="Logo omatapalo" style="float:left;width:120px;height:120px;margin-top:0px;">
             </div>

             <div style="background-color:rgb(0,176,240);padding:5px;margin-left:140px;margin-right:140px;">
                 <h3 style="text-align:center;color:white"><strong>CENTRAL DE BRITAGEM DE <?= $cIndCapitais ?></strong><br><br>TABELA DE PREÇOS <?= $destinoCapitais ?></h3>
              </div>
             <h3><strong></strong></h3>
         </div>
         <br><br>
         <div class="container">
             <table align="center">
                 <thead>
                     <tr>
                         <th>Agregado</th>
                         <th>Preço Unitário <br>(<?= $tipoTabela?>/<?= $unidade ?>)</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php for ($i=0; $i < $numeroPrecos-1; $i++): ?>
                         <tr>
                             <td><?= $listaPrecosArray[$i]['brita'] ?></td>
                             <td><?= $listaPrecosArray[$i]['preco'] ?></td>
                         </tr>
                     <?php endfor; ?>
                 </tbody>

             </table>
             <?php if ($destino == 'externos'): ?>
                 <p style="text-align:center"><br>* Adicionar 10% imposto de consumo sobre o valor da tabela.<br>
                 Os valores apresentados poderão ser sujeitos a alterações sem aviso prévio</p>
             <?php endif; ?>
         </div>


<p style="text-align:center;margin-top:600px">
<strong>OMATAPALO ENGENHARIA E CONSTRUÇÃO, S.A.</strong><br>
Bairro do Tchioco, Zona Industrial II – Lubango – Huila – Angola <br>
NIF 5171093733 <br>
Telef. 00244 251 288 021 – Fax. 00244 261 228 020 <br>
[e-mail: geral@omatapalo.com] <br>
[www.omatapalo.com]<br>
</p>
     </body>
 </html>

 <style>

 table, th, td {
     border: 1px solid black;
     border-collapse: collapse;;
     text-align: center;
     border-spacing: 15px;
     padding: 5px 100px;
     cellspacing
 }

 table thead {
     background-color: lightgrey;

 }




 </style>
