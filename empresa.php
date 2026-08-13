<?php
// empresa.php
require __DIR__ . '/config/config.php';

use App\Controllers\EmpresaController;
use App\Helpers\Session;

Session::requireLogin();

$ctrl = new EmpresaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $ctrl->guardar([
        'company_code'        => $_POST['company_code'],
        'nombre'              => $_POST['nombre'],
        'rif'                 => $_POST['rif'],
        'direccion_fiscal'    => $_POST['direccion_fiscal'],
        'telefono'            => $_POST['telefono'],
        'email'               => $_POST['email'],
        'logo_url'            => $_POST['logo_url'],
        'caratula_url'        => $_POST['caratula_url'],
        'empresa_descripcion' => $_POST['empresa_descripcion'],
    ]);
    $msg = $res['ok'] ? 'Empresa actualizada.' : ('Error: ' . $res['msg']);
    header('Location: ' . BASE_URL . 'empresa.php?m=' . urlencode($msg));
    exit;
}

$empresa = $ctrl->obtener();

$page_title = APP_NAME . ' - Empresa';
require APP_PATH . '/views/layout/header.php';
if (!empty($_GET['m'])) echo '<p class="card" style="color:#14532d">' . htmlspecialchars($_GET['m']) . '</p>';
?>

<div class="card">
    <h3>Datos de la empresa</h3>
    <p><em>Sistema single-tenant: esta es la única empresa de la instalación. No se puede crear otra.</em></p>
    <form method="post" action="<?= BASE_URL ?>empresa.php">
        <input type="text" name="company_code" placeholder="Código de clínica (SIMAC)" required value="<?= htmlspecialchars($empresa['company_code'] ?? '') ?>">
        <input type="text" name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($empresa['nombre'] ?? '') ?>">
        <input type="text" name="rif" placeholder="RIF" value="<?= htmlspecialchars($empresa['rif'] ?? '') ?>">
        <input type="text" name="direccion_fiscal" placeholder="Dirección fiscal" value="<?= htmlspecialchars($empresa['direccion_fiscal'] ?? '') ?>">
        <input type="text" name="telefono" placeholder="Teléfono" value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">
        <input type="text" name="logo_url" placeholder="URL del logo" value="<?= htmlspecialchars($empresa['logo_url'] ?? '') ?>">
        <input type="text" name="caratula_url" placeholder="URL de la carátula (fondo de login)" value="<?= htmlspecialchars($empresa['caratula_url'] ?? '') ?>">
        <textarea name="empresa_descripcion" placeholder="Descripción"><?= htmlspecialchars($empresa['empresa_descripcion'] ?? '') ?></textarea>
        <button type="submit">Guardar cambios</button>
    </form>
</div>

<?php
require APP_PATH . '/views/layout/footer.php';
