<?php
// index.php — Router principal (estilo SIMAC: todas las rutas vía ?page=)
require __DIR__ . '/config/config.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EmpresaController;
use App\Controllers\UsuarioController;
use App\Controllers\SyncController;
use App\Helpers\Session;

try {

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

    default:
        http_response_code(404);
        die('Página no encontrada.');
}

} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}