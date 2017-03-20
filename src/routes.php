<?php
// Routes

// $app->get('/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");
//
//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });


$app->get('/login', 'Src\Controllers\LoginAction:index');
$app->post('/login', 'Src\Controllers\LoginAction:logar');
$app->get('/logout', 'Src\Controllers\LoginAction:logout');

$app->group('', function() {

    /* Acessos cabeçalho */

    $this->get('/', 'Src\Controllers\HomeAction:index');
    $this->get('/home', 'Src\Controllers\HomeAction:index');
    $this->get('/tabelas', 'Src\Controllers\HomeAction:tabelas');
    $this->get('/mapas', 'Src\Controllers\HomeAction:mapas');
    $this->get('/relatorios', 'Src\Controllers\HomeAction:relatorios');
    $this->post('/ano', 'Src\Controllers\HomeAction:ano');
    $this->get('/ano', 'Src\Controllers\HomeAction:ano');
    $this->post('/cind', 'Src\Controllers\HomeAction:cind');
    $this->get('/cind', 'Src\Controllers\HomeAction:cind');


    $this->get('/import', 'Src\Controllers\Admin\ImportAction:dataImport');

    /* Mapas */

    $this->post('/dpgmi/imprimir', 'Src\Controllers\PrintAction:imprimir');

    $this->get('/mapas/dpgmi/{pagina}', 'Src\Controllers\MapaDpgmiAction:geral');
    $this->post('/mapas/dpgmi/{pagina}', 'Src\Controllers\MapaDpgmiAction:geral');
    $this->get('/mapas/dpgmi/faixasEtarias/{pagina}', 'Src\Controllers\MapaDpgmiAction:geral');
    $this->post('/mapas/dpgmi/faixasEtarias/{pagina}', 'Src\Controllers\MapaDpgmiAction:geral');

    $this->get('/ppiam', 'Src\Controllers\PpiamAction:mapa');
    $this->post('/ppiam', 'Src\Controllers\PpiamAction:mapa');

    $this->get('/mapas/mrpf/{item}', 'Src\Controllers\MapasResumoAction:mapas');
    $this->post('/mapas/mrpf/{item}', 'Src\Controllers\MapasResumoAction:mapas');

    $this->get('/mapas/ndim/{item}', 'Src\Controllers\NdimAction:geral');
    $this->post('/mapas/ndim/{item}', 'Src\Controllers\NdimAction:geral');
    $this->get('/mapas/ndim/{ano}/{mes}/{item}', 'Src\Controllers\NdimAction:dados');
    $this->post('/mapas/ndim/{ano}/{mes}/{item}', 'Src\Controllers\NdimAction:dados');
    $this->post('/mapas/geral/ndim/imprimir', 'Src\Controllers\PrintAction:imprimir_ndimGeral');

    $this->get('/mapas/ndim/detalhado/{ano}/{mes}/{item}', 'Src\Controllers\NdimAction:detalhado');
    $this->post('/mapas/ndim/detalhado/{ano}/{mes}/{item}', 'Src\Controllers\NdimAction:detalhado');
    $this->post('/mapas/detalhado/ndim/imprimir', 'Src\Controllers\PrintAction:imprimir_ndimDetalhado');


    /* Relatórios */

    // $this->get('/relatorios/dresultados/{item}', 'Src\Controllers\DResultadosAction:demonstracaoResultados');
    // $this->post('/relatorios/dresultados/{item}', 'Src\Controllers\DResultadosAction:demonstracaoResultados');

    $this->get('/relatorios/mensal/dadosmensais/{item}', 'Src\Controllers\RelatorioMensalAction:dadosMensais');
    $this->post('/relatorios/mensal/dadosmensais/{item}', 'Src\Controllers\RelatorioMensalAction:dadosMensais');

    $this->get('/relatorios/mensal/dadosacumulados/{item}', 'Src\Controllers\RelatorioMensalAction:dadosAcumulados');
    $this->post('/relatorios/mensal/dadosacumulados/{item}', 'Src\Controllers\RelatorioMensalAction:dadosAcumulados');

    $this->get('/relatorios/mensal/custos/{item}', 'Src\Controllers\RelatorioMensalAction:custos');
    $this->post('/relatorios/mensal/custos/{item}', 'Src\Controllers\RelatorioMensalAction:custos');

    $this->get('/relatorios/mensal/operacional/{item}', 'Src\Controllers\RelatorioMensalAction:operacional');
    $this->post('/relatorios/mensal/operacional/{item}', 'Src\Controllers\RelatorioMensalAction:operacional');

    $this->get('/relatorios/mensal/facturacao/{item}', 'Src\Controllers\RelatorioMensalAction:facturacao');
    $this->post('/relatorios/mensal/facturacao/{item}', 'Src\Controllers\RelatorioMensalAction:facturacao');

    $this->get('/relatorios/mensal/resultado/{item}', 'Src\Controllers\RelatorioMensalAction:resultado');
    $this->post('/relatorios/mensal/resultado/{item}', 'Src\Controllers\RelatorioMensalAction:resultado');

    $this->get('/relatorios/mensal/vendas/{item}', 'Src\Controllers\RelatorioMensalAction:vendas');
    $this->post('/relatorios/mensal/vendas/{item}', 'Src\Controllers\RelatorioMensalAction:vendas');

    # Demonstação de resultados
    $this->get('/relatorios/dresultados', 'Src\Controllers\DResultadosAction:dResultados');
    $this->post('/relatorios/dresultados', 'Src\Controllers\DResultadosAction:dResultados');



    /* Tabelas */

    $this->get('/tabelas/precos/{destino}/{unidade}/{moeda}', 'Src\Controllers\PrecosAction:preco');
    $this->post('/tabelas/precos/{destino}/{unidade}/{moeda}', 'Src\Controllers\PrecosAction:preco');
    $this->post('/tabelas/precos/imprimir', 'Src\Controllers\PrintAction:imprimir_preco');

    $this->post('/tabelas/balanca', 'Src\Controllers\TabelasAction:balanca');
    $this->get('/tabelas/balanca', 'Src\Controllers\TabelasAction:balanca');
    $this->get('/tabelas/balanca/form', 'Src\Controllers\TabelasAction:form');




})->add(Src\Middleware\AuthMiddleware::class);
