<?php
// index.php
require __DIR__ . '/config/config.php';

use App\Helpers\Session;
use App\Models\SyncLog;

Session::requireLogin();

$empresaNombre = Session::get('empresa_nombre');
$usuarioNombre = Session::get('usuario_nombre');

$syncModel = new SyncLog();
$logs = $syncModel->ultimos(10);

$page_title = APP_NAME . ' - Panel';
require APP_PATH . '/views/layout/header.php';
?>
<h1>Bienvenido, <?= htmlspecialchars($usuarioNombre) ?></h1>
<p>Empresa: <strong><?= htmlspecialchars($empresaNombre) ?></strong>
   (código: <?= htmlspecialchars(Session::get('empresa_codigo') ?? '—') ?>)</p>

<div class="card">
    <h3>Últimas sincronizaciones</h3>
    <?php if (empty($logs)): ?>
        <p>Aún no hay registros de sincronización.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th>Reg.</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['creado_en']) ?></td>
                    <td><?= htmlspecialchars($l['tipo']) ?></td>
                    <td><?= htmlspecialchars($l['direccion']) ?></td>
                    <td><?= htmlspecialchars($l['estado']) ?></td>
                    <td><?= (int)$l['registros'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
require APP_PATH . '/views/layout/footer.php';
