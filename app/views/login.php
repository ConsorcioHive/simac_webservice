<?php
// app/views/login.php
$page_title = APP_NAME . ' - Iniciar Sesión';
require APP_PATH . '/views/layout/header.php';
?>
<div class="card" style="max-width: 380px; margin: 40px auto;">
    <h2>Iniciar sesión</h2>
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
