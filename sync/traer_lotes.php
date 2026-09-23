<?php
// sync/traer_lotes.php  — CLI: baja lotes de egreso pendientes desde SIMAC cloud.
// Uso (Task Scheduler / cron): php sync/traer_lotes.php
require __DIR__ . '/../config/config.php';

use App\Controllers\LotesEgresosController;

$res = (new LotesEgresosController())->traerTodos();
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
