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
            require ('/Applications/XAMPP/xamppfiles/htdocs/omatapalo/resources/views/pages/mapas/ppiam/print/'.$nomeFicheiro.'.php');
        } else {
            require ('/Applications/XAMPP/xamppfiles/htdocs/omatapalo/resources/views/pages/mapas/dpgmi/print/'.$nomeFicheiro.'.php');

        }
        $content = ob_get_clean();

        // You can pass a filename, a HTML string, an URL or an options array to the constructor
        $pdf = new Pdf($content);

        // On some systems you may have to set the path to the wkhtmltopdf executable
        $pdf->binary = '/usr/local/bin/wkhtmltopdf';

        $pdf -> setOptions(['orientation' => 'Landscape']);

        if (!$pdf->send($printName.'.pdf')) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        $pdf->send($printName.'.pdf');


    }

}


 ?>
