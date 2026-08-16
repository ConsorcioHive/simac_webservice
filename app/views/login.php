<?php
// app/views/login.php
// Login clonado fielmente del de SIMAC (carátula + formulario), ajustado al backend local.
$page_title = APP_NAME . ' - Iniciar Sesión';

$imgUrl = function ($p) {
    if (empty($p)) return null;
    if (preg_match('~^https?://~i', $p)) return $p;
    return BASE_URL . ltrim($p, '/');
};
$loginLogoUrl    = $imgUrl($empresa['logo_url'] ?? null) ?: BASE_URL . 'assets/images/logo/simac-logo.png';
$loginCaratulaUrl = $imgUrl($empresa['empresa_caratula_url'] ?? $empresa['caratula_url'] ?? null);
?>
<?php require BASE_PATH . '/partial/header.php'; ?>
<?php require BASE_PATH . '/partial/loader.php'; ?>

<style>
  :root {
    --primary-blue: #005691;
    --accent-yellow: #ffc107;
    --soft-bg: #f8f9fa;
    --glass-bg: rgba(255, 255, 255, 0.9);
  }
  body { background-color: var(--soft-bg); font-family: 'Rubik', sans-serif; }
  .login-wrapper {
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    padding: 40px 0; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  }
  .login-container {
    background: white; border-radius: 20px; overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1); max-width: 1000px; width: 95%;
    display: flex; animation: fadeIn 0.8s ease-out;
  }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  .login-side-img { width: 45%; position: relative; overflow: hidden; }
  .login-side-img img.cover { width: 100%; height: 100%; object-fit: cover; filter: brightness(1); }
  .login-side-img .overlay {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(to bottom, rgba(0,86,145,0.15), rgba(0,0,0,0.35));
    display: flex; flex-direction: column; justify-content: flex-end; align-items: center;
    padding: 40px; text-align: center; color: white;
  }
  .login-form-container { width: 55%; padding: 60px; background: var(--glass-bg); display: flex; flex-direction: column; justify-content: center; }
  .login-form-container h4 { color: var(--primary-blue); font-weight: 800; margin-bottom: 10px; font-size: 1.8rem; }
  .login-form-container p.subtitle { color: #6c757d; margin-bottom: 35px; }
  .form-label { font-weight: 600; font-size: 0.85rem; color: #495057; margin-bottom: 5px; }
  .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
  .form-control:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 0.2rem rgba(0,86,145,0.15); }
  .btn-login-submit { background-color: var(--primary-blue); border: none; padding: 12px; border-radius: 10px; font-weight: 700; letter-spacing: 0.5px; transition: transform 0.2s; }
  .btn-login-submit:hover { background-color: #00406d; transform: translateY(-2px); }
  .show-hide-toggle { position: absolute; right: 15px; top: 12px; cursor: pointer; color: #aaa; }
  @media (max-width: 991px) {
    .login-container { flex-direction: column; }
    .login-side-img, .login-form-container { width: 100%; }
    .login-side-img { height: 250px; }
  }
</style>

<div class="login-wrapper">
  <div class="login-container">
    <div class="login-side-img">
      <?php if ($loginCaratulaUrl): ?>
        <img class="cover" src="<?= htmlspecialchars($loginCaratulaUrl) ?>" alt="Cover">
      <?php else: ?>
        <img class="cover" src="<?= BASE_URL ?>assets/images/logo/simac-logo.png" alt="Cover">
      <?php endif; ?>
      <div class="overlay">
        <h3>Bienvenido de nuevo</h3>
        <p>Accede a la administración de tu clínica con eficiencia y seguridad.</p>
      </div>
    </div>

    <div class="login-form-container">
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
          <i class="fa fa-exclamation-triangle me-2"></i>
          Credenciales incorrectas o usuario inactivo.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($loginLogoUrl) ?>" alt="Logo" style="max-height: 80px; max-width: 100%;">
      </div>

      <form class="theme-form" method="post" action="<?= BASE_URL ?>index.php?page=login_process">
        <h4>Iniciar sesión</h4>
        <p class="subtitle text-muted">Ingresa tus credenciales para acceder</p>

        <div class="mb-3">
          <label class="form-label">Usuario o Correo Electrónico</label>
          <input class="form-control" type="text" name="login" required placeholder="Usuario o tu@correo.com" autofocus>
        </div>

        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label">Clave secreta</label>
          </div>
          <div class="position-relative">
            <input class="form-control" id="passwordInput" type="password" name="password" required placeholder="*********">
            <span class="show-hide-toggle"><i class="fa fa-eye" id="togglePasswordIcon"></i></span>
          </div>
        </div>

        <div class="mb-4">
          <button class="btn btn-primary btn-login-submit w-100" type="submit">Entrar al Sistema</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var passwordInput = document.getElementById('passwordInput');
  var togglePassword = document.querySelector('.show-hide-toggle');
  var toggleIcon = document.getElementById('togglePasswordIcon');
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function() {
      var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      toggleIcon.classList.toggle('fa-eye');
      toggleIcon.classList.toggle('fa-eye-slash');
    });
  }
});
</script>

<?php require BASE_PATH . '/partial/scripts.php'; ?>
<?php require BASE_PATH . '/partial/footer-end.php'; ?>
