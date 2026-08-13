<?php
// app/views/login.php
$page_title = APP_NAME . ' - Iniciar Sesión';

// Construye la URL pública de una ruta de archivo (relativa o absoluta).
$imgUrl = function ($p) {
    if (empty($p)) return null;
    if (strpos($p, 'http') === 0) return $p;
    if (strpos($p, 'public/') === 0) return BASE_URL . $p;
    return BASE_URL . 'public/' . ltrim($p, '/');
};

$logo     = $imgUrl($empresa['logo_url'] ?? null);
$caratula = $imgUrl($empresa['caratula_url'] ?? null);
$nombre   = htmlspecialchars($empresa['nombre'] ?? APP_NAME);
?>
<?php if ($caratula): ?>
    <style>
        body {
            background-image: linear-gradient(rgba(20,83,45,.55), rgba(20,83,45,.55)), url('<?= $caratula ?>');
            background-size: cover; background-position: center; background-repeat: no-repeat;
        }
    </style>
<?php endif; ?>
<div class="card" style="max-width: 380px; margin: 40px auto; background: rgba(255,255,255,.96);">
    <?php if ($logo): ?>
        <div style="text-align:center; margin-bottom:14px;">
            <img src="<?= $logo ?>" alt="Logo" style="max-height:90px; max-width:80%;">
        </div>
    <?php endif; ?>
    <h2 style="text-align:center;"><?= $nombre ?></h2>
    <h4 style="text-align:center; color:#555; margin-top:0;">Iniciar sesión</h4>
    <?php if ($error): ?>
        <p class="error">Credenciales incorrectas o usuario inactivo.</p>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>login.php">
        <label>Usuario o email</label>
        <input type="text" name="login" required autofocus>
        <label>Contraseña</label>
        <input type="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
</div>
<?php
require APP_PATH . '/views/layout/footer.php';
