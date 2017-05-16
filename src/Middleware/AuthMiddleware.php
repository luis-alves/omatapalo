<?php

namespace Src\Middleware;

class AuthMiddleware
{
    public function __invoke($request, $response, $next)
    {
        if (!isset($_SESSION['autenticado'])) {
            return $response->withRedirect('/omatapalo/login');
        }
        $response = $next($request, $response);

        return $response;
    }
}
