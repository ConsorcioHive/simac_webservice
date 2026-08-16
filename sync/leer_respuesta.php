<?php
// sync/leer_respuesta.php  — CLI: lee la respuesta que dejó la nube tras procesar.
// Uso (Task Scheduler / cron): php sync/leer_respuesta.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;

$res = (new SyncController())->leerRespuesta();
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;