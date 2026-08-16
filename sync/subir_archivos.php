<?php
// sync/subir_archivos.php  — CLI: sube control de parámetros (prespuestos/servicios) a SIMAC cloud.
// Uso (Task Scheduler / cron): php sync/subir_archivos.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;

$res = (new SyncController())->subirArchivos();
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;