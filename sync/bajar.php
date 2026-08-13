<?php
// sync/bajar.php  — CLI: baja un paquete desde SIMAC cloud.
// Uso (Task Scheduler / cron): php sync/bajar.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;

$res = (new SyncController())->bajar();
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
