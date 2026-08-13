<?php
// sync/subir.php  — CLI: sube el paquete de la clínica a SIMAC cloud.
// Uso (Task Scheduler / cron): php sync/subir.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;

$res = (new SyncController())->subir();
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
