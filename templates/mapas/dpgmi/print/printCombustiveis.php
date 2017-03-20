<?php  ?>

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
                    <th>Gasóleo (litros)</th>
                    <th>Óleo 10W (litros)</th>
                    <th>Óleo 15W40 (litros)</th>
                    <th>Óleo HD80W90 (litros)</th>
                    <th>Óleo  Pneuma 100 (litros)</th>
                    <th>Óleo Azolla ZS (litros)</th>

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
            </tfoot>

            <tbody>
                    <tr>
                        <td>Janeiro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>


                    </tr>
                    <tr>
                        <td>Fevereiro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Março</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Abril</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Maio</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Junho</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Julho</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Agosto</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Setembro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Outubro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Novembro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>
                    <tr>
                        <td>Dezembro</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                        <td>N/d</td>
                    </tr>

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
