<?php
    // 1. Detectamos la página actual desde la URL (?page=...)
    $slug = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    // 2. Limpiamos el nombre
    $titulo_limpio = ucfirst(str_replace(['_', '-'], ' ', $slug));

    // 3. Nombres especiales para ciertos slugs
    $nombres_especiales = [
        'dashboard_layouts' => 'Configuración de Dashboard',
        'cierre_mes'        => 'Cierre Mensual',
        'final_lote_docs_lista' => 'Documentos Finales',
        'pacientes_lista'   => 'Listado de Pacientes',
        'paciente_nuevo'    => 'Crear nuevo paciente',
        'medico_consultorios_lista' => 'Gestión de consultorios médicos',
        'medico_gestionar_consultorio' => 'Gestión de consultorios',
        'admin_plantillas_sistema' => 'Plantillas del Sistema',
        'admin_plantilla_campos' => 'Campos de Plantilla',
        'empresas_form' => 'Editar Clínica/Empresa',
        'gerente_dashboard' => 'Dashboard Gerencial',
    ];

    if (array_key_exists($slug, $nombres_especiales)) {
        $titulo_limpio = $nombres_especiales[$slug];
    }
?>

<div class="container-fluid">        
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $titulo_limpio ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb" style="flex-wrap: nowrap; white-space: nowrap;">
                    <li class="breadcrumb-item">
                        <a href="index.php?page=<?= ($_SESSION['usuario_rol'] ?? '') === 'medico' ? 'medico_dashboard' : 'dashboard' ?>">
                            <svg class="stroke-icon">
                                <use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-home"></use>
                            </svg>
                        </a>
                    </li>
                    <?php if (!empty($breadcrumbExtra) && is_array($breadcrumbExtra)): ?>
                        <?php foreach ($breadcrumbExtra as $item): ?>
                            <li class="breadcrumb-item"><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($slug !== 'dashboard'): ?>
                        <li class="breadcrumb-item active">
                        <?php
                        if ($titulo_limpio == "Proveedores lista") {
                            echo "Manejo de Inmuebles";
                        } else {
                            if ($titulo_limpio == "Clientes lista") {
                                echo "Asesores Independientes";
                            }else{
                                echo $titulo_limpio;
                            }
                        }
                        ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active">Dashboard</li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</div>