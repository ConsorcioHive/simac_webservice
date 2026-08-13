<?php
// sync.php
require __DIR__ . '/config/config.php';

use App\Controllers\SyncController;
use App\Models\SyncLog;
use App\Helpers\Session;

Session::requireLogin();

$ctrl = new SyncController();
$syncLog = new SyncLog();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    if ($_POST['_action'] === 'subir') {
        $res = $ctrl->subir();
        $msg = $res['ok'] ? 'Subida enviada a SIMAC.' : ('Error: ' . ($res['message'] ?? 'desconocido'));
    } elseif ($_POST['_action'] === 'bajar') {
        $res = $ctrl->bajar();
        $msg = $res['ok'] ? 'Bajada recibida de SIMAC.' : ('Error: ' . ($res['message'] ?? 'desconocido'));
    } elseif ($_POST['_action'] === 'mover_outbox') {
        $ctrl->moverAOutbox($_POST['archivo']);
        $msg = 'Archivo movido a outbox.';
    }
    header('Location: ' . BASE_URL . 'sync.php?m=' . urlencode($msg));
    exit;
}

$inbox  = $ctrl->listarInbox();
$outbox = $ctrl->listarOutbox();
$logs   = $syncLog->ultimos(15);

function mostrarArchivos($lista)
{
    if (empty($lista)) return '<p><em>Sin archivos.</em></p>';
    echo '<ul>';
    foreach ($lista as $f) echo '<li>' . htmlspecialchars($f) . '</li>';
    echo '</ul>';
}

$page_title = APP_NAME . ' - Sincronización';
require APP_PATH . '/views/layout/header.php';
if (!empty($_GET['m'])) echo '<p class="card" style="color:#14532d">' . htmlspecialchars($_GET['m']) . '</p>';
?>

<div class="card">
    <h3>Sincronización con SIMAC (nube)</h3>
    <p>Modo stub actual: <strong><?= SIMAC_API_STUB ? 'ACTIVO (simulado, sin nube real)' : 'OFF (usa SIMAC_CLOUD_URL)' ?></strong></p>
    <form method="post" style="display:inline">
        <input type="hidden" name="_action" value="subir">
        <button type="submit">Subir paquete a la nube</button>
    </form>
    <form method="post" style="display:inline">
        <input type="hidden" name="_action" value="bajar">
        <button type="submit">Bajar paquete de la nube</button>
    </form>
</div>

<div class="card">
    <h3>Contenedor de archivos (transición)</h3>
    <p><strong>Inbox</strong> (entrada — colocar aquí los archivos a mover):</p>
    <?php mostrarArchivos($inbox); ?>
    <p><strong>Outbox</strong> (salida — listos para subir a la nube):</p>
    <?php mostrarArchivos($outbox); ?>
</div>

<div class="card">
    <h3>Registro de sincronización</h3>
    <table>
        <thead><tr><th>Fecha</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th>Mensaje</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['creado_en']) ?></td>
                <td><?= htmlspecialchars($l['tipo']) ?></td>
                <td><?= htmlspecialchars($l['direccion']) ?></td>
                <td><?= htmlspecialchars($l['estado']) ?></td>
                <td><?= htmlspecialchars(substr($l['mensaje'] ?? '', 0, 80)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require APP_PATH . '/views/layout/footer.php';
