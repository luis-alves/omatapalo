<?php
include 'src/Auxiliares/globals.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// dump($mesNumero);
$mesNumero = $_SESSION['mesNumero'];
$mes = $_SESSION['mes'];

$query = "SELECT `nome_col`,
                 `data_nasc`,
                 MONTH(`data`) AS data,
                 `nacional`,
                 `sexo`
          FROM `colaboradores`
          LEFT JOIN `folha_ponto`
          ON `num_mec` = `n_mec`
          WHERE MONTH(`data`) = '$mesNumero'
          GROUP BY `nome_col`
          ";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->prepare($query);
$stmt->execute();


$vars['row'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

foreach ($vars['row'] as $key => $value) {
    $value->idade = floor((time() - strtotime($value->data_nasc)) / 31556926);
}

// separar os tipos de dados que se quer obtar
$agrupamento = array('array_n_m_21', 'array_n_m_25', 'array_n_m_30',
                     'array_n_m_40', 'array_n_m_45', 'array_n_m_50',
                     'array_n_m_55', 'array_n_m_60',
                     'array_n_f_21', 'array_n_f_25', 'array_n_f_30',
                     'array_n_f_40', 'array_n_f_45', 'array_n_f_50',
                     'array_n_f_55', 'array_n_f_60',
                     'array_e_m_21', 'array_e_m_25', 'array_e_m_30',
                     'array_e_m_40', 'array_e_m_45', 'array_e_m_50',
                     'array_e_m_55', 'array_e_m_60',
                     'array_e_f_21', 'array_e_f_25', 'array_e_f_30',
                     'array_e_f_40', 'array_e_f_45', 'array_e_f_50',
                     'array_e_f_55', 'array_e_f_60',
                 );

foreach ($agrupamento as $key) {
    ${$key} = 0;
}

// Separar os colaboradores por idade/Sexo/nacionalidade
foreach ($vars['row'] as $key => $value) {
    //dump($vars);
    switch (true) {
        case $value->nacional === 'N' && $value->sexo === 'M' && $value->idade <= 21:
                $array_n_m_21 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 25:
                $array_n_m_25 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 30:
                $array_n_m_30 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 40:
                $array_n_m_40 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 45:
                $array_n_m_45 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 50:
                $array_n_m_50 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade <= 55:
                $array_n_m_55 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'M' and $value->idade > 55:
                $array_n_m_60 += 1;
                break;
        // Nacional feminino
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 21:
                $array_n_f_21 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 25:
                $array_n_f_25 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 30:
                $array_n_f_30 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 40:
                $array_n_f_40 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 45:
                $array_n_f_45 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 50:
                $array_n_f_50 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade <= 55:
                $array_n_f_55 += 1;
                break;
        case $value->nacional === 'N' && $value->sexo === 'F' and $value->idade > 55:
                $array_n_f_60 += 1;
                break;
        // Expatriados
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 21:
                $array_e_m_21 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 25:
                $array_e_m_25 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 30:
                $array_e_m_30 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 40:
                $array_e_m_40 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 45:
                $array_e_m_45 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 50:
                $array_e_m_50 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade <= 55:
                $array_e_m_55 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'M' and $value->idade > 55:
                $array_e_m_60 += 1;
                break;
        // Expatriado  feminino
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 21:
                $array_e_f_21 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 25:
                $array_e_f_25 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 30:
                $array_e_f_30 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 40:
                $array_e_f_40 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 45:
                $array_e_f_45 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 50:
                $array_e_f_50 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade <= 55:
                $array_e_f_55 += 1;
                break;
        case $value->nacional === 'E' && $value->sexo === 'F' and $value->idade > 55:
                $array_e_f_60 += 1;
                break;
        default:
            break;
    }
}

$faixas_etarias = array('f_21' => array('nm' => $array_n_m_21, 'em' => $array_e_m_21,
                                        'nf' => $array_n_f_21, 'ef' => $array_e_f_21),
                        'f_25' => array('nm' => $array_n_m_25, 'em' => $array_e_m_25,
                                        'nf' => $array_n_f_25, 'ef' => $array_e_f_25),
                        'f_30' => array('nm' => $array_n_m_30, 'em' => $array_e_m_30,
                                        'nf' => $array_n_f_30, 'ef' => $array_e_f_30),
                        'f_40' => array('nm' => $array_n_m_40, 'em' => $array_e_m_40,
                                        'nf' => $array_n_f_40, 'ef' => $array_e_f_40),
                        'f_45' => array('nm' => $array_n_m_45, 'em' => $array_e_m_45,
                                        'nf' => $array_n_f_45, 'ef' => $array_e_f_45),
                        'f_50' => array('nm' => $array_n_m_50, 'em' => $array_e_m_50,
                                        'nf' => $array_n_f_50, 'ef' => $array_e_f_50),
                        'f_55' => array('nm' => $array_n_m_55, 'em' => $array_e_m_55,
                                        'nf' => $array_n_f_55, 'ef' => $array_e_f_55),
                        'f_60' => array('nm' => $array_n_m_60, 'em' => $array_e_m_60,
                                        'nf' => $array_n_f_60, 'ef' => $array_e_f_60),
                    );

$faixas = ['18 - 21 anos','22 - 25 anos',
          '26 - 30 anos',  '31 - 40 anos',
          '41 - 45 anos', '46 - 50 anos',
          '51 - 55 anos',  '56 - 60 anos'];

// totais parciais de cada faixa
$ate_21 = $faixas_etarias['f_21']['nm'] + $faixas_etarias['f_21']['em'] +
         $faixas_etarias['f_21']['nf'] + $faixas_etarias['f_21']['ef'];
$ate_25 = $faixas_etarias['f_25']['nm'] + $faixas_etarias['f_25']['em'] +
         $faixas_etarias['f_25']['nf'] + $faixas_etarias['f_25']['ef'];
$ate_30 = $faixas_etarias['f_30']['nm'] + $faixas_etarias['f_30']['em'] +
         $faixas_etarias['f_30']['nf'] + $faixas_etarias['f_30']['ef'];
$ate_40 = $faixas_etarias['f_40']['nm'] + $faixas_etarias['f_40']['em'] +
         $faixas_etarias['f_40']['nf'] + $faixas_etarias['f_40']['ef'];
$ate_45 = $faixas_etarias['f_45']['nm'] + $faixas_etarias['f_45']['em'] +
         $faixas_etarias['f_45']['nf'] + $faixas_etarias['f_45']['ef'];
$ate_50 = $faixas_etarias['f_50']['nm'] + $faixas_etarias['f_50']['em'] +
         $faixas_etarias['f_50']['nf'] + $faixas_etarias['f_50']['ef'];
$ate_55 = $faixas_etarias['f_55']['nm'] + $faixas_etarias['f_55']['em'] +
         $faixas_etarias['f_55']['nf'] + $faixas_etarias['f_55']['ef'];
$ate_60 = $faixas_etarias['f_60']['nm'] + $faixas_etarias['f_60']['em'] +
         $faixas_etarias['f_60']['nf'] + $faixas_etarias['f_60']['ef'];

$parcial_array = [$ate_21, $ate_25, $ate_30, $ate_40, $ate_45, $ate_50, $ate_55, $ate_60];


// Totais do rodapé da tabela
$total_N_M = $faixas_etarias['f_21']['nm'] + $faixas_etarias['f_25']['nm'] + $faixas_etarias['f_30']['nm'] +
            $faixas_etarias['f_40']['nm'] + $faixas_etarias['f_45']['nm'] +
            $faixas_etarias['f_50']['nm'] + $faixas_etarias['f_55']['nm'] + $faixas_etarias['f_60']['nm'];

$total_N_F = $faixas_etarias['f_21']['nf'] + $faixas_etarias['f_25']['nf'] + $faixas_etarias['f_30']['nf'] +
             $faixas_etarias['f_40']['nf'] + $faixas_etarias['f_45']['nf'] +
             $faixas_etarias['f_50']['nf'] + $faixas_etarias['f_55']['nf'] + $faixas_etarias['f_60']['nf'];

$total_E_M = $faixas_etarias['f_21']['em'] + $faixas_etarias['f_25']['em'] + $faixas_etarias['f_30']['em'] +
            $faixas_etarias['f_40']['em'] + $faixas_etarias['f_45']['em'] +
            $faixas_etarias['f_50']['em'] + $faixas_etarias['f_55']['em'] + $faixas_etarias['f_60']['em'];

$total_E_F = $faixas_etarias['f_21']['ef'] + $faixas_etarias['f_25']['ef'] + $faixas_etarias['f_30']['ef'] +
            $faixas_etarias['f_40']['ef'] + $faixas_etarias['f_45']['ef'] +
            $faixas_etarias['f_50']['ef'] + $faixas_etarias['f_55']['ef'] + $faixas_etarias['f_60']['ef'];

$total_array = $ate_21 + $ate_25 + $ate_30 + $ate_40 + $ate_45 + $ate_50 + $ate_55 + $ate_60;


unset($_SESSION['mesNumero']);
unset($_SESSION['mes'])


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
                <!-- <img alt="Logo" src="/Applications/XAMPP/xamppfiles/htdocs/omatapalo_v3/public/img/oma.svg"> -->

             </div>
             <div>
                 <h3><font style="color:#003686">omatapalo_v3 - Engenharia e Construção, S.A.</font></h3>
             </div>
             <div class="colorir">
                 <h1>MAPA RESUMO DE GRUPOS ETÁRIOS</h1>
             </div>
          </div>
          <div class="al_esquerda">
              <p><b>Ano:</b> 2016</p>
              <p><b>Empresa:</b> <?php echo $nome_empresa; ?> </p>
          </div>
          <div class="al_direita">
              <p><b>Tipo de Recurso Mineral:</b> Agregado britado granitico</p>
              <?php if (isset($mes)) {
     print_r("<p><b>Mês: </b>". $mes ."</p>");
 }

              ?>
          </div>
        <div>
            <table class="items" id="myTdable">
                <thead>
                    <tr>
                        <th colspan="12">DISTRIBUIÇÃO DE MÃO DE OBRA POR GRUPOS ETÁRIOS</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Faixa Etária</th>
                        <th colspan="4">Homens</th>
                        <th colspan="4">Mulheres</th>
                        <th colspan="2">Totais</th>
                    </tr>
                    <tr>
                        <th>Nacionais</th>
                        <th>%</th>
                        <th>Expatriados</th>
                        <th>%</th>
                        <th>Nacionais</th>
                        <th>%</th>
                        <th>Expatriadas</th>
                        <th>%</th>
                        <th>Mão-de-Obra</th>
                        <th>%</th>
                    </tr>
                </thead>

                <tfoot>
                    <th>Totais</th>
                    <th><?php echo $total_N_M ?></th>
                    <th></th>
                    <th><?php echo $total_E_M ?></th>
                    <th></th>
                    <th><?php echo $total_N_F ?></th>
                    <th></th>
                    <th><?php echo $total_E_F ?></th>
                    <th></th>
                    <th><?php echo $total_array ?></th>
                    <th></th>
                </tfoot>


                <tbody>
                    <?php
                        $i = 0;
                        foreach ($faixas_etarias as $key => $value) {
                            echo "<tr>";
                                //dump($faixas_etarias);
                                // Faixa etária
                                echo "<td>". $faixas[$i] . "</td>";
                                // Homens nacional
                                echo "<td>". $value['nm'] . "</td>";
                                // Média
                                if ($total_N_M === 0) {
                                    echo "<td>0</td>";
                                } else {
                                    echo "<td>". number_format(($value['nm'] / $total_N_M) * 100, 1, ",", ".") . "</td>";
                                }
                                // Homens Expatriado
                                echo "<td>". $value['em'] . "</td>";
                                // Média
                                if ($total_E_M === 0) {
                                    echo "<td>0</td>";
                                } else {
                                    echo "<td>". number_format(($value['em'] / $total_E_M) * 100, 1, ",", ".") . "</td>";
                                }
                                // Mulheres nacional
                                echo "<td>". $value['nf'] . "</td>";
                                // Média
                                if ($total_N_F === 0) {
                                    echo "<td>0</td>";
                                } else {
                                    echo "<td>". number_format(($value['nf'] / $total_N_F) * 100, 1, ",", ".") . "</td>";
                                }
                                // Mulheres Expatriado
                                echo "<td>". $value['ef'] . "</td>";
                                // Média
                                if ($total_E_F === 0) {
                                    echo "<td>0</td>";
                                } else {
                                    echo "<td>". number_format(($value['ef'] / $total_E_F) * 100, 1, ",", ".") . "</td>";
                                }
                                // Total parcial
                                echo "<td>". $parcial_array[$i] . "</td>";
                                // % do total parcial
                                if ($total_array === 0) {
                                    echo "<td>0</td>";
                                } else {
                                    echo "<td>". number_format(($parcial_array[$i] / $total_array) * 100, 1, ",", ".") . "</td>";
                                }
                            echo "</tr>";
                            $i++;
                        }
                     ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="al_esquerda_footer" style="padding-top:10px;">
            <?php
                header('Content-Type: text/html; charset=iso-8859-1');
                setlocale(LC_ALL, 'pt_PT', 'pt_PT.iso-8859-1', 'pt_PT.utf-8', 'portuguese');
                date_default_timezone_set('Europe/Lisbon');
                echo "Lubango, " . strftime('%d de %B de %Y', strtotime(date('Y-m-d')));
            ?>

        </div>
        <div class="al_direita_footer">
            <p> Responsável pelo preeenchimento</p>
            <p> _______________________________</p>
        </div>
<br><br>
</body>

</html>
