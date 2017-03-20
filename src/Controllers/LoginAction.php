<?php

namespace Src\Controllers;

use Src\Controllers\Action;

final class LoginAction extends Action
{
    public function index($request, $response)
    {
        if (isset($_SESSION["autenticado"])) {
            return $response->withRedirect('templates');
        }
        return $this->view->render($response, 'login.twig');
    }

    public function logar($request, $response)
    {
        $data = $request->getParsedBody();

        $email = strip_tags(filter_var($data['email'], FILTER_SANITIZE_STRING));
        $senha = strip_tags(filter_var($data['password'], FILTER_SANITIZE_STRING));

        if ($email != '' && $senha != '') {
            $verificarNoBanco = $this->db->prepare("SELECT `id` FROM `users` WHERE `email` = ? AND `password` = ?");
            $verificarNoBanco->execute(array($email, $senha));

            if ($verificarNoBanco->rowCount() > 0) {
                $_SESSION["autenticado"] = true;

                return $response->withRedirect('/omatapalo_v3/home');
            } else {
                $vars['erro'] = 'As credenciais estão erradas';
                return $this->view->render($response, 'login.twig');
            }
        } else {
            $vars['erro'] = 'Preencha todos os campos.';

            return $this->container->view->render($response, 'login.twig');
        }
    }

    public function logout($request, $response)
    {
        unset($_SESSION["autenticado"]);
        session_destroy();

        return $response->withRedirect('/omatapalo_v3/login');
    }

    public function teste($request, $response)
    {
        return $this->view->render($response, 'home.twig', ['name' => 'Luis']);
    }
}
