<?php
// sync/subir_archivos.php  — CLI: sube control de parámetros (prespuestos,
// servicios, clientes, convenios y/o medicos) a SIMAC cloud.
// Uso (Task Scheduler / cron): php sync/subir_archivos.php
// Solo sube cuando hay archivos nuevos o modificados (comparación de hash):
// evita reenviar lo mismo en cada corrida del cronjob de 2 minutos.
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;

$res = (new SyncController())->subirArchivos(true);
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;