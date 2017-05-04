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

         <div class="container">

             <img id="image" style="width:125px;float:right;block:in-line" src="C:/xampp/htdocs/omatapalo/public/img/omapng.png" alt="logo">


                 <!-- <div class="leftFloat"> -->
             <p style="text-align:center;font-size:25px;">NOTA DE DÉBITO INTERNO <br> MENSAL</p>
             <p  style="text-align:center;font-size:20px;">Centro de Britagem de <?= $cindus ?></p>
                 <!-- </div> -->

             <div class=" cabecalho" style="margin-top:50px;">
                 <table table class="tablee" id="myTablee" style="width:100%">
                     <tbody>
                         <tr style="">
                             <td class="cab1" style="width:10%;background-color:lightgrey;padding:5px;text-align:center;"><b>Data emissão:</b></td>
                             <td style="width:0.4%;"></td>
                             <td class="cab1"  style="width:10%;background-color:lightgrey;text-align:center;"><?= date('d/m/Y') ?></td>
                             <td class=""  style="width:55%;" colspan="3"></td>
                             <td class="cab1"  style="width:4%;background-color:lightgrey;text-align:center;"><b>N.º:</b></td>
                             <td style="width:0.4%;"></td>
                             <td class="cab1" style="width:20%;background-color:lightgrey;text-align:center;"><?= $numCindus ?>.<?= $mes ?>/<?= $numObra ?></td>
                         </tr>
                     </tbody>
                 </table>
             </div>
             <div class="" style="margin-top:7px;">
                 <table table class="tablee" id="myTablee" style="width:100%;border-collapse:collapse">
                     <tbody>
                         <tr>
                             <td class="cab3" style="width:10%;background-color:lightgrey;padding:5px;text-align:center;">Origem</td>
                             <td style="width:0.4%;"></td>
                             <td class="cab2" style="width:15%;background-color:lightgrey;text-align:center;"><b>N.º Centro Analítico:</b></td>
                             <td class="cab2" style="width:10%;background-color:lightgrey;text-align:center;"><?= $numCindus ?></td>
                             <td style="width:0.4%;"></td>
                             <td class="cab2" style="width:10%;background-color:lightgrey;text-align:center;"><b>Designação:</b></td>
                             <td  class="cab2" style="width:54.2%;background-color:lightgrey;padding-left:5px;">Centro de Britagem de <?= $cindus ?> </td>
                         </tr>
                     </tbody>
                 </table>
             </div>

             <div class="" style="margin-top:7px;">
                 <table table class="tablee" id="myTablee" style="width:100%;border-collapse:collapse">
                     <tbody>
                         <tr style="margin-top:10px">
                             <td class="cab3" style="width:10%;background-color:lightgrey;padding:5px;text-align:center;">Cliente</td>
                             <td style="width:0.4%;"></td>
                             <td class="cab2" style="width:15%;background-color:lightgrey;text-align:center;"><b>N.º Centro de Custo:</b></td>
                             <td class="cab2" style="width:10%;background-color:lightgrey;text-align:center;"><?= $numObra ?></td>
                             <td style="width:0.4%;"></td>
                             <td class="cab2" style="width:10%;background-color:lightgrey;text-align:center;"><b>Designação:</b></td>
                             <td  class="cab2" style="width:54.2%;background-color:lightgrey;;padding-left:5px;"><?= $obra ?></td>
                         </tr>
                     </tbody>
                 </table>
             </div>

             <div class="" style="margin-top:15px;margin-bottom:15px;">
                 <table class="tableCabecalho3" style="width:100%;border-collapse:collapse">
                     <tbody>
                         <tr style="background-color:lightblue;">
                             <td class="cab3" style="text-align:right;width:80%" ><b>Periodo Facturação:&nbsp </b></td>
                             <td class="cab3" style="text-align:left;padding-left:10px:width:20%" >1-<?= $mes ?>-<?= $ano_ndim ?> a <?= $numeroDiasMes ?>-<?= $mes ?>-<?= $ano_ndim ?> </td>
                         </tr>
                     </tbody>
                 </table>
             </div>

             <div class="cabecalho" style="margin-top:7px;">
                 <table class="tableCorpo1" style="width:100%;border-collapse:collapse;" border="1px solid black" border-collapse:collapse>
                     <thead>
                         <tr>
                             <th class="cor1" style="width:50%;background-color:darkgrey;padding-left:5px;color:white;text-align:left;padding-top:7px;">Designação</th>
                             <th class="cor1" style="width:10%;text-align:center;background-color:darkgrey;color:white">Un.</th>
                             <th class="cor1" style="width:10%;text-align:center;background-color:darkgrey;color:white">QT.</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">V. Unitário (Akz)</th>
                             <th class="cor1" style="width:15%;text-align:center;background-color:darkgrey;color:white">Valor total (Akz)</th>
                         </tr>
                     </thead>

                     <tbody>

                         <?php for ($i = 0; $i < $quantBritas; $i++): ?>
                             <tr>
                                 <td class="cor2" style="width:50%;padding-left:5px;padding-top:7px;"><?= $britas[$i]['nomeBrita'] ?></td>
                                 <td class="cor2" style="width:10%;text-align:center;">m3</td>
                                 <td class="cor2" style="width:10%;text-align:center;"><?= number_format($britas[$i]['m3'], 0, ".", " ") ?></td>
                                 <td class="cor2" style="width:15%;text-align:center;"><?= number_format($britas[$i]['pu'], 0, ".", " ") ?></td>
                                 <td class="cor2" style="width:15%;text-align:center;"><?= number_format($britas[$i]['total'], 0, ".", " ") ?></td>
                             </tr>
                         <?php endfor; ?>
                         <?php for ($i = 0; $i < $numeroLinhas; $i++): ?>
                             <tr>
                                 <td class="cor2" style="width:50%;padding-left:5px;padding-top:20px;"></td>
                                 <td class="cor2" style="width:10%;text-align:center;"></td>
                                 <td class="cor2" style="width:10%;text-align:center;"></td>
                                 <td class="cor2" style="width:15%;text-align:center;"></td>
                                 <td class="cor2" style="width:15%;text-align:center;"></td>
                             </tr>
                         <?php endfor; ?>

                     </tbody>
                     <tfoot>
                         <tr>
                             <td style="width:70%;text-align:right;background-color:lightgrey;padding-top:5px;" colspan="3"></td>
                             <td class="cor3" style="width:15%;text-align:right;background-color:lightgrey;text-align:center;"><b>Total (Akz):</b></td>
                             <td class="cor3" style="width:15%;background-color:lightgrey;text-align:center;"><?= number_format($totalBritas, 0, ".", " ") ?></td>
                         </tr>
                     </tfoot>
                 </table>
             </div>
             <div class="esquerdo">
                 <p>Elaborado por:</p>
                 <p class="indent1">Nome: ________________________________________________________</p>
                 <p class="indent2">Função: ________________________________________________________</p>
                 <p class="indent3">Rub: ________________________________________________________</p>
             </div>
             <div class="direito">
                 <p>Verificado por:</p>
                 <p class="indent1">Nome: ________________________________________________________</p>
                 <p class="indent2">Função: ________________________________________________________</p>
                 <p class="indent3">Rub: ________________________________________________________</p>
             </div>
             <div class="versao">
                 <p>OMT-MOD-NDI-V001</p>
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
 </style>
