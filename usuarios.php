<?php
// usuarios.php
require __DIR__ . '/config/config.php';

use App\Controllers\UsuarioController;
use App\Helpers\Session;
use App\Models\Usuario;

Session::requireLogin();

$ctrl = new UsuarioController();
$model = new Usuario();
$empresaId = Session::get('empresa_id');

// Acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['_action'])) {
        if ($_POST['_action'] === 'crear') {
            $res = $ctrl->store([
                'nombre'  => $_POST['nombre'],
                'apellido'=> $_POST['apellido'],
                'cedula'  => $_POST['cedula'],
                'email'   => $_POST['email'],
                'login'   => $_POST['login'],
                'password'=> $_POST['password'],
                'rol'     => $_POST['rol'],
                'estado'  => $_POST['estado'],
                'cargo'   => $_POST['cargo'],
                'telefono'=> $_POST['telefono'],
            ]);
            $msg = $res['ok'] ? 'Usuario creado.' : ('Error: ' . $res['msg']);
        } elseif ($_POST['_action'] === 'editar') {
            $res = $ctrl->update((int)$_POST['id'], [
                'nombre'  => $_POST['nombre'],
                'apellido'=> $_POST['apellido'],
                'cedula'  => $_POST['cedula'],
                'email'   => $_POST['email'],
                'login'   => $_POST['login'],
                'password'=> $_POST['password'] ?? '',
                'rol'     => $_POST['rol'],
                'estado'  => $_POST['estado'],
                'cargo'   => $_POST['cargo'],
                'telefono'=> $_POST['telefono'],
            ]);
            $msg = $res['ok'] ? 'Usuario actualizado.' : 'Error al actualizar.';
        } elseif ($_POST['_action'] === 'eliminar') {
            $ctrl->delete((int)$_POST['id']);
            $msg = 'Usuario eliminado.';
        }
        header('Location: ' . BASE_URL . 'usuarios.php?m=' . urlencode($msg));
        exit;
    }
}

// Vista
$editar = null;
if (isset($_GET['edit'])) {
    $editar = $model->obtenerPorId((int)$_GET['edit']);
}
$usuarios = $ctrl->index();

$page_title = APP_NAME . ' - Usuarios';
require APP_PATH . '/views/layout/header.php';
if (!empty($msg)) echo '<p class="card" style="color:#14532d">' . htmlspecialchars($msg) . '</p>';
?>

<div class="card">
    <h3><?= $editar ? 'Editar usuario' : 'Nuevo usuario' ?></h3>
    <form method="post" action="<?= BASE_URL ?>usuarios.php">
        <input type="hidden" name="_action" value="<?= $editar ? 'editar' : 'crear' ?>">
        <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
        <input type="text" name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
        <input type="text" name="apellido" placeholder="Apellido" value="<?= htmlspecialchars($editar['apellido'] ?? '') ?>">
        <input type="text" name="cedula" placeholder="Cédula" value="<?= htmlspecialchars($editar['cedula'] ?? '') ?>">
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
        <input type="text" name="login" placeholder="Login" required value="<?= htmlspecialchars($editar['login'] ?? '') ?>">
        <input type="text" name="cargo" placeholder="Cargo" value="<?= htmlspecialchars($editar['cargo'] ?? '') ?>">
        <input type="text" name="telefono" placeholder="Teléfono" value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
        <input type="password" name="password" placeholder="<?= $editar ? 'Dejar en blanco para no cambiar' : 'Contraseña' ?>" <?= $editar ? '' : 'required' ?>>
        <select name="rol">
            <option value="operador" <?= ($editar['rol'] ?? '') === 'operador' ? 'selected' : '' ?>>Operador</option>
            <option value="admin" <?= ($editar['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="superadmin" <?= ($editar['rol'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
        </select>
        <select name="estado">
            <option value="activo" <?= ($editar['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= ($editar['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <button type="submit"><?= $editar ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <?php if ($editar): ?><a href="<?= BASE_URL ?>usuarios.php">Cancelar</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Usuarios de la clínica</h3>
    <table>
        <thead><tr><th>Nombre</th><th>Login</th><th>Email</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')) ?></td>
                <td><?= htmlspecialchars($u['login'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['rol'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['estado'] ?? '') ?></td>
                <td>
                    <a href="<?= BASE_URL ?>usuarios.php?edit=<?= $u['id'] ?>">Editar</a>
                    <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')">
                        <input type="hidden" name="_action" value="eliminar">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" style="background:#b91c1c;width:auto">X</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require APP_PATH . '/views/layout/footer.php';
