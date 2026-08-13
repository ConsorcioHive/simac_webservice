<?php
// sync/procesar.php  — CLI: proceso de transición de archivos.
// Mueve todo lo que haya en inbox a outbox y dispara la subida a la nube.
// Uso (Task Scheduler / cron): php sync/procesar.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;
use App\Services\FileDropService;

$ctrl = new SyncController();
$drop = new FileDropService();

$inbox = $drop->listarInbox();
foreach ($inbox as $archivo) {
    $drop->moverAOutbox($archivo);
    echo "Movido a outbox: $archivo" . PHP_EOL;
}

// Ejemplo: empaquetar outbox y subirlo (stub por ahora).
$outbox = $drop->listarOutbox();
if (!empty($outbox)) {
    $paquete = ['archivos' => $outbox, 'generado_en' => date('c')];
    $res = $ctrl->subir();
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo "Proceso de transición finalizado." . PHP_EOL;
