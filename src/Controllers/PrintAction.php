<?php

namespace Src\Controllers;
use \mikehaertl\wkhtmlto\Pdf;
use Exception;

/**
 *
 */
final class PrintAction
{

    public function imprimir ($request, $response)
    {

        $folha = $_POST['printit'];
        $variaveis = explode(',', $folha);

        $nomeFicheiro = $variaveis[0];

        $printName = substr($nomeFicheiro, 5);

        if (isset($variaveis[2])) {

            $_SESSION['mesNumero'] = $variaveis[2];
            $_SESSION['mes'] = $variaveis[1];

        } else {

            $mesNumero = 0;
            $mes = '';

        }

        ob_start();
        if ($nomeFicheiro == 'printPpiam') {
            require ('C:/xampp/htdocs/omatapalo/resources/views/pages/mapas/ppiam/print/'.$nomeFicheiro.'.php');
        } else {
            require ('C:/xampp/htdocs/omatapalo/resources/views/pages/mapas/dpgmi/print/'.$nomeFicheiro.'.php');
        }
        $content = ob_get_clean();

        // You can pass a filename, a HTML string, an URL or an options array to the constructor
        $pdf = new Pdf($content);

        // On some systems you may have to set the path to the wkhtmltopdf executable
        $pdf->binary = 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf';

        $pdf -> setOptions(['orientation' => 'Landscape',
                            // 'javascript-delay' => 500,
                            // 'window-status' => 'myrandomstring ',
                            // 'no-animation',
                            // 'enable-javascript',
                            // 'debug-javascript',
                            // 'no-stop-slow-scripts',
                        ]);

        if (!$pdf->send($printName.'.pdf')) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        $pdf->send($printName.'.pdf');

    }

    public function imprimir_preco ($request, $response)
    {

        $folha = $_POST['printit'];
        // dump($folha);
        $variaveis = explode(',', $folha);

        $nomeFicheiro = $variaveis[0];
        $_SESSION['moeda'] = $variaveis[1];
        $_SESSION['tipo'] = $variaveis[2];
        $_SESSION['unidade'] = $variaveis[3];

        setlocale(LC_CTYPE, "pt_PT.UTF-8");

        ob_start();
            require ('C:/xampp/htdocs/omatapalo/resources/views/pages/tabelas/precos/print/'.$nomeFicheiro.'.php');
        $content = ob_get_clean();

        // You can pass a filename, a HTML string, an URL or an options array to the constructor
        $pdf = new Pdf(array(
                            'encoding' => 'UTF-8',
                            //'header-left' => $title,
                            'commandOptions' => array(
                                'locale' => 'pt_PT.utf-8',
                                'procEnv' => array(
                                    'LANG' => 'pt_PT.utf-8',
                                ),
                            ),
                        ));

        $pdf->addPage($content);

        // On some systems you may have to set the path to the wkhtmltopdf executable
        $pdf->binary = 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf';

        $pdf -> setOptions(['orientation' => 'Portrait', 'encoding' => 'UTF-8']);

        if (!$pdf->send($nomeFicheiro.'.pdf')) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        $pdf->send($nomeFicheiro.'.pdf');


    }

    public function imprimir_ndimGeral ($request, $response)
    {

        $folha = $_POST['printit'];

        $variaveis = explode(',', $folha);

        $nomeFicheiro = $variaveis[0];
        $_SESSION['mes_ndim'] = $variaveis[1];
        $_SESSION['cindus_ndim'] = $variaveis[2];
        $_SESSION['numCindus'] = $variaveis[3];
        $_SESSION['numObra'] = $variaveis[4];
        $_SESSION['obra_ndim'] = $variaveis[5];
        $_SESSION['numDiasMes'] = $variaveis[6];
        $_SESSION['ano_ndim'] = $variaveis[7];
        $_SESSION['britas_ndim'] = $variaveis[8];


        ob_start();
            require ('C:/xampp/htdocs/omatapalo/resources/views/pages/mapas/ndim/print/'.$nomeFicheiro.'.php');
        $content = ob_get_clean();

        // You can pass a filename, a HTML string, an URL or an options array to the constructor
        $pdf = new Pdf($content);

        // On some systems you may have to set the path to the wkhtmltopdf executable
        $pdf->binary = 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf';

        $pdf -> setOptions(['orientation' => 'Portrait', 'margin-left' => 20, 'margin-top' => 5]);

        if (!$pdf->send($nomeFicheiro.'.pdf')) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        $pdf->send($nomeFicheiro.'.pdf');


    }

    public function imprimir_ndimDetalhado ($request, $response)
    {

        $folha = $_POST['printit'];

        $variaveis = explode(',', $folha);

        $nomeFicheiro = $variaveis[0];

        $_SESSION['mes_ndim'] = $variaveis[1];
        $_SESSION['cindus_ndim'] = $variaveis[2];
        $_SESSION['numCindus'] = $variaveis[3];
        $_SESSION['numObra'] = $variaveis[4];
        $_SESSION['obra_ndim'] = $variaveis[5];
        $_SESSION['numDiasMes'] = $variaveis[6];
        $_SESSION['ano_ndim'] = $variaveis[7];
        $_SESSION['britas_ndim'] = $variaveis[8];


        ob_start();
            require ('C:/xampp/htdocs/omatapalo/resources/views/pages/mapas/ndim/print/'.$nomeFicheiro.'.php');
        $content = ob_get_clean();

        // You can pass a filename, a HTML string, an URL or an options array to the constructor
        $pdf = new Pdf($content);

        // On some systems you may have to set the path to the wkhtmltopdf executable
        $pdf->binary = 'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf';

        $pdf -> setOptions(['orientation' => 'Portrait',
                            'margin-left' => 20,
                            'margin-top' => 5, 'encoding' =>
                            'UTF-8', 'footer-right' =>
                            '[page] de [toPage]']);

        if (!$pdf->send($nomeFicheiro.'.pdf')) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        $pdf->send($nomeFicheiro.'.pdf');


    }

}
