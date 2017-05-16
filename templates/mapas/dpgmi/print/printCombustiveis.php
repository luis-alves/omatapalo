<?php include 'src/Auxiliares/globals.php'; ?>

<!DOCTYPE html>

<html lang="pt">

    <head>
        <?= '<link href= '. $rootDir.'\omatapalo\public\css\style_dpgmi.css'." rel='stylesheet'/>" ?>
        <title>Mapa Impostos</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">
    </head>
    <body>
        <div class="centrar">
            <div>

               <?= "<img alt='Logo' width='100px' src=".$rootDir.'\omatapalo\public\img\omapng.png'. ">" ?>

            </div>
            <div>
                <h3><font style="color:#003686">Omatapalo - Engenharia e Construção, S.A.</font></h3>
            </div>
            <div class="colorir">
                <h1><?= $_SESSION['title'] ?></h1>
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
                <?php
                for ($i=1; $i < 13; $i++) {
                    echo "<tr class='dpgm_estreito'>";
                    echo "<td class='dpgm_estreito'>".$lista_meses[$i-1]."</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
                    echo "<td class='dpgm_estreito'>N/d</td>";
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
