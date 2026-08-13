<?php
// config/config.php
// Bootstrap del sistema local simac_webservice.
// Define constantes, carga la capa de datos y el autoloader de App\.

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('BASE_URL', '/simac_webservice/');
define('APP_NAME', 'SIMAC Webservice');

// ── Destino en la nube (SIMAC) ──────────────────────────────────────────────
// URL base de la API de SIMAC. Se configura por instalación de clínica.
define('SIMAC_CLOUD_URL', 'https://simacweb.app');
// Token de autenticación de esta clínica ante SIMAC cloud (lo define el admin).
define('SIMAC_API_TOKEN', '');
// Modo stub: cuando no existen endpoints reales en la nube, el cliente
// simula la subida/bajada escribiendo archivos en storage/sync/.
define('SIMAC_API_STUB', true);

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
