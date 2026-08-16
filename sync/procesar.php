<?php
// sync/procesar.php  — CLI: proceso automático de sincronización.
// Detecta si llegaron archivos NUEVOS de control de parámetros (depositados por
// el sistema externo en public/uploads/empresas/<code>/archivos/control_parametros/)
// y los sube a SIMAC cloud. Solo sube si el contenido cambió respecto al último
// envío exitoso. También lee la respuesta de la nube si la hay.
// Uso (Task Scheduler / cron): php sync/procesar.php
require __DIR__ . '/../config/config.php';

use App\Controllers\SyncController;
use App\Services\FileDropService;

$ctrl = new SyncController();
$drop = new FileDropService();

// 1) Transición inbox -> outbox (compatibilidad con flujo anterior)
$inbox = $drop->listarInbox();
foreach ($inbox as $archivo) {
    $drop->moverAOutbox($archivo);
    echo "Movido a outbox: $archivo" . PHP_EOL;
}

// 2) Subir control de parámetros solo si cambió
$res = $ctrl->subirArchivos(true);
if (empty($res['sin_cambios'])) {
    echo "Subida: " . json_encode($res, JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo "Subida: sin cambios, no se envió." . PHP_EOL;
}

// 3) Leer respuesta de la nube (resultado del procesamiento)
$resResp = $ctrl->leerRespuesta();
if ($resResp['ok']) {
    echo "Respuesta de la nube: " . json_encode($resResp['data'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

echo "Proceso automático finalizado." . PHP_EOL;