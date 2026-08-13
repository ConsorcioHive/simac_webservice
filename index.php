<?php
// index.php — Dashboard básico (punto de partida para ampliar módulos)
require __DIR__ . '/config/config.php';

use App\Helpers\Session;
use App\Models\SyncLog;
use App\Models\Usuario;
use App\Models\Empresa;

Session::requireLogin();

$empresaNombre = Session::get('empresa_nombre');
$usuarioNombre = Session::get('usuario_nombre');
$empresaCodigo = Session::get('empresa_codigo');

$syncModel  = new SyncLog();
$usuarioModel = new Usuario();
$empresaModel = new Empresa();

$logs      = $syncModel->ultimos(5);
$totalUsuarios = count($usuarioModel->listarPorEmpresa(Session::get('empresa_id') ?? 0));
$empresa   = $empresaModel->obtenerUnica();

$modulos = [
    ['icon' => '👤', 'titulo' => 'Usuarios',        'desc' => 'Gestión de usuarios de la clínica', 'url' => 'usuarios.php'],
    ['icon' => '🏥', 'titulo' => 'Empresa',         'desc' => 'Datos de la clínica instalada',      'url' => 'empresa.php'],
    ['icon' => '🔄', 'titulo' => 'Sincronización',  'desc' => 'Subir/bajar datos con SIMAC nube',   'url' => 'sync.php'],
];

$page_title = APP_NAME . ' - Panel';
require APP_PATH . '/views/layout/header.php';
?>

<h1>Bienvenido, <?= htmlspecialchars($usuarioNombre) ?></h1>
<p>Empresa: <strong><?= htmlspecialchars($empresaNombre) ?></strong>
   (código: <?= htmlspecialchars($empresaCodigo ?? '—') ?>)</p>

<div style="display:flex; gap:16px; flex-wrap:wrap;">
    <?php foreach ($modulos as $m): ?>
        <a href="<?= BASE_URL . $m['url'] ?>" style="text-decoration:none; color:inherit; flex:1; min-width:220px;">
            <div class="card" style="height:100%;">
                <div style="font-size:32px;"><?= $m['icon'] ?></div>
                <h3 style="margin:8px 0 4px;"><?= $m['titulo'] ?></h3>
                <p style="color:#555; margin:0;"><?= $m['desc'] ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <h3>Resumen</h3>
    <ul>
        <li>Usuarios registrados: <strong><?= $totalUsuarios ?></strong></li>
        <li>Clínica instalada: <strong><?= htmlspecialchars($empresa['nombre'] ?? '—') ?></strong></li>
        <li>Modo de sincronización: <strong><?= SIMAC_API_STUB ? 'Stub (simulado)' : 'Nube real' ?></strong></li>
    </ul>
</div>

<div class="card">
    <h3>Últimas sincronizaciones</h3>
    <?php if (empty($logs)): ?>
        <p>Aún no hay registros de sincronización.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Dirección</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['creado_en']) ?></td>
                    <td><?= htmlspecialchars($l['tipo']) ?></td>
                    <td><?= htmlspecialchars($l['direccion']) ?></td>
                    <td><?= htmlspecialchars($l['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require APP_PATH . '/views/layout/footer.php';
