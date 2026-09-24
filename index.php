<?php
// index.php — Router principal (estilo SIMAC: todas las rutas vía ?page=)
require __DIR__ . '/config/config.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EmpresaController;
use App\Controllers\UsuarioController;
use App\Controllers\SyncController;
use App\Controllers\AlmacenesController;
use App\Controllers\LotesEgresosController;
use App\Controllers\ApiController;
use App\Helpers\Session;
use App\Helpers\Json;

try {

// ====== API v1 (sin sesión; auth por X-API-Key) ======
// Soporta: /simac_webservice/api/v1/..., /api/v1/..., ?page=api_v1&path=lotes
$apiPath = null;
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
if (preg_match('#(?:^|/)api/v1(/.*)?$#', $uriPath, $m)) {
    $apiPath = '/' . ltrim($m[1] ?? '', '/');
} elseif (($_GET['page'] ?? $_POST['page'] ?? '') === 'api_v1') {
    $apiPath = '/' . ltrim((string)($_GET['path'] ?? $_POST['path'] ?? ''), '/');
}
if ($apiPath !== null) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    (new ApiController())->handle($apiPath, $method);
    exit;
}

// Determinar página solicitada (GET y fallback a POST para llamadas AJAX)
$page = $_GET['page'] ?? $_POST['page'] ?? 'dashboard';

// ====== MIDDLEWARE SIMPLE DE AUTENTICACIÓN ======
$publicPages = [
    'login',
    'login_process',
];

if (!in_array($page, $publicPages, true) && !Session::isLoggedIn()) {
    $_SESSION['flash_info'] = 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {

    case 'login':
        $controller = new AuthController();
        $controller->showLogin();
        break;

    case 'login_process':
        $controller = new AuthController();
        $controller->loginProcess();
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'empresas_form':
        $controller = new EmpresaController();
        $controller->form();
        break;

    case 'empresas_guardar':
        $controller = new EmpresaController();
        $controller->guardar();
        break;

    case 'empresas_crear_carpeta_entidad':
        $controller = new EmpresaController();
        $controller->crearCarpetaEntidad();
        break;

    case 'empresas_contenedor':
        $controller = new EmpresaController();
        $controller->contenedor();
        break;

    case 'usuarios':
        $controller = new UsuarioController();
        $controller->index();
        break;

    case 'usuarios_cambiar_foto':
        $controller = new UsuarioController();
        $controller->cambiarFoto();
        break;

    case 'sync':
        $controller = new SyncController();
        $controller->index();
        break;

    case 'sync_respuesta':
        $controller = new SyncController();
        $controller->respuestaAjax();
        break;

    case 'sync_log':
        $controller = new SyncController();
        $controller->log();
        break;

    case 'almacenes':
        $controller = new AlmacenesController();
        $controller->index();
        break;

    case 'almacenes_colocar':
        $controller = new AlmacenesController();
        $result = $controller->colocarJson();
        if (!empty($result['ok'])) {
            header('Location: index.php?page=almacenes&m=' . urlencode($result['message'] ?? 'OK'));
        } else {
            header('Location: index.php?page=almacenes&m=' . urlencode('Error: ' . ($result['message'] ?? 'desconocido')));
        }
        exit;

    case 'lotes_egresos':
        $controller = new LotesEgresosController();
        $controller->index();
        break;

    case 'lotes_egresos_traer':
        $controller = new LotesEgresosController();
        $result = $controller->traer();
        header('Location: index.php?page=lotes_egresos&m=' . urlencode((!empty($result['ok']) ? '' : 'Error: ') . ($result['message'] ?? 'OK')));
        exit;

    case 'lotes_egresos_traer_todos':
        $controller = new LotesEgresosController();
        $result = $controller->traerTodos();
        header('Location: index.php?page=lotes_egresos&m=' . urlencode((!empty($result['ok']) ? '' : 'Error: ') . ($result['message'] ?? 'OK')));
        exit;

    case 'lotes_egresos_explorar':
        $controller = new LotesEgresosController();
        $controller->explorar();
        break;

    case 'api_v1':
        // Fallback si no hay rewrite: index.php?page=api_v1&path=lotes
        (new ApiController())->handle(
            '/' . ltrim((string)($_GET['path'] ?? ''), '/'),
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
        break;

    default:
        http_response_code(404);
        die('Página no encontrada.');
}

} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}