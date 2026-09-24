<?php
// app/views/layout/app_start.php
// Inicio del chrome administrativo (clonado de SIMAC Cuba): header, topbar, sidebar, breadcrumb.
require BASE_PATH . '/partial/header.php';
require BASE_PATH . '/partial/loader.php';

$empresaModel = new \App\Models\Empresa();
$empresaActiva = $empresaModel->obtenerUnica();

$GLOBALS['_sidebar_current'] = $GLOBALS['_sidebar_current'] ?? 'dashboard';

// Badge sidebar: lotes "disponibles" (sin llamar a la nube)
$GLOBALS['_badge_lotes'] = ['disponibles' => 0];
try {
    $GLOBALS['_badge_lotes'] = \App\Controllers\LotesEgresosController::contadoresBadge();
} catch (\Throwable $e) {
    // noop
}
?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <?php require BASE_PATH . '/partial/topbar_local.php'; ?>
  <div class="page-body-wrapper">
    <?php require BASE_PATH . '/partial/sidebar_local.php'; ?>
    <div class="page-body">
      <?php require BASE_PATH . '/partial/breadcrumb_local.php'; ?>
      <div class="container-fluid">
