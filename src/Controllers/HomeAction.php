<?php

namespace Src\Controllers;

/**
 *
 */
final class HomeAction extends Action
{
    public function ano($request, $response)
    {
        $_SESSION['ano'] = $_POST['ano'];
        header('Content-Type: application/json');
        echo json_encode(array('status' => true));
    }

    public function cind($request, $response)
    {
        $_SESSION['ci'] = $_POST['ci'];
        header('Content-Type: application/json');
        echo json_encode(array('status' => true));
    }

    public function index($request, $response)
    {
        if (!isset($_SESSION['ano'])) {
            $_SESSION['ano'] = 2017;
        }

        $vars['page'] = 'home';
        $vars['title'] = 'Home';

        return $this->view->render($response, 'home.twig', $vars);
    }

    public function tabelas($request, $response)
    {
        include 'src/Auxiliares/globals.php';

        //
        // Direccionar para uma pagina de selecção
        //

        $vars['page'] = 'tabelas';
        $vars['title'] = 'Tabelas';

        return $this->view->render($response, 'tabelas.twig', $vars);
    }

    public function mapas($request, $response)
    {
        $vars['page'] = 'mapas';
        $vars['title'] = 'Mapas de Informação';

        return $this->view->render($response, 'mapas.twig', $vars);
    }

    public function relatorios($request, $response)
    {
        $vars['page'] = 'relatorios';
        $vars['title'] = 'Relatórios Gerais';

        return $this->view->render($response, 'relatorios.twig', $vars);
    }
}
