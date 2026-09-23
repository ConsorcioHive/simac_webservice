<?php
// partial/sidebar_local.php
// Sidebar clonado del layout Cuba de SIMAC, ajustado a los módulos de simac_webservice.
$logoLight = BASE_URL . 'assets/images/logo/simac-logo.png';
if (!empty($empresaActiva) && !empty($empresaActiva['logo_url'])) {
    $path = $empresaActiva['logo_url'];
    if (preg_match('~^https?://~i', $path)) {
        $logoLight = $path;
    } else {
        $logoLight = BASE_URL . ltrim($path, '/');
    }
}
$rol = $_SESSION['usuario_rol'] ?? '';
$homePage = 'dashboard';
$current = $GLOBALS['_sidebar_current'] ?? 'dashboard';
$menu = [
    'dashboard'  => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'index.php?page=dashboard'],
    'usuarios'   => ['label' => 'Usuarios',  'icon' => 'user', 'url' => 'index.php?page=usuarios'],
    'empresa'    => ['label' => 'Empresa',   'icon' => 'briefcase', 'url' => 'index.php?page=empresas_form'],
    'almacenes'  => ['label' => 'Almacenes',  'icon' => 'package', 'url' => 'index.php?page=almacenes'],
    'lotes_egresos' => ['label' => 'Lotes de Egreso', 'icon' => 'folder', 'url' => 'index.php?page=lotes_egresos'],
    'sync'       => ['label' => 'Sincronización', 'icon' => 'refresh-cw', 'url' => 'index.php?page=sync'],
];
?>
<div class="sidebar-wrapper" sidebar-layout="default-sidebar">
  <div>
    <div class="logo-wrapper">
      <a href="<?= $menu[$homePage]['url'] ?>">
        <img id="sidebarLogoLight" class="img-fluid for-light" src="<?= htmlspecialchars($logoLight, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
        <img id="sidebarLogoDark" class="img-fluid for-dark"  src="<?= htmlspecialchars($logoLight, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
      </a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar">
        <i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i>
      </div>
    </div>

    <div class="logo-icon-wrapper">
      <a href="<?= $menu[$homePage]['url'] ?>">
        <img id="sidebarLogoIcon" class="img-fluid" src="<?= htmlspecialchars($logoLight, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="max-width: 45px; max-height: 45px; object-fit: contain;">
      </a>
    </div>

    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn">
            <div class="mobile-back text-end"><span>Atrás</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>

          <li class="sidebar-main-title">
            <div><h6>Menú Principal</h6></div>
          </li>

          <?php foreach ($menu as $key => $item): ?>
          <li class="sidebar-list">
            <i class="fa fa-thumb-tack"></i>
            <a class="sidebar-link sidebar-title link-nav <?= $current === $key ? 'active' : '' ?>" href="<?= BASE_URL . $item['url'] ?>">
              <i data-feather="<?= $item['icon'] ?>" style="width: 18px;"></i><span><?= $item['label'] ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
