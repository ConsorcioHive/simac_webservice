<?php
$miLogoPorDefecto = BASE_URL . "public/uploads/empresas/1164/logo.png"; 
$logoLight = $miLogoPorDefecto;
$logoDark  = $miLogoPorDefecto;

if (isset($empresaActiva) && !empty($empresaActiva['logo_url'])) {
    $path = $empresaActiva['logo_url'];
    if (preg_match('~^https?://~i', $path)) {
        $finalPath = $path;
    } else {
        $cleanPath = ltrim($path, '/');
        $finalPath = (strpos($cleanPath, 'public/') === 0 ? BASE_URL : BASE_URL . "public/") . $cleanPath;
    }
    $logoLight = $finalPath;
    $logoDark  = $finalPath;
}

$rol = $_SESSION['usuario_rol'] ?? '';
$tipoUsuario = (int)($_SESSION['tipo_usuario'] ?? 0);
$esPaciente = ($tipoUsuario === 5);
$esMedico = ($rol === 'medico');
$esAsistente = ($rol === 'asistente');
$esAdmin = ($rol === 'admin' || $_SESSION['usuario_id'] == 1);
$esPromotor = ($rol === 'promotor');
$esSuperadmin = ($_SESSION['usuario_id'] == 1);
$esGerenteOficina = ($rol === 'admin' && ($_SESSION['active_profile_name'] ?? '') === 'Gerente de Oficina');

// Verificar si el usuario tiene cajas disponibles
$tieneCajas = false;
if (in_array($rol, ['admin', 'medico', 'asistente'])) {
    $cajaModel = new \App\Models\Caja();
    $tieneCajas = $cajaModel->tieneCajas($_SESSION['usuario_id'], $_SESSION['company_id_activa'] ?? 0, $rol);
}

// Helper: SMI (SIMAC Medical Icon) inline SVG for small icons
function smi($name) {
    $icons = [
        'clinica' => '<svg class="stroke-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z"/><path d="M12 7v10M7 12h10"/></svg>',
        'paciente' => '<svg class="stroke-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>',
        'agenda' => '<svg class="stroke-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'notas' => '<svg class="stroke-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    ];
    return $icons[$name] ?? '<svg class="stroke-icon"><use href="' . BASE_URL . 'assets/svg/icon-sprite.svg#stroke-' . $name . '"></use></svg>';
}
?>
<div class="sidebar-wrapper" sidebar-layout="default-sidebar">
  <div>
<?php $homePage = $esMedico ? 'medico_dashboard' : 'dashboard'; ?>
    <div class="logo-wrapper">
      <a href="index.php?page=<?= $homePage ?>">
        <img id="sidebarLogoLight" class="img-fluid for-light" src="<?= htmlspecialchars($logoLight, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
        <img id="sidebarLogoDark" class="img-fluid for-dark"  src="<?= htmlspecialchars($logoDark, ENT_QUOTES, 'UTF-8') ?>"  alt="Logo">
      </a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar">
        <i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i>
      </div>
    </div>

    <div class="logo-icon-wrapper">
        <a href="index.php?page=<?= $homePage ?>">
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

          <!-- ===================== PACIENTE ===================== -->
          <?php if ($esPaciente): ?>
              <li class="pin-title sidebar-main-title">
                <div><h6>Fijados</h6></div>
              </li>
              <li class="sidebar-main-title">
                <div><h6>Mi Portal de Salud</h6></div>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=dashboard">
                  <?= smi('home') ?><span>Dashboard</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=caja_dashboard">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Dashboard</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=citas">
                  <?= smi('calendar') ?><span>Mis Citas</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=mis_pagos">
                  <?= smi('credit-card') ?><span>Mis Pagos</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=paciente_consultas">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-board"></use></svg>
                  <span>Mis Consultas</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=paciente_historial">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-board"></use></svg>
                  <span>Historial Clínico</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=usuarios_editar&id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>">
                  <?= smi('user') ?><span>Mi Cuenta</span>
                </a>
              </li>

          <!-- ===================== MÉDICO ===================== -->
          <?php elseif ($esMedico): ?>
              <li class="pin-title sidebar-main-title">
                <div><h6>Fijados</h6></div>
              </li>
              <li class="sidebar-main-title">
                <div><h6>Portal del Médico</h6></div>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=medico_dashboard">
                  <i class="fa fa-tachometer" style="width: 18px;"></i><span>Dashboard Médico</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('paciente') ?><span>Pacientes</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=pacientes_lista">Lista de Pacientes</a></li>
                  <li><a href="index.php?page=paciente_nuevo">Nuevo Paciente</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('agenda') ?><span>Agenda de Citas</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=citas_calendario">Calendario de Citas</a></li>
                  <li><a href="index.php?page=citas_agenda">Solicitudes Pendientes</a></li>
                  <li><a href="index.php?page=mis_horarios">Disponibilidad / Horarios</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('notas') ?><span>Consultas</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=consultas">Consultas de pacientes</a></li>
                  <li><a href="index.php?page=consultas_plantillas">Plantillas</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-check-square-o" style="width:18px;"></i><span>Consentimientos</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=plantillas_consentimiento">Plantillas</a></li>
                  <li><a href="index.php?page=catalogo_variables">Variables</a></li>
                  <li><a href="index.php?page=documentos_consentimiento">Documentos</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-building-o" style="width: 18px;"></i><span>Consultorios</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=medico_consultorios_lista">Gestionar</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Administración</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=caja_dashboard">Dashboard de Caja</a></li>
                  <li><a href="index.php?page=caja_medico_crear">Crear Caja</a></li>
                  <li><a href="index.php?page=caja_ingresar">Registrar Ingreso</a></li>
                  <li><a href="index.php?page=caja_historial">Historial de Movimientos</a></li>
                  <li><a href="index.php?page=caja_pendientes">Pagos Pendientes</a></li>
                  <li><a href="index.php?page=honorarios_lista">Mis Honorarios</a></li>
                  <li><a href="index.php?page=mis_cuentas_bancarias">Mis Cuentas Bancarias</a></li>
                  <li><a href="index.php?page=reporte_consolidado">Consolidado por Período</a></li>
                  <li><a href="index.php?page=reporte_historial_cierres">Historial de Cierres</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=mensajeria">
                  <?= smi('chat') ?><span>Mensajería</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=usuarios_editar&id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>">
                  <?= smi('user') ?><span>Mi Cuenta</span>
                </a>
              </li>

          <!-- ===================== ASISTENTE ===================== -->
          <?php elseif ($esAsistente): ?>
              <li class="pin-title sidebar-main-title">
                <div><h6>Fijados</h6></div>
              </li>
              <li class="sidebar-main-title">
                <div><h6>Panel de Asistente</h6></div>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=dashboard">
                  <?= smi('home') ?><span>Dashboard</span>
                </a>
              </li>
              <?php if ($tieneCajas): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=caja_dashboard">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Manejo de Caja</span>
                </a>
              </li>
              <?php endif; ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('agenda') ?><span>Agenda de Citas</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=citas_calendario">Calendario de Citas</a></li>
                  <li><a href="index.php?page=citas_agenda">Solicitudes Pendientes</a></li>
                  <li><a href="index.php?page=mis_horarios">Disponibilidad / Horarios</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('paciente') ?><span>Pacientes</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=pacientes_lista">Lista de Pacientes</a></li>
                  <li><a href="index.php?page=paciente_nuevo">Nuevo Paciente</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('notas') ?><span>Notas Médicas</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=consultas">Consultas de pacientes</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Facturación</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=caja_ingresar">Registrar Ingreso</a></li>
                  <li><a href="index.php?page=caja_historial">Historial de Movimientos</a></li>
                  <li><a href="index.php?page=caja_pendientes">Pagos Pendientes</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=mensajeria">
                  <?= smi('chat') ?><span>Mensajería</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=usuarios_editar&id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>">
                  <?= smi('user') ?><span>Mi Cuenta</span>
                </a>
              </li>

          <!-- ===================== PROMOTOR ===================== -->
          <?php elseif ($esPromotor): ?>
              <li class="pin-title sidebar-main-title">
                <div><h6>Fijados</h6></div>
              </li>
              <li class="sidebar-main-title">
                <div><h6>Mi Panel Promotor</h6></div>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=dashboard">
                  <?= smi('home') ?><span>Dashboard</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=inmueble_maestro_lista">
                  <?= smi('project') ?><span>Consultorios</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=empresas">
                  <?= smi('project') ?><span>Empresas</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=mensajeria">
                  <?= smi('chat') ?><span>Mensajería</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=usuario_editar&id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>">
                  <?= smi('user') ?><span>Mi Perfil</span>
                </a>
              </li>

          <!-- ===================== ADMIN / DEFAULT ===================== -->
          <?php else: ?>
              <li class="pin-title sidebar-main-title">
                <div><h6>Fijados</h6></div>
              </li>

              <li class="sidebar-main-title">
                <div><h6>Menú Principal</h6></div>
              </li>
              <?php if (!$esGerenteOficina): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=dashboard">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-home"></use></svg>
                  <span>Dashboard</span>
                </a>
              </li>
              <?php endif; ?>
              <?php if ($esGerenteOficina): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=gerente_dashboard">
                  <i class="fa fa-tachometer" style="width: 18px;"></i><span>Dashboard Gerencia</span>
                </a>
              </li>
              <?php endif; ?>
              <?php if (!$esGerenteOficina): ?>
              <?php if ($tieneCajas): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=caja_dashboard">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Manejo de Caja</span>
                </a>
              </li>
              <?php endif; ?>

              <!-- SIMAC Módulos Médicos -->
              <li class="sidebar-main-title">
                <div><h6>Módulo Médico SIMAC</h6></div>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <?= smi('clinica') ?><span>Clínica</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=citas_calendario">Calendario de Citas</a></li>
                  <li><a href="index.php?page=citas">Gestión de Citas</a></li>
                  <li><a href="index.php?page=inmueble_maestro_lista">Consultorios</a></li>
                </ul>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-credit-card" style="width: 18px;"></i><span>Caja / Finanzas</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=caja_admin_lista">Gestión de Cajas</a></li>
                  <li><a href="index.php?page=cuentas_bancarias_lista">Cuentas Bancarias</a></li>
                  <li><a href="index.php?page=caja_ingresar">Registrar Ingreso</a></li>
                  <li><a href="index.php?page=caja_historial">Historial de Movimientos</a></li>
                  <li><a href="index.php?page=caja_pendientes">Pagos Pendientes</a></li>
                  <li><a href="index.php?page=honorarios_lista">Honorarios Médicos</a></li>
                </ul>
              </li>

              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <i class="fa fa-bar-chart" style="width: 18px;"></i><span>Reportes</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=reporte_consolidado">Consolidado por Período</a></li>
                  <li><a href="index.php?page=reporte_por_medico">Reporte por Médico</a></li>
                  <li><a href="index.php?page=reporte_historial_cierres">Historial de Cierres</a></li>
                  <li><a href="index.php?page=reporte_resumen_general">Resumen General</a></li>
                </ul>
              </li>
              <?php endif; ?>

              <!-- Módulos Simac (existing) -->
              <li class="sidebar-main-title">
                <div><h6>Módulos Simac</h6></div>
              </li>

              <?php if (hasPermission('usuarios', 'view') && !$esGerenteOficina): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-user"></use></svg>
                  <span>Usuarios</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=usuarios_lista">Lista de usuarios</a></li>
                  <?php if ($esAdmin || $esSuperadmin): ?>
                    <li><a href="index.php?page=usuarios_crear">Nuevo usuario</a></li>
                  <?php endif; ?>
                </ul>
              </li>
              <?php endif; ?>

              <?php if (($_SESSION['usuario_id'] ?? 0) === 1): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                  <span>Proveedores</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=proveedores_lista">Proveedores</a></li>
                </ul>
              </li>
              <?php endif; ?>

              <?php
              $esAgenteEmpresa = (($_SESSION['entidad_tipo'] ?? '') === 'empresas');
              $esDorata = false;
              if (isset($_SESSION['company_id_activa'])) {
                  if ($_SESSION['company_id_activa'] == 5) {
                      $esDorata = true;
                  } else {
                      try {
                          $pdo = \App\Models\Database::getConnection();
                          $stmtCorp = $pdo->prepare("SELECT parent_id FROM companies WHERE id = ?");
                          $stmtCorp->execute([$_SESSION['company_id_activa']]);
                          $compParent = $stmtCorp->fetchColumn();
                          if ($compParent == 5) { $esDorata = true; }
                      } catch (\Exception $e) {}
                  }
              }

              if (hasPermission('clientes', 'view') && ($esSuperadmin || !$esAgenteEmpresa)): 
              ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title" href="#">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                  <span>Hospitales Públicos</span>
                </a>
                <ul class="sidebar-submenu">
                  <li><a href="index.php?page=clientes_lista">Lista de Hospitales</a></li>
                </ul>
              </li>
              <?php endif; ?>

              <?php if (hasPermission('inmuebles', 'view') && !$esGerenteOficina): ?>
              <li class="sidebar-list">
                    <i class="fa fa-thumb-tack"></i>
                    <a class="sidebar-link sidebar-title" href="#">
                        <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                        <span>Consultorios</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <?php if ($esSuperadmin): ?>
                            <li><a href="index.php?page=edomunpar_lista">Distribución Territorial</a></li>
                            <li><a href="index.php?page=inmuebles_atributos_lista">Tablas de Control</a></li>
                        <?php endif; ?>
                        <li><a href="index.php?page=inmueble_maestro_lista">Maestro Consultorios</a></li>
                    </ul>
                </li>
              <?php endif; ?>

              <?php if (hasPermission('contratos', 'view') && !$esGerenteOficina): ?>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title" href="#">
                      <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-file"></use></svg>
                      <span>Mis Contratos</span>
                  </a>
                  <ul class="sidebar-submenu">
                      <?php if ($esSuperadmin): ?>
                          <li><a href="index.php?page=admin_contratos_global">Gestión de Contratos</a></li>
                          <li><a href="index.php?page=admin_contratos_tipos">Configurar Planes</a></li>
                      <?php endif; ?>
                      <li><a href="index.php?page=contratos_lista">Mis Contratos</a></li>
                      <li><a href="index.php?page=contratos_planes">Planes Disponibles</a></li>
                  </ul>
              </li>
              <?php endif; ?>

              <?php if ($esGerenteOficina): ?>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=mensajeria">
                  <?= smi('chat') ?><span>Mensajería</span>
                </a>
              </li>
              <li class="sidebar-list">
                <i class="fa fa-thumb-tack"></i>
                <a class="sidebar-link sidebar-title link-nav" href="index.php?page=empresas_form&id=<?= (int)($_SESSION['company_id_activa'] ?? 0) ?>">
                  <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                  <span>Editar Empresa</span>
                </a>
              </li>
              <?php endif; ?>
              <?php if (hasPermission('empresas', 'view') && !$esGerenteOficina): ?>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title" href="#">
                    <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                    <span>Empresas</span>
                  </a>
                  <ul class="sidebar-submenu">
                      <?php if ($esSuperadmin): ?>
                      <li><a href="index.php?page=corporaciones_lista" style="font-weight: bold; color: #1e3a5f;">Corporaciones / Holdings</a></li>
                      <?php endif; ?>
                      <li><a href="index.php?page=empresas_lista">Empresas</a></li>
                      <li><a href="index.php?page=mensajeria">Mensajería</a></li>
                      <?php if ($esSuperadmin): ?>
                      <li><a href="index.php?page=mensajeria_tickets">Soporte y Tickets</a></li>
                      <?php endif; ?>
                      <?php if (hasPermission('tasas_cambio', 'view')): ?>
                      <li><a href="index.php?page=tasas_cambio_lista">Tasas de cambio</a></li>
                      <?php endif; ?>
                      <?php if (hasPermission('cuentas_bancarias', 'view')): ?>
                      <li><a href="index.php?page=cuentas_bancarias_lista">Cuentas bancarias</a></li>
                      <?php endif; ?>
                      <?php if ($esSuperadmin || in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'medico'])): ?>
                      <li><a href="index.php?page=perfiles_lista">Perfiles y accesos</a></li>
                       <?php endif; ?>
                  </ul>
              </li>
              <?php endif; ?>

              <?php if ($esSuperadmin): ?>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="index.php?page=publicidad_lista">
                      <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-bookmark"></use></svg>
                      <span>Publicidad</span>
                  </a>
              </li>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="index.php?page=admin_plantillas_sistema">
                      <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-project"></use></svg>
                      <span>Plantillas del Sistema</span>
                  </a>
              </li>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title" href="#">
                      <i class="fa fa-check-square-o" style="width:18px;"></i><span>Consentimientos</span>
                  </a>
                  <ul class="sidebar-submenu">
                      <li><a href="index.php?page=catalogo_variables">Catálogo de Variables</a></li>
                      <li><a href="index.php?page=plantillas_consentimiento">Plantillas Públicas</a></li>
                  </ul>
              </li>
              <?php endif; ?>

              <?php if (($esAdmin || $esSuperadmin) && !$esGerenteOficina): ?>
              <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title" href="#">
                      <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-board"></use></svg>
                      <span>Blog</span>
                  </a>
                  <ul class="sidebar-submenu">
                      <li><a href="index.php?page=blog_admin_lista">Administrar Blog</a></li>
                      <li><a href="index.php?page=blog_admin_form">Nuevo Post</a></li>
                  </ul>
              </li>
              <?php endif; ?>

          <?php endif; ?>
          
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
