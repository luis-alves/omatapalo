<?php


include 'src/Auxiliares/globals.php';

$numObra = $_SESSION['numObra'];
$mes = $_SESSION['mes_ndim'];
$ano_ndim = $_SESSION['ano_ndim'];
$indus = 'importacao_'.$cAnalitico;
$cindus = $_SESSION['cindus_ndim'];
$numCindus = $_SESSION['numCindus'];
$obra = $_SESSION['obra_ndim'];

$cambioMes = $cambio[$ano_ndim][$mes-1];

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $con->error);
}

        $numeroDiasMes = $_SESSION['numDiasMes'];

        $britas = $_SESSION['britas'];
        $quantBritas = count($britas);

        # Acrescentar numero de linhas ao documento para preencher toda a folha
        $numeroLinhas = 28 - $quantBritas;

        $totalBritas = 0;
        foreach ($britas as $key => $value) {
            $totalBritas += $value['total'];
        }

 ?>


 <!DOCTYPE html>
 <html>
     <head>
         <!-- <link href='http://fonts.googleapis.com/css?family=Jolly+Lodger' rel='stylesheet' type='text/css'> -->
         <meta charset="utf-8">
         <title></title>
     </head>
     <body style="font-family:arial;">
         <br><br>

         <div class="container" style="margin-top:20px">

             <img id="image" style="width:125px;float:right;block:in-line" src="C:/xampp/htdocs/omatapalo/public/img/omapng.png" alt="logo">


                 <!-- <div class="leftFloat"> -->
                 <h3 style="text-align:center">MAPA DE GUIAS TRANSPORTE</h3>
                 <h4  style="text-align:center">Centro de Britagem de <?= $cindus ?></h4>
             <!-- <p style="text-align:center;font-size:25px;">MAPA DE GUIAS TRANSPORTE</p>
             <p  style="text-align:center;font-size:20px;">Centro de Britagem de <?= $cindus ?></p> -->
                 <!-- </div> -->

             <div class=" cabecalho" style="margin-top:70px;">
                 <table table class="tablee" id="myTablee" style="width:100%">
                     <tbody>
                         <tr style="">
                             <td class="cab1" style="width:15%;background-color:lightgrey;padding:5px;text-align:center;">Mês do Fornecimento:</td>
                             <td style="width:0.4%;"></td>
                             <td class="cab1"  style="width:10%;background-color:lightgrey;text-align:center;"><?= $mes ?> de <?= $ano_ndim ?></td>
                             <td class=""  style="width:55.8%;" colspan="3"></td>
                             <td class="cab1"  style="width:4%;background-color:lightgrey;text-align:center;">N.º:</td>
                             <td style="width:0.4%;"></td>
                             <td class="cab1" style="width:20%;background-color:lightgrey;text-align:center;"><?= $numCindus ?>.<?= $mes ?>/<?= $numObra ?></td>
                         </tr>
                     </tbody>
                 </table>
             </div>
             <div class="" style="margin-top:5px;">
                 <table table class="tablee" id="myTablee" style="width:100%;">
                     <tbody>
                         <tr>
                             <td class="cab3" style="width:15%;background-color:lightgrey;padding:5px;text-align:center;">Centro de Custo:</td>
                             <td style="width:0.4%;"></td>
                             <td class="cab2" style="width:10%;background-color:lightgrey;text-align:center;"><?= $numCindus ?></td>
                             <td style="width:0.4%;"></td>
                             <td  class="cab2" style="width:74.2%;background-color:lightgrey;padding-left:5px;"><?= $obra ?> </td>
                         </tr>
                     </tbody>
                 </table>
             </div>




             <div class="cabecalho" style="margin-top:7px;">
                 <table class="tableCorpo1" style="width:100%;border-collapse:collapse;" border="1px solid black">
                     <thead >
                         <tr style="padding-top:20px;">
                             <th class="cor1" style="width:15%;background-color:darkgrey;text-align:center;color:white">Data</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">Guia n.º</th>
                             <th class="cor1" style="width:25%;text-align:center;background-color:darkgrey;color:white">Produto</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">Qt. (m3)</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">V. Unitário (Akz)</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">Valor Total (Akz)</th>
                         </tr>
                     </thead>

                     <tbody>

                         <?php for ($i = 0; $i < $quantBritas; $i++): ?>
                         <tr>
                             <td class="cor2" style="width:15%;text-align:center;"><?= $britas[$i]['data'] ?></td>
                             <td class="cor2" style="width:15%;text-align:center;"><?= $britas[$i]['guia'] ?></td>
                             <td class="cor2" style="width:25%;text-align:center;"><?= $britas[$i]['nomeBrita'] ?></td>
                             <td class="cor2" style="width:15%;text-align:center;"><?= $britas[$i]['m3'] ?></td>
                             <td class="cor2" style="width:15%;text-align:center;"><?= $britas[$i]['pu'] ?></td>
                             <td class="cor2" style="width:15%;text-align:center;"><?= number_format($britas[$i]['total'], 2, ".", " ") ?></td>
                         </tr>
                         <?php endfor; ?>

                     </tbody>
                     <!-- valor total é diferente da folha resumo -->
                     <tfoot>
                         <tr>
                             <td style="width:70%;text-align:right;background-color:lightgrey;" colspan="4"></td>
                             <td class="cor3" style="width:15%;text-align:right;background-color:lightgrey;text-align:center;"><b>Total (Akz):</b></td>
                             <td class="cor3" style="width:15%;background-color:lightgrey;text-align:center;"><?= number_format($totalBritas, 2, ".", " ") ?></td>
                         </tr>
                     </tfoot>
                 </table>
             </div>
         </div>

     </body>
 </html>

 <style>

th, td {
    font-size: 10px;
    padding-top:5px;
    padding-bottom: 5px;
}

.esquerdo {
    margin-top: 20px;
    float:left;
    font-size: 11px;
}

.direito {
    margin-top: 20px;
    float:right;
    font-size: 11px;
}

.indent1{
    margin-left: 30px;
}

.indent2{
    margin-left: 20px;
}

.indent3{
    margin-left: 36px;

}

.versao {
    padding-top:15px;
    font-size:8px;
    clear:both;
}

/* Imprimir o cabeçalho da tabela em todas as folhas */
table {
    page-break-inside: auto !important;
}

thead {
    display: table-header-group !important;
}

tfoot {
    display: table-row-group !important;
}

tr {
    page-break-inside: avoid !important;
    page-break-after: auto !important;
}

td, th {
    page-break-inside: avoid !important;
}

 </style>
