<?php

include 'config/constants.php';
include 'resources/auxiliar/helpers.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT `nome_col`,
                 `data_nasc`,
                 MONTH(`data`) AS data,
                 `nacional`,
                 `sexo`
          FROM `colaboradores`
          LEFT JOIN `folha_ponto`
          ON `num_mec` = `n_mec`
          GROUP BY `nome_col`
          ";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->prepare($query);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

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
}

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
                        <th rowspan="3">Mês</th>
                        <th colspan="9">Mão-de-Obra</th>
                        <th rowspan="3">Salários (Akz)</th>
                        <th rowspan="3">IRT (Akz)</th>
                        <th rowspan="3">ISS (Akz)</th>
                    </tr>
                    <tr>

                        <th colspan="3">Nacional</th>
                        <th colspan="3">Expatriado</th>
                        <th colspan="3">Total</th>

                    </tr>
                    <tr>

                        <th>F</th>
                        <th>M</th>
                        <th>Sub-total</th>
                        <th>F</th>
                        <th>M</th>
                        <th>Sub-total</th>
                        <th>F</th>
                        <th>M</th>
                        <th>Sub-total</th>


                    </tr>
                </thead>
            <!--
                <tfoot>
                    <th>Totais</th>
                    <th>0</th>
                    <th>255</th>
                    <th>255</th>
                    <th>0</th>
                    <th>9</th>
                    <th>-</th>
                    <th></th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                    <th>-</th>
                </tfoot>
            -->
                <tbody>
                    <?php include 'config/constants.php'; ?>
                    <?php for ($i = 0; $i < 12; $i++): ?>

                        <tr>
                            <td><?= $lista_meses[$i] ?></td>
                            <td><?= $nac_feminino[$i]?></td>
                            <td><?= $nac_masculino[$i]?></td>
                            <td><?= $nac_feminino[$i] + $nac_masculino[$i] ?></td>
                            <td><?= $exp_feminino[$i]?></td>
                            <td><?= $exp_masculino[$i]?></td>
                            <td><?= $exp_feminino[$i] + $exp_masculino[$i] ?></td>
                            <td><?= $nac_feminino[$i] + $exp_feminino[$i] ?></td>
                            <td><?= $nac_masculino[$i] + $nac_masculino[$i] ?></td>
                            <td><?= $nac_feminino[$i] + $exp_feminino[$i]+
                                    $nac_masculino[$i] + $nac_masculino[$i] ?></td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>

                    <?php endfor ?>

                </tbody>
            </table>
        </div>
        <div class="al_esquerda_footer" style="padding-top:10px;">
            <?php
                header( 'Content-Type: text/html; charset=iso-8859-1' );
                setlocale( LC_ALL, 'pt_PT', 'pt_PT.iso-8859-1', 'pt_PT.utf-8', 'portuguese' );
                date_default_timezone_set( 'Europe/Lisbon' );
                echo "Local, " . strftime( '%d de %B de %Y', strtotime( date( 'Y-m-d' ) ) );
            ?>

        </div>
        <div class="al_direita_footer">
            <p> Responsável pelo preeenchimento</p>
            <p> _______________________________</p>
        </div>
<br><br>
</body>

</html>
