<?php
declare(strict_types=1);

// Autoload simples (sem Composer)
spl_autoload_register(function (string $class) {
    $base = __DIR__ . '/../';
    $file = $base . str_replace(['App\\', '\\'], ['app/', '/'], $class) . '.php';
    if (file_exists($file)) require_once $file;
});

// Configurações
require_once __DIR__ . '/../config/config.php';

// Sessão
session_name(SESSION_NAME);
session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'httponly' => true, 'samesite' => 'Lax']);
session_start();

// Rotas
use App\Core\Router;

$router = new Router();

// Auth
$router->get('/login',  'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Página pública de download do ScanTE Relay
$router->get('/scan-relay', 'RelayDownloadController', 'index');

// Checkout público (sem autenticação)
$router->get( '/checkout',           'CheckoutController', 'index');
$router->post('/checkout',           'CheckoutController', 'processar');
$router->get( '/checkout/pagamento',  'CheckoutController', 'pagamento');
$router->post('/checkout/pagar',               'CheckoutController', 'pagar');
$router->post('/checkout/processar-pagamento', 'CheckoutController', 'processarPagamento');
$router->get( '/checkout/status',     'CheckoutController', 'status');
$router->get( '/checkout/sucesso',    'CheckoutController', 'sucesso');
$router->get( '/checkout/cancelado',  'CheckoutController', 'cancelado');

// Admin
$router->get( '/admin',                             'Admin/DashboardController', 'index');
$router->get( '/admin/empresas',                    'Admin/EmpresasController',  'index');
$router->get( '/admin/empresas/criar',              'Admin/EmpresasController',  'criar');
$router->post('/admin/empresas/criar',              'Admin/EmpresasController',  'criar');
$router->get( '/admin/empresas/{id}',               'Admin/EmpresasController',  'ver');
$router->get( '/admin/empresas/{id}/editar',        'Admin/EmpresasController',  'editar');
$router->post('/admin/empresas/{id}/editar',        'Admin/EmpresasController',  'editar');
$router->get( '/admin/empresas/{id}/excluir',       'Admin/EmpresasController',  'excluir');
$router->post('/admin/empresas/{id}/gerar-licencas','Admin/EmpresasController',  'gerarLicencas');
$router->get( '/admin/manual',                      'Admin/ManualController',       'index');
$router->get( '/admin/configuracoes',               'Admin/ConfiguracoesController', 'index');
$router->post('/admin/configuracoes/salvar',         'Admin/ConfiguracoesController', 'salvar');
$router->post('/admin/configuracoes/limpar-chave',   'Admin/ConfiguracoesController', 'limparChave');
$router->get( '/admin/pagamentos',                  'Admin/PagamentosController', 'index');
$router->post('/admin/pagamentos/registrar',        'Admin/PagamentosController', 'registrar');
$router->get( '/admin/pagamentos/{id}/excluir',     'Admin/PagamentosController', 'excluir');
$router->get( '/admin/licencas',                    'Admin/LicencasController',  'index');
$router->post('/admin/licencas/gerar',              'Admin/LicencasController',  'gerar');
$router->get( '/admin/licencas/{id}',               'Admin/LicencasController',  'ver');
$router->post('/admin/licencas/{id}/aprovar',       'Admin/LicencasController',  'aprovar');
$router->post('/admin/licencas/{id}/revogar',       'Admin/LicencasController',  'revogar');
$router->post('/admin/licencas/{id}/reativar',      'Admin/LicencasController',  'reativar');
$router->post('/admin/licencas/{id}/estender',      'Admin/LicencasController',  'estender');
$router->post('/admin/licencas/{id}/alterar-tipo',  'Admin/LicencasController',  'alterarTipo');
$router->post('/admin/licencas/{id}/transferir',    'Admin/LicencasController',  'transferir');

// Empresa
$router->get( '/empresa',                                      'Empresa/DashboardController', 'index');
$router->get( '/empresa/licencas',                             'Empresa/LicencasController',  'index');
$router->get( '/empresa/licencas/{id}',                        'Empresa/LicencasController',  'ver');
$router->post('/empresa/licencas/{id}/transferir',             'Empresa/LicencasController',  'solicitarTransferencia');

// API REST (app Android)
$router->post('/api/licenca/validar',               'Api/LicencaController',      'validar');
$router->post('/api/webhook/mercadopago',           'Api/LicencaController',         'webhookMercadoPago');
$router->post('/api/webhook/pagarme',              'Api/PagarmeWebhookController',  'handle');
$router->post('/api/dispositivo/ping',              'Api/DispositivoController',  'ping');

// Admin — Licenças do ScanTE Relay
$router->get( '/admin/relay-licencas',              'Admin/RelayLicencasController', 'index');
$router->post('/admin/relay-licencas/gerar',         'Admin/RelayLicencasController', 'gerar');
$router->get( '/admin/relay-licencas/{id}/baixar',   'Admin/RelayLicencasController', 'baixar');

// Admin — Dispositivos
$router->get( '/admin/dispositivos',                     'Admin/DispositivosController', 'index');
$router->post('/admin/dispositivos/atribuir-empresa',    'Admin/DispositivosController', 'atribuirEmpresa');
$router->post('/admin/dispositivos/atribuir-licenca',    'Admin/DispositivosController', 'atribuirLicenca');
$router->post('/admin/dispositivos/excluir',             'Admin/DispositivosController', 'excluir');

// Redirecionar raiz para login
$router->get('/', 'AuthController', 'login');

// Despachar
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
