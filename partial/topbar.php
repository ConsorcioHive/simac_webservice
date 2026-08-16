<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. Detectar si el usuario cambió la empresa (Auto-procesamiento)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entidad_selector'])) {
    $raw = $_POST['entidad_selector']; // ej: "empresas:10" o "clientes:5"

    [$tipo, $id] = explode(':', $raw, 2);
    $id = (int)$id;

    // Guardar id y tipo en sesión
    $_SESSION['company_id_activa'] = $id;
    $_SESSION['entidad_tipo']      = $tipo; // 'empresas' o 'clientes'

    // === NUEVO: recalcular company_code según el tipo de entidad ===
    $companyCode = null;

	if ($tipo === 'empresas') {
		$empresaModel = new \App\Models\Empresa();
		$empresa      = $empresaModel->obtenerPorId($id);
		if ($empresa && isset($empresa['company_code'])) {
			$companyCode = (string)$empresa['company_code'];   // SIN (int)
		}
	} elseif ($tipo === 'clientes') {
		$clienteModel = new \App\Models\Cliente();
		$cliente      = $clienteModel->obtenerPorId($id);
		if ($cliente && isset($cliente['company_code'])) {
			$companyCode = (string)$cliente['company_code'];   // SIN (int)
		}
	} elseif ($tipo === 'proveedores') {
		$proveedorModel = new \App\Models\Proveedor();
		$proveedor      = $proveedorModel->obtenerPorId($id);
		if ($proveedor && isset($proveedor['rif'])) {
			$companyCode = (string)$proveedor['rif']; // O el código que uses para proveedores
		}
	}

    $_SESSION['company_code'] = $companyCode;

    // --- CARGAR PERMISOS DEL PERFIL ---
    $profileModel = new \App\Models\Profile();
    $profileModel->cargarPermisosEnSesion((int)$_SESSION['usuario_id'], $tipo, $id);

    // Recargar para limpiar el POST
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$toast_success = $_SESSION['flash_success'] ?? null;
$toast_danger  = $_SESSION['flash_danger']  ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_danger']);
// traigo los datos del usuario

$empresaActiva = $empresaActiva ?? null;

if (empty($empresaActiva) && !empty($_SESSION['company_id_activa']) && !empty($_SESSION['entidad_tipo'])) {
    $entidadId   = (int)$_SESSION['company_id_activa'];
    $entidadTipo = $_SESSION['entidad_tipo']; // 'empresas' o 'clientes'

    if ($entidadTipo === 'empresas') {
        $empresaModel  = new \App\Models\Empresa();
        $empresaActiva = $empresaModel->obtenerPorId($entidadId);
    } elseif ($entidadTipo === 'clientes') {
        $customerModel = new \App\Models\Cliente();
        $empresaActiva = $customerModel->obtenerPorId($entidadId);
    } elseif ($entidadTipo === 'proveedores') {
        $proveedorModel = new \App\Models\Proveedor();
        $empresaActiva  = $proveedorModel->obtenerPorId($entidadId);
    }

    // Si no tenemos los permisos cargados en la sesión, los cargamos ahora (forzamos para el Superadmin ID 1 en pruebas)
    if (!isset($_SESSION['user_permissions']) || empty($_SESSION['user_permissions']) || (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1)) {
        $profileModel = new \App\Models\Profile();
        $profileModel->cargarPermisosEnSesion((int)$_SESSION['usuario_id'], $entidadTipo, $entidadId);
    }
}



?>

<?php if ($toast_success || $toast_danger): ?>
<div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="rmryToast" class="toast align-items-center text-white bg-<?= $toast_success ? 'success' : 'danger' ?> border-0"
	   role="alert" aria-live="assertive" aria-atomic="true"
	   data-bs-autohide="true" data-bs-delay="3000">
	<div class="d-flex">
	  <div class="toast-body">
		<?= htmlspecialchars($toast_success ?: $toast_danger) ?>
	  </div>
	  <button type="button" class="btn-close btn-close-white me-2 m-auto"
			  data-bs-dismiss="toast" aria-label="Close"></button>
	</div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
	var toastEl = document.getElementById('rmryToast');
	if (toastEl) {
	  var toast = new bootstrap.Toast(toastEl);
	  toast.show();
	}
  });
</script>
<?php endif; ?>
<div class="page-header" style="z-index: 1040 !important;">
    <div class="header-wrapper row m-0">
        <form class="form-inline search-full col" action="#" method="get" onsubmit="return false;">
            <div class="form-group w-100">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative">
                        <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Buscar páginas, módulos o tareas... (ej: inmuebles, contratos)" name="q" id="sitemap-search-input" title="" autofocus autocomplete="off">
                        <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Cargando...</span></div><i class="close-search" data-feather="x"></i>
                    </div>
                    <div class="Typeahead-menu" id="sitemap-search-results"></div>
                </div>
            </div>
        </form>
        
        
        <?php
		// DEBUG empresa activa
		echo '<!-- empresaActiva-id: ' . ($empresaActiva['id'] ?? 'null') . ' -->';
		echo '<!-- empresaActiva-logo_url: ' . ($empresaActiva['logo_url'] ?? 'null') . ' -->';
		?>

        
        
		<?php
        $defaultLogo = BASE_URL . 'assets/images/logo/simac-logo.png';
        $logoUrl = $defaultLogo;
        
        if (!empty($empresaActiva) && !empty($empresaActiva['logo_url'])) {
            $path = $empresaActiva['logo_url'];
        
            // Si es URL absoluta, úsala tal cual
            if (preg_match('~^https?://~i', $path)) {
                $logoUrl = $path;
            } else {
                // Ruta relativa: quitar /inicial para evitar doble slash y anteponer BASE_URL
                $logoUrl = BASE_URL . ltrim($path, '/');
            }
        }
        ?>



        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper">
                <a href="index.php?page=<?= ($_SESSION['usuario_rol'] ?? '') === 'medico' ? 'medico_dashboard' : 'dashboard' ?>">
                    <img class="img-fluid" src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo empresa">
                </a>
            </div>
            <!-- Botón de 4 cuadros (Solo visible cuando se comprime vía CSS) -->
            <div class="toggle-sidebar toggle-sidebar-topbar ms-3">
                <i class="status_toggle middle sidebar-toggle" data-feather="grid"></i>
            </div>
            <!-- Botón original para móvil -->
            <div class="toggle-sidebar d-xl-none">
                <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
            </div>
        </div>

        
        <div class="left-header col-auto p-0">

		<?php       
        $usuarioId         = $_SESSION['usuario_id']        ?? null;
		$usuarioNombre     = $_SESSION['usuario_nombre']    ?? 'Usuario';
		$usuarioRol        = $_SESSION['usuario_rol']       ?? 'asistente';
		
		$entidadIdActiva   = $_SESSION['company_id_activa'] ?? null;
		$entidadTipoActiva = $_SESSION['entidad_tipo']      ?? null;
		
		$entidadesUsuario  = [];
		
		// Obtener foto del usuario (opcional)
		$usuarioFotoUrl = BASE_URL . 'assets/images/dashboard/profile.png';
		
		if ($usuarioId) {
			$usuarioModel = new \App\Models\Usuario();
			$datosUsuario = $usuarioModel->obtenerPorId((int)$usuarioId);
			if (!empty($datosUsuario['foto'])) {
				$cleanFoto = trim($datosUsuario['foto']);
				// Si la foto ya es una URL completa, no anteponer BASE_URL
				if (stripos($cleanFoto, 'http://') === 0 || stripos($cleanFoto, 'https://') === 0) {
					$usuarioFotoUrl = $cleanFoto;
				} else {
					$usuarioFotoUrl = BASE_URL . ltrim($cleanFoto, '/');
				}
			}
		
			// Cargar TODAS las entidades (companies + customers) relacionadas al usuario
			$entidadesUsuario = $usuarioModel->obtenerEntidadesPorUsuario((int)$usuarioId);
		
			// Superadmin (ID 1): cargar TODAS las empresas si no tiene relaciones asignadas
			if (empty($entidadesUsuario) && (int)$usuarioId === 1) {
				$todasLasCompanies = $usuarioModel->obtenerTodasLasCompanies();
				foreach ($todasLasCompanies as $c) {
					$entidadesUsuario[] = [
						'entidad_tipo'      => 'empresas',
						'tipo_relacion'     => 'admin',
						'rol_staff'         => null,
						'es_predeterminada' => ((int)$c['id'] === 1164) ? 1 : 0,
						'entidad_id'        => (int)$c['id'],
						'entidad_code'      => $c['company_code'],
						'entidad_nombre'    => $c['nombre'],
					];
				}
			}
		
			// Si NO es admin y NO tiene entidades → mensaje + logout
			if ($usuarioRol !== 'admin' && empty($entidadesUsuario)) {
				$_SESSION['flash_danger'] = 'Su usuario no tiene ninguna entidad asignada. Contacte al administrador.';
				header('Location: index.php?page=logout');
				exit;
			}
		
			// Si tiene entidades y no hay activa en sesión, elegir predeterminada o primera
			if (!empty($entidadesUsuario) && ($entidadIdActiva === null || $entidadTipoActiva === null)) {
				$seleccion = null;
		
				foreach ($entidadesUsuario as $e) {
					if (!empty($e['es_predeterminada']) && (int)$e['es_predeterminada'] === 1) {
						$seleccion = $e;
						break;
					}
				}
		
				if ($seleccion === null) {
					$seleccion = $entidadesUsuario[0];
				}
		
				$_SESSION['company_id_activa'] = (int)$seleccion['entidad_id'];
				$_SESSION['entidad_tipo']      = $seleccion['entidad_tipo'];
		
				$entidadIdActiva   = $_SESSION['company_id_activa'];
				$entidadTipoActiva = $_SESSION['entidad_tipo'];
			}
		}
		
		// 3. DATOS DE CONTRATO PARA EL TOPBAR
		$contratoVigente = null;
		$historialContratos = [];
		$ultimosContratos = [];
		if ($usuarioId && $entidadIdActiva && $entidadTipoActiva) {
			$modelContrato = new \App\Models\Contratos();
			$contratoVigente = $modelContrato->obtenerVigentePorEntidad((int)$usuarioId, (int)$entidadIdActiva, $entidadTipoActiva);
			
			// Obtener historial (últimos 3)
			$historialContratos = $modelContrato->listarHistorialPorEntidad((int)$usuarioId, (int)$entidadIdActiva, $entidadTipoActiva);
			// Filtrar el vigente del historial si es necesario, o solo tomar los 3 primeros
			$ultimosContratos = array_slice($historialContratos, 0, 3);
		}

		// Traducir rol a etiqueta amigable
		switch ($usuarioRol) {
			case 'admin':         $usuarioRolLabel = 'Administrador'; break;
			case 'representante': $usuarioRolLabel = 'Representante'; break;
			case 'asistente':     $usuarioRolLabel = 'Asistente'; break;
			default:              $usuarioRolLabel = ucfirst($usuarioRol);
		}

        ?>
		<span class="d-inline-block ms-3">
			<?php if (!empty($entidadesUsuario) && !empty($_SESSION['user_preferences']['mostrar_empresa_topbar'])): ?>
                <select id="selEntidadTopbar" class="form-select form-select-sm">
                    <?php foreach ($entidadesUsuario as $e): 
						$value    = $e['entidad_tipo'] . ':' . (int)$e['entidad_id'];
						$selected = (
							$entidadTipoActiva === $e['entidad_tipo'] 
							&& (int)$entidadIdActiva === (int)$e['entidad_id']
						);
						if ($e['entidad_tipo'] === 'empresas') {
							$prefijo = 'EMP';
						} elseif ($e['entidad_tipo'] === 'clientes') {
							$prefijo = 'CLI';
						} else {
							$prefijo = 'PRO';
						}
					?>
						<option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
							[<?= $prefijo ?>] <?= htmlspecialchars($e['entidad_code']) ?> - <?= htmlspecialchars($e['entidad_nombre']) ?>
						</option>
					<?php endforeach; ?>
                </select>
            <?php endif; ?>
        </span>

         </div>

        <!--<div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <div class="notification-slider">
                <div class="d-flex h-100"> <img src="<?= BASE_URL ?>assets/images/giftools.gif" alt="gif">
                    <h6 class="mb-0 f-w-400"><span class="font-primary">Don't Miss Out! </span><span class="f-light">Out new update has been release.</span></h6><i class="icon-arrow-top-right f-light"></i>
                </div>
                <div class="d-flex h-100"><img src="<?= BASE_URL ?>assets/images/giftools.gif" alt="gif">
                    <h6 class="mb-0 f-w-400"><span class="f-light">Something you love is now on sale! </span></h6><a class="ms-1" href="https://1.envato.market/3GVzd" target="_blank">Buy now !</a>
                </div>
            </div>
        </div>-->
        <div class="nav-right col-auto pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
                <li class="onhover-dropdown">
                    <div class="translate_wrapper">
                        <div class="current_lang">
                            <div class="lang" style="white-space: nowrap; display: flex; align-items: center;">
                                <i class="fa fa-file-text-o me-2"></i>
                                <span class="lang-txt"><?= $contratoVigente ? 'CONTRATO #' . $contratoVigente['id_contrato'] : 'S/C' ?></span>
                            </div>
                        </div>
                        <div class="more_lang onhover-show-div" style="width: 280px; padding: 15px; border-radius: 8px; top: 50px !important; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                            <div class="mb-3 border-bottom pb-2">
                                <h6 class="m-0"><i class="fa fa-briefcase me-2"></i>Mi Suscripción</h6>
                            </div>
                            
                            <?php if ($contratoVigente): ?>
                                <div class="mb-3 p-3 bg-light-success border-start border-success border-3 rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="m-0 text-success" style="font-weight: 700;">Plan <?= htmlspecialchars($contratoVigente['plan_nombre']) ?></h6>
                                        <span style="background: #51bb25; color: #fff; padding: 3px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; line-height: 1;">ACTIVO</span>
                                    </div>
                                    <div class="small text-muted">
                                        Vence: <strong><?= date('d/m/Y', strtotime($contratoVigente['fecha_vencimiento'])) ?></strong>
                                    </div>
                                    <a href="index.php?page=contratos_lista" class="btn btn-primary btn-xs mt-3 w-100 shadow-sm" style="font-weight: 600;">Ver Detalles</a>
                                </div>
                            <?php else: ?>
                                <div class="mb-3 p-2 bg-light-warning border-start border-warning border-3 rounded text-center">
                                    <p class="small mb-2">No tienes un plan activo en esta entidad.</p>
                                    <a href="index.php?page=contratos_planes" class="btn btn-warning btn-xs w-100">Adquirir Plan</a>
                                </div>
                            <?php endif; ?>

                            <div class="mt-2">
                                <p class="small text-bold mb-1"><i class="fa fa-history me-1"></i> Historial Reciente</p>
                                <ul class="list-unstyled">
                                    <?php if (!empty($ultimosContratos)): ?>
                                        <?php foreach ($ultimosContratos as $c): ?>
                                            <li class="border-bottom py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small">#<?= $c['id'] ?> (<?= date('d/m/Y', strtotime($c['created_at'])) ?>)</span>
                                                    <span style="color: <?= $c['status'] == 'activo' ? '#51bb25' : '#888' ?>; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                                                        <?= strtoupper($c['status']) ?>
                                                    </span>
                                                </div>
                                                <div class="x-small text-muted" style="font-size: 10px;">
                                                    Vence: <?= $c['fecha_fin'] ? date('d/m/Y', strtotime($c['fecha_fin'])) : 'N/A' ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="text-center text-muted py-2 small">No hay historial disponible</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <div class="text-center mt-2">
                                <a href="index.php?page=contratos_lista" class="small text-primary">Gestionar Contratos <i class="fa fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </li>
                <li> <span class="header-search">
                        <svg>
                            <use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#search"></use>
                        </svg></span></li>
                <li class="onhover-dropdown" id="dropdownActividadMercado">
                    <div class="notification-box">
                        <svg>
                            <use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#star"></use>
                        </svg>
                        <span class="badge rounded-pill badge-danger" id="badge_actividad_top" style="display:none;">!</span>
                    </div>
                    <div class="onhover-show-div bookmark-flip" style="width: 320px; z-index: 9999 !important;">
                        <div class="flip-card">
                            <div class="flip-card-inner">
                                <div class="front">
                                    <h6 class="f-18 mb-0 dropdown-title text-start" style="background-color: var(--azul-inmo); color: white; padding: 15px;">
                                        <i class="fa fa-line-chart me-2"></i>Actividad del Mercado
                                    </h6>
                                    <ul class="bookmark-dropdown p-3" id="contenidoActividadMercado" style="max-height: 300px; overflow-y: auto; text-align: left;">
                                        <li class="text-center py-3">
                                            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                            <span class="ms-2 small text-muted">Cargando actividad...</span>
                                        </li>
                                    </ul>
                                    <div class="text-center p-2 border-top bg-light">
                                        <a href="index.php?page=web_actividad_mercado" class="btn btn-sm btn-success w-100 fw-bold rounded-pill shadow-sm">
                                            Ver Todo Detallado
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="mode">
                        <svg>
                            <use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#moon"></use>
                        </svg>
                    </div>
                </li>
                <li id="li-favoritos-top">
                    <a href="index.php?page=inmueble_maestro_lista&ver_favoritos=1" title="Mis Favoritos" class="notification-box">
                        <i class="fa fa-heart-o f-18"></i>
                        <span class="badge rounded-pill badge-warning" id="badge_favoritos_top">0</span>
                    </a>
                </li>
                <li id="li-comparativo-top">
                    <a href="index.php?page=web_comparativo" title="Comparador de Inmuebles" class="notification-box">
                        <i class="fa fa-balance-scale f-18"></i>
                        <span class="badge rounded-pill badge-primary" id="badge_comparativo_top">0</span>
                    </a>
                </li>
                <li class="cart-nav onhover-dropdown" id="li-favoritos-dropdown-top">
                    <div class="notification-box">
                        <a href="index.php?page=web_favoritos" title="Mi Selección de Inmuebles">
                            <i class="fa fa-list-alt f-18"></i>
                        </a>
                        <span class="badge rounded-pill badge-primary" id="badge_favoritos_dropdown">0</span>
                    </div>
                    <div class="cart-dropdown onhover-show-div" style="width: 320px; z-index: 9999 !important;">
                        <h6 class="f-18 mb-0 dropdown-title">Mi Selección de Inmuebles</h6>
                        <ul id="lista_favoritos_dropdown">
                            <!-- Los favoritos se cargarán aquí por AJAX -->
                            <li class="text-center p-3 text-muted">Cargando selección...</li>
                        </ul>
                    </div>
                </li>
                <?php
                    $unreadMessagesCount = 0;
                    if (!empty($usuarioId)) {
                        $mensajeModel = new \App\Models\Mensaje();
                        $unreadMessagesCount = $mensajeModel->contarNoLeidos((int)$usuarioId);
                    }
                ?>
                <li class="onhover-dropdown" title="Mensajería Interna">
                    <a href="index.php?page=mensajeria" style="text-decoration: none; color: inherit;">
                        <div class="notification-box">
                            <svg>
                                <use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#notification"></use>
                            </svg>
                            <?php if ($unreadMessagesCount > 0): ?>
                            <span class="badge rounded-pill badge-danger"><?= $unreadMessagesCount ?></span>
                            <?php else: ?>
                            <span class="badge rounded-pill badge-secondary" style="display:none;">0</span>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
                
                <li class="onhover-dropdown" title="Mi To-Do List">
                    <div class="notification-box">
                        <i class="fa fa-check-square-o f-18"></i>
                        <span class="badge rounded-pill badge-info" id="todo_count_badge" style="display:none;">0</span>
                    </div>
                    <div class="onhover-show-div todo-dropdown" style="width: 320px; padding: 20px; z-index: 9999 !important;">
                        <div class="mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="fa fa-list-ul me-2"></i>Mis Actividades</h6>
                            <div class="text-end">
                                <span class="badge badge-light-primary" id="todo_total_label">0/50</span>
                                <div class="small text-success font-weight-bold" id="todo_percentage">0%</div>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 5px;">
                            <div id="todo_progress_bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div id="todo_list_container" style="max-height: 250px; overflow-y: auto; margin-bottom: 15px; padding-right: 5px;">
                            <div class="text-center p-4 text-muted">
                                <i class="fa fa-spinner fa-spin fa-2x mb-2"></i><br>Cargando...
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="mb-2">
                                <input type="text" id="todo_new_project" class="form-control form-control-sm mb-1" placeholder="Proyecto...">
                                <textarea id="todo_new_task" class="form-control form-control-sm mb-2" rows="2" placeholder="Actividad..."></textarea>
                                <button class="btn btn-primary btn-sm w-100 mb-2" type="button" id="btn_add_todo">
                                    <i class="fa fa-plus me-1"></i> Agregar
                                </button>
                                <a href="index.php?page=todo_gestion" class="btn btn-outline-secondary btn-xs w-100">
                                    <i class="fa fa-external-link me-1"></i> Gestionar Todo
                                </a>
                            </div>
                            <small class="text-muted text-center d-block" style="font-size: 10px;">Límite: 50 tareas por usuario</small>
                        </div>
                    </div>
                </li>

                <li class="profile-nav onhover-dropdown pe-0 py-0">
                  <div class="media profile-media">
                    <img class="rounded-circle" src="<?= $usuarioFotoUrl ?>" alt="usuario" width="35" height="35" style="object-fit: cover;">
                    <div class="media-body">
                      <span><?= htmlspecialchars($usuarioNombre) ?></span>
                      <p class="mb-0 font-roboto">
                        <?= htmlspecialchars($usuarioRolLabel) ?>
                        <i class="middle fa fa-angle-down"></i>
                      </p>
                    </div>
                  </div>
                  <ul class="profile-dropdown onhover-show-div">
                    <li>
                    	<a href="index.php?page=usuarios_editar&id=<?= $_SESSION['usuario_id'] ?? '' ?>">
                    	<i data-feather="user"></i><span>Mi cuenta</span></a>
                    </li>
                      <li>
                        <a href="index.php?page=preferencias_usuario">
                          <i data-feather="settings"></i><span>Preferenc</span>
                        </a>
                      </li>
                    <li>
                    	<a href="index.php?page=logout"><i data-feather="log-out"></i><span>Cerrar</span></a>
                    </li>
                  </ul>
                </li>

            </ul>

            <!-- Botón toggle menú móvil (fuera del ul para que sea visible al ocultar nav-menus) -->
            <div class="mobile-nav-toggle d-lg-none text-end px-2">
              <button type="button" class="btn btn-link text-reset p-0 border-0" onclick="toggleMobileMenu()" aria-label="Menú">
                <i class="fa fa-ellipsis-v f-20"></i>
              </button>
            </div>

            <!-- Panel vertical móvil (oculto por defecto) -->
            <div class="mobile-nav-panel d-lg-none" id="mobileNavPanel">
              <div class="mobile-nav-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <span class="fw-bold small">Navegación</span>
                <button type="button" class="btn btn-link text-reset p-0 border-0" onclick="toggleMobileMenu()">
                  <i class="fa fa-times f-18"></i>
                </button>
              </div>
              <div class="mobile-nav-body">
                <!-- Perfil de usuario -->
                <div class="px-3 py-3 border-bottom d-flex align-items-center gap-3">
                  <img class="rounded-circle" src="<?= $usuarioFotoUrl ?>" alt="usuario" width="40" height="40" style="object-fit: cover;">
                  <div>
                    <div class="fw-bold small"><?= htmlspecialchars($usuarioNombre) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($usuarioRolLabel) ?></small>
                  </div>
                </div>
                <a href="index.php?page=usuarios_editar&id=<?= $_SESSION['usuario_id'] ?? '' ?>" class="mobile-nav-item">
                  <i class="fa fa-user me-2"></i> Mi cuenta
                </a>
                <a href="index.php?page=preferencias_usuario" class="mobile-nav-item">
                  <i class="fa fa-cog me-2"></i> Preferencias
                </a>
                <a href="index.php?page=logout" class="mobile-nav-item">
                  <i class="fa fa-sign-out me-2"></i> Cerrar sesión
                </a>
                <div class="border-top my-2"></div>
                <?php if (!empty($entidadesUsuario) && !empty($_SESSION['user_preferences']['mostrar_empresa_topbar'])): ?>
                <form method="POST" action="" id="formCambiarEntidadMovil">
                  <div class="px-3 py-2 border-bottom">
                    <label class="x-small text-muted mb-1">Entidad activa</label>
                    <select name="entidad_selector" class="form-select form-select-sm" onChange="this.form.submit()">
                      <?php foreach ($entidadesUsuario as $e):
                        $value = $e['entidad_tipo'] . ':' . (int)$e['entidad_id'];
                        $selected = ($entidadTipoActiva === $e['entidad_tipo'] && (int)$entidadIdActiva === (int)$e['entidad_id']);
                        if ($e['entidad_tipo'] === 'empresas') $prefijo = 'EMP';
                        elseif ($e['entidad_tipo'] === 'clientes') $prefijo = 'CLI';
                        else $prefijo = 'PRO';
                      ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $selected ? 'selected' : '' ?>>
                          [<?= $prefijo ?>] <?= htmlspecialchars($e['entidad_code']) ?> - <?= htmlspecialchars($e['entidad_nombre']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </form>
                <?php endif; ?>
                <a href="index.php?page=contratos_lista" class="mobile-nav-item">
                  <i class="fa fa-file-text-o me-2"></i> <?= $contratoVigente ? 'CONTRATO #' . $contratoVigente['id_contrato'] : 'S/C' ?>
                </a>
                <a href="index.php?page=web_actividad_mercado" class="mobile-nav-item">
                  <i class="fa fa-line-chart me-2"></i> Actividad de Mercado
                </a>
                <div class="mobile-nav-item" onclick="if(document.querySelector('.mode')) document.querySelector('.mode').click()">
                  <i class="fa fa-moon-o me-2"></i> Modo oscuro
                </div>
                <a href="index.php?page=inmueble_maestro_lista&ver_favoritos=1" class="mobile-nav-item">
                  <i class="fa fa-heart-o me-2"></i> Favoritos
                  <span class="badge bg-warning ms-auto" id="mobileBadgeFav">0</span>
                </a>
                <a href="index.php?page=web_comparativo" class="mobile-nav-item">
                  <i class="fa fa-balance-scale me-2"></i> Comparador
                  <span class="badge bg-primary ms-auto" id="mobileBadgeComp">0</span>
                </a>
                <a href="index.php?page=mensajeria" class="mobile-nav-item">
                  <i class="fa fa-envelope-o me-2"></i> Mensajes
                  <span class="badge bg-danger ms-auto" id="mobileBadgeMsg">0</span>
                </a>
                <a href="index.php?page=todo_gestion" class="mobile-nav-item">
                  <i class="fa fa-check-square-o me-2"></i> To-Do List
                  <span class="badge bg-info ms-auto" id="mobileBadgeTodo">0</span>
                </a>
              </div>
            </div>
            <script>
            function toggleMobileMenu() {
              var panel = document.getElementById('mobileNavPanel');
              var overlay = document.getElementById('mobileNavOverlay');
              panel.classList.toggle('open');
              overlay.style.display = panel.classList.contains('open') ? 'block' : 'none';
            }
            // Sincronizar badges
            document.addEventListener('DOMContentLoaded', function() {
              var interval = setInterval(function() {
                var bFav = document.getElementById('badge_favoritos_top');
                var bComp = document.getElementById('badge_comparativo_top');
                var bMsg = document.querySelector('.notification-box .badge.badge-danger');
                var bTodo = document.getElementById('todo_count_badge');
                if (bFav) document.getElementById('mobileBadgeFav').textContent = bFav.textContent;
                if (bComp) document.getElementById('mobileBadgeComp').textContent = bComp.textContent;
                if (bMsg) document.getElementById('mobileBadgeMsg').textContent = bMsg.textContent;
                if (bTodo) document.getElementById('mobileBadgeTodo').textContent = bTodo.textContent;
              }, 1000);
            });
            </script>
            <style>
            .mobile-nav-panel {
              position: fixed;
              top: 0; right: -300px;
              width: 280px; height: 100vh;
              background: #fff;
              box-shadow: -4px 0 20px rgba(0,0,0,0.15);
              z-index: 9999;
              transition: right 0.3s ease;
              overflow-y: auto;
            }
            .mobile-nav-panel.open { right: 0; }
            .mobile-nav-panel .mobile-nav-item {
              display: flex;
              align-items: center;
              padding: 12px 16px;
              border-bottom: 1px solid #f1f1f1;
              color: #333;
              text-decoration: none;
              font-size: 13px;
              cursor: pointer;
            }
            .mobile-nav-panel .mobile-nav-item:hover {
              background: #f8f9fa;
            }
            .mobile-nav-overlay {
              position: fixed;
              top: 0; left: 0;
              width: 100%; height: 100%;
              background: rgba(0,0,0,0.4);
              z-index: 9998;
              display: none;
            }
            .mobile-nav-toggle { list-style: none; }
            @media (max-width: 991.98px) {
              .page-header .header-wrapper .nav-right .nav-menus { display: none !important; }
              .page-header .header-wrapper .nav-right { position: relative; }
              .toggle-sidebar-topbar { display: none !important; }
              .mobile-nav-toggle { display: inline-block !important; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 5; }
            }
            </style>
        </div>
<div id="mobileNavOverlay" class="mobile-nav-overlay d-lg-none" onclick="toggleMobileMenu()"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const todoContainer = document.getElementById('todo_list_container');
    const todoBadge = document.getElementById('todo_count_badge');
    const todoTotalLabel = document.getElementById('todo_total_label');
    const todoProgressBar = document.getElementById('todo_progress_bar');
    const todoPercentageLabel = document.getElementById('todo_percentage');
    const btnAdd = document.getElementById('btn_add_todo');
    const inputProject = document.getElementById('todo_new_project');
    const inputTask = document.getElementById('todo_new_task');

    function loadTodos() {
        if (!todoContainer) return;
        fetch('index.php?page=todo_listar')
            .then(res => res.json())
            .then(data => {
                let html = '';
                let total = 0;
                let pending = 0;

                if (Object.keys(data).length === 0) {
                    html = '<div class="text-center p-3 text-muted small">No hay tareas pendientes.</div>';
                } else {
                    for (const proyecto in data) {
                        html += `<div class="mt-3 mb-1 border-bottom pb-1"><strong class="small text-primary text-uppercase" style="font-size: 10px;">${proyecto}</strong></div>`;
                        data[proyecto].forEach(t => {
                            total++;
                            const prog = parseInt(t.progreso || 0);
                            pending += (100 - prog); // Acumulamos lo pendiente en base a 100
                            
                            html += `
                                <div class="border-bottom py-2" style="opacity: ${prog == 100 ? '0.6' : '1'}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small ${prog == 100 ? 'text-decoration-line-through text-muted' : ''}">${t.actividad}</span>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light-success me-2" style="font-size: 9px;">${prog}%</span>
                                            <i class="fa fa-trash-o text-danger" style="cursor: pointer; opacity: 0.5;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5" onclick="deleteTodo(${t.id})"></i>
                                        </div>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="100" step="5" value="${prog}" 
                                           onchange="updateTodoProgress(${t.id}, this.value)" 
                                           style="height: 1.2rem;">
                                </div>
                            `;
                        });
                    }
                }
                todoContainer.innerHTML = html;
                
                // Cálculo de Porcentaje Global (Promedio de progresos)
                // total * 100 = total posible puntos
                // total*100 - pending = puntos obtenidos
                const totalPuntosPosibles = total * 100;
                const puntosObtenidos = totalPuntosPosibles - pending;
                const porcentajeGlobal = total > 0 ? Math.round((puntosObtenidos / totalPuntosPosibles) * 100) : 0;
                
                if(todoTotalLabel) todoTotalLabel.innerText = `${total}/50`;
                if(todoPercentageLabel) todoPercentageLabel.innerText = `${porcentajeGlobal}%`;
                if(todoProgressBar) todoProgressBar.style.width = `${porcentajeGlobal}%`;
                
                // Badge de tareas incompletas (menos de 100%)
                let incompleteCount = 0;
                for (const p in data) {
                    data[p].forEach(x => { if(x.progreso < 100) incompleteCount++; });
                }

                if(todoBadge) {
                    if (incompleteCount > 0) {
                        todoBadge.innerText = incompleteCount;
                        todoBadge.style.display = 'inline-block';
                    } else {
                        todoBadge.style.display = 'none';
                    }
                }
            });
    }

    window.updateTodoProgress = function(id, val) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('progreso', val);
        fetch('index.php?page=todo_update_progress', { method: 'POST', body: formData })
            .then(() => loadTodos());
    };

    window.deleteTodo = function(id) {
        if (!confirm('¿Eliminar esta actividad?')) return;
        const formData = new FormData();
        formData.append('id', id);
        fetch('index.php?page=todo_eliminar', { method: 'POST', body: formData })
            .then(() => loadTodos());
    };

    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            const proyecto = inputProject.value.trim() || 'General';
            const actividad = inputTask.value.trim();
            
            if (!actividad) return;

            const formData = new FormData();
            formData.append('proyecto', proyecto);
            formData.append('actividad', actividad);

            fetch('index.php?page=todo_agregar', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        inputTask.value = '';
                        loadTodos();
                    } else {
                        alert('Límite de 50 tareas alcanzado.');
                    }
                });
        });
    }

    // --- SISTEMA DE BÚSQUEDA RÁPIDA (SITEMAP) ---
    const sitemap = [
        { name: "Dashboard / Inicio", url: "index.php?page=dashboard", icon: "home" },
        { name: "Inmuebles - Maestro", url: "index.php?page=inmueble_maestro_lista", icon: "building" },
        { name: "Inmuebles - Tablas de Control", url: "index.php?page=inmuebles_atributos_lista", icon: "settings" },
        { name: "Inmuebles - Mover Carpetas", url: "index.php?page=inmueble_mover_carpetas_usuarios", icon: "move" },
        { name: "Actividad del Mercado", url: "index.php?page=web_actividad_mercado", icon: "bar-chart" },
        { name: "Mis Contratos / Suscripción", url: "index.php?page=contratos_lista", icon: "file-text" },
        { name: "Planes Disponibles", url: "index.php?page=contratos_planes", icon: "shopping-bag" },
        { name: "To-Do List / Actividades", url: "index.php?page=todo_gestion", icon: "check-square" },
        { name: "Usuarios - Lista", url: "index.php?page=usuarios_lista", icon: "users" },
        { name: "Usuarios - Crear Nuevo", url: "index.php?page=usuarios_crear", icon: "user-plus" },
        { name: "Empresas / Entidades", url: "index.php?page=empresas_lista", icon: "briefcase" },
        { name: "Mensajería Interna", url: "index.php?page=mensajeria", icon: "mail" },
        { name: "Tasas de Cambio", url: "index.php?page=tasas_cambio_lista", icon: "dollar" },
        { name: "Cuentas Bancarias", url: "index.php?page=cuentas_bancarias_lista", icon: "credit-card" },
        { name: "Perfiles y Accesos", url: "index.php?page=perfiles_lista", icon: "lock" },
        { name: "Configuración de Dashboard", url: "index.php?page=dashboard_layouts", icon: "layout" },
        { name: "Cierre de Mes", url: "index.php?page=cierre_mes", icon: "calendar" },
        { name: "Preferencias de Negocio", url: "index.php?page=preferencias_negocio", icon: "sliders" },
        { name: "Mi Cuenta", url: "index.php?page=usuarios_editar&id=<?= $_SESSION['usuario_id'] ?? '' ?>", icon: "user" }
    ];

    const searchInput = document.getElementById('sitemap-search-input');
    const searchResults = document.getElementById('sitemap-search-results');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const filtered = sitemap.filter(item => 
                item.name.toLowerCase().includes(query) || 
                item.url.toLowerCase().includes(query)
            );

            if (filtered.length > 0) {
                let html = '<div class="Typeahead-menu is-open" style="display:block; width: 100%;">';
                filtered.forEach(item => {
                    html += `
                        <div class="ProfileCard u-cf" style="cursor:pointer; padding: 10px;" onclick="window.location.href='${item.url}'">
                            <div class="ProfileCard-avatar" style="background: #f3f3f3; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 4px;">
                                <i class="fa fa-${item.icon} text-primary"></i>
                            </div>
                            <div class="ProfileCard-details" style="margin-left: 45px;">
                                <div class="ProfileCard-realName" style="font-weight: 600;">${item.name}</div>
                                <div class="small text-muted" style="font-size: 10px;">${item.url}</div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                searchResults.innerHTML = html;
                searchResults.style.display = 'block';
            } else {
                searchResults.innerHTML = '<div class="EmptyMessage p-3">No se encontraron páginas o módulos con ese nombre.</div>';
                searchResults.style.display = 'block';
            }
        });

        // Manejar tecla Enter para ir al primer resultado
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstResult = searchResults.querySelector('.ProfileCard');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    // Cargar To-Dos al inicio
    loadTodos();
    setInterval(loadTodos, 60000);
});
</script>
        <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">                        
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details">
            <div class="ProfileCard-realName">{{name}}</div>
            </div>
            </div>
          </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
    </div>
</div>

<script>
(function() {
    const sel = document.getElementById('selEntidadTopbar');
    if (!sel) return;

    function sincronizarSelectsEntidad(val, omitir) {
        document.querySelectorAll('#selEntidad, #selEntidadCaja, #selEntidadTopbar').forEach(function(el) {
            if (el !== omitir) el.value = val;
        });
    }

    sel.addEventListener('change', function() {
        const val = this.value;
        if (!val) return;

        const formData = new FormData();
        formData.append('cambiar_entidad', val);

        fetch('index.php?page=medico_cambiar_entidad_ajax', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                sincronizarSelectsEntidad(val, this);
                const src = (res.entidad && res.entidad.sidebar_logo_url) || '';
                ['sidebarLogoLight', 'sidebarLogoDark', 'sidebarLogoIcon'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.src = src;
                });
            }
        })
        .catch(err => console.error('Error al cambiar entidad:', err));
    });
})();
</script>
