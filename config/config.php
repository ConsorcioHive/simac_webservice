<?php
// config/config.php
// Bootstrap del sistema local simac_webservice.
// Define constantes, carga la capa de datos y el autoloader de App\.
if (defined('BASE_PATH')) {
    return; // ya inicializado
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// URL base local: se resuelve desde la ruta real del script para que funcione
// siempre en local (subcarpeta o raíz) sin depender de un valor fijo.
$scriptDir = isset($_SERVER['SCRIPT_NAME'])
    ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/')
    : '';
if ($scriptDir === '' || $scriptDir === '/' || $scriptDir === '.') {
    $scriptDir = '';
}
define('BASE_URL', $scriptDir . '/');

define('APP_NAME', 'SIMAC Webservice');

// ── Destino en la nube (SIMAC) ──────────────────────────────────────────────
// URL base de la API de SIMAC. Se configura por instalación de clínica.
// El token de nube se lee de empresas.simac_api_token (por company_code);
// el valor de abajo es SOLO fallback para instalaciones sin fila configurada.
define('SIMAC_CLOUD_URL', 'https://simacweb.app');
define('SIMAC_API_TOKEN_FALLBACK', 'tok_simac_1013_demo');
if (!defined('SIMAC_API_TOKEN')) {
    define('SIMAC_API_TOKEN', SIMAC_API_TOKEN_FALLBACK);
}
// Modo real activo: la clínica se comunica con la nube (https://simacweb.app).
define('SIMAC_API_STUB', false);

// Autoloader de Composer (dompdf, phpmailer, phpspreadsheet, etc.)
require_once BASE_PATH . '/vendor/autoload.php';

// Carga de la capa de datos y helpers.
require APP_PATH . '/Core/Database.php';
require APP_PATH . '/Helpers/Session.php';
require APP_PATH . '/Helpers/Json.php';

// Autoloader para el namespace App\ (app/Core, app/Models, app/Controllers, ...).
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strpos($class, $prefix) === 0) {
        $rel  = substr($class, strlen($prefix));
        $file = APP_PATH . '/' . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
