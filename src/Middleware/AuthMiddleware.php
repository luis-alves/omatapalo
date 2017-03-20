<?php

namespace Src\Middleware;

class AuthMiddleware
{
    public function __invoke($request, $response, $next)
    {
        if (!isset($_SESSION['autenticado'])) {
            return $response->withRedirect('login');
        }
        $response = $next($request, $response);

        return $response;
    }
}
