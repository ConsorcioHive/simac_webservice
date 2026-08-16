<?php
// partial/topbar_local.php
// Topbar clonado del layout Cuba de SIMAC, ajustado a simac_webservice.
$defaultLogo = BASE_URL . 'assets/images/logo/simac-logo.png';
$logoUrl = $defaultLogo;
if (!empty($empresaActiva) && !empty($empresaActiva['logo_url'])) {
    $path = $empresaActiva['logo_url'];
    $logoUrl = preg_match('~^https?://~i', $path) ? $path : BASE_URL . ltrim($path, '/');
}

// Foto del usuario logueado
$usuarioFoto = null;
if (!empty($_SESSION['usuario_id'])) {
    $pdo = \App\Core\Database::getConnection();
    $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $usuarioFoto = $stmt->fetchColumn() ?: null;
}

$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuarioRol    = $_SESSION['usuario_rol'] ?? 'operador';
switch ($usuarioRol) {
    case 'admin':   $rolLabel = 'Administrador'; break;
    case 'superadmin': $rolLabel = 'Superadmin'; break;
    default:        $rolLabel = ucfirst($usuarioRol);
}
?>
<div class="page-header" style="z-index: 1040 !important;">
  <div class="header-wrapper row m-0">
    <div class="header-logo-wrapper col-auto p-0">
      <div class="logo-wrapper">
        <a href="<?= BASE_URL ?>index.php">
          <img class="img-fluid" src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo empresa">
        </a>
      </div>
      <div class="toggle-sidebar toggle-sidebar-topbar ms-3">
        <i class="status_toggle middle sidebar-toggle" data-feather="grid"></i>
      </div>
      <div class="toggle-sidebar d-xl-none">
        <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
      </div>
    </div>

    <div class="nav-right col-auto pull-right right-header p-0 ms-auto">
      <ul class="nav-menus">
        <li>
          <div class="mode">
            <svg><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#moon"></use></svg>
          </div>
        </li>
        <li class="profile-nav onhover-dropdown pe-0 py-0">
          <div class="media profile-media">
            <?php if ($usuarioFoto): ?>
              <img class="rounded-circle" src="<?= BASE_URL . htmlspecialchars($usuarioFoto) ?>" alt="<?= htmlspecialchars($usuarioNombre) ?>" width="35" height="35" style="object-fit: cover;">
            <?php else: ?>
              <img class="rounded-circle" src="<?= BASE_URL ?>assets/images/dashboard/profile.png" alt="usuario" width="35" height="35" style="object-fit: cover;">
            <?php endif; ?>
            <div class="media-body">
              <span><?= htmlspecialchars($usuarioNombre) ?></span>
              <p class="mb-0 font-roboto"><?= htmlspecialchars($rolLabel) ?> <i class="middle fa fa-angle-down"></i></p>
            </div>
          </div>
          <ul class="profile-dropdown onhover-show-div">
            <li><a href="<?= BASE_URL ?>index.php?page=empresas_form"><i data-feather="settings"></i><span>Configuración</span></a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=logout"><i data-feather="log-out"></i><span>Cerrar</span></a></li>
          </ul>
        </li>
      </ul>
      <div class="mobile-nav-toggle d-lg-none text-end px-2">
        <button type="button" class="btn btn-link text-reset p-0 border-0" onclick="toggleMobileMenu()" aria-label="Menú">
          <i class="fa fa-ellipsis-v f-20"></i>
        </button>
      </div>
    </div>
  </div>
</div>
