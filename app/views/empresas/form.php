<?php

// Wizard de empresa clonado de SIMAC (app/views/empresas/form.php), adaptado al
// sistema local single-tenant: usa el chrome local (app_start/app_end).
$GLOBALS['_page_title']   = 'Editar empresa';
$GLOBALS['_sidebar_current'] = 'empresa';
require APP_PATH . '/views/layout/app_start.php';
?>

<div class="col-sm-12">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <span>Wizard de edición de empresa, usuario (<?= htmlspecialchars($_SESSION['usuario_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)</span>
      </div>
      <a href="index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">
        ← Volver al dashboard
      </a>
    </div>

    <div class="card-body">
      <?php if (!empty($_SESSION['flash_danger'])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($_SESSION['flash_danger']); ?>
        </div>
        <?php unset($_SESSION['flash_danger']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['flash_success']); ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
      <?php endif; ?>

      <div class="stepwizard">
        <div class="stepwizard-row setup-panel">
          <div class="stepwizard-step">
            <a class="btn btn-primary" href="#step-1">1</a>
            <p> Datos básicos </p>
          </div>
          <div class="stepwizard-step">
            <a class="btn btn-light" href="#step-2">2</a>
            <p> Ubicación </p>
          </div>
          <div class="stepwizard-step">
            <a class="btn btn-light" href="#step-3">3</a>
            <p> Branding </p>
          </div>
          <div class="stepwizard-step" id="step-monedas-nav" style="display:none;">
            <a class="btn btn-light" href="#step-4">4</a>
            <p> Monedas </p>
          </div>
          <div class="stepwizard-step" id="step-admin-nav" style="display:none;">
            <a class="btn btn-light" href="#step-5">5</a>
            <p> Administraci&oacute;n </p>
          </div>
          <div class="stepwizard-step">
            <a class="btn btn-light" href="#step-6">6</a>
            <p> Preferencias </p>
          </div>
        </div>
      </div>

                <form action="index.php?page=empresas_guardar" method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="id"
                  value="<?= htmlspecialchars($empresa['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <!-- AGREGA ESTOS DOS CAMPOS -->
                 <input type="hidden" name="input_redes" id="input_redes" 
                    value="<?= htmlspecialchars($empresa['redes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    
                <input type="hidden" name="input_redes_url" id="input_redes_url" 
                    value="<?= htmlspecialchars($empresa['redes_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">


                <!-- STEP 1: Datos básicos + contacto -->
                <div class="setup-content" id="step-1">
                  <div class="col-xs-12">
                    <div class="col-md-12">
                
                      <div class="row">
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="control-label">Código</label>
                            <input name="company_code" type="text" required class="form-control"
                                   value="<?= htmlspecialchars($empresa['company_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                
                        <div class="col-md-8">
                          <div class="mb-3">
                            <label class="control-label">Nombre</label>
                            <input class="form-control" type="text" name="nombre" required
                                   value="<?= htmlspecialchars($empresa['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                      </div>
                
                      <div class="row">
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label class="control-label">Slogan</label>
                            <input class="form-control" type="text" name="slogan" maxlength="255"
                                   placeholder="Ej: La mejor calidad al mejor precio"
                                   value="<?= htmlspecialchars($empresa['slogan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <small class="text-muted">Máximo 255 caracteres.</small>
                          </div>
                        </div>
                      </div>
                
                      <div class="row">
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="control-label">Tipo</label>
                            <?php $tipo = isset($empresa['tipo']) ? (int)$empresa['tipo'] : 0; ?>
                            <select name="tipo" required class="form-select">
                              <option value="0" <?= $tipo === 0 ? 'selected' : '' ?>>Sede Principal</option>
                              <option value="1" <?= $tipo === 1 ? 'selected' : '' ?>>Entidad / Sucursal</option>
                              <option value="2" <?= $tipo === 2 ? 'selected' : '' ?>>Holding / Corporación</option>
                            </select>
                          </div>
                        </div>
                
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="control-label">RIF</label>
                            <input name="rif" type="text" required class="form-control"
                                placeholder="J1234567890"
                                pattern="[JVEG][\-]?[0-9]{1,10}"
                                title="Debe comenzar con J, V, E o G seguido de hasta 10 dígitos."
                                value="<?= htmlspecialchars($empresa['rif'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="11"
                                   >
                          </div>
                          <script>
                          document.querySelector('input[name="rif"]').value = 
						  document.querySelector('input[name="rif"]').value.replace(/-/g, '');
						  </script>

                        </div>
                
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="control-label">Teléfono</label>
                            <input name="telefono" type="text" required class="form-control"
                                   value="<?= htmlspecialchars($empresa['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                      </div>
                
                      <div class="row">
                          <!-- Columna izquierda: Email -->
                          <div class="col-md-6">
                            <div class="mb-3">
                              <label class="control-label">Email</label>
                              <input name="email" type="email" required class="form-control"
                                     value="<?= htmlspecialchars($empresa['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                          </div>
                        
                          <!-- Columna derecha: Contacto + RIF + Descripción -->
                          <div class="col-md-6">
                            <!-- Contacto principal -->
                            <div class="mb-3">
                              <label for="usuario_contacto_id" class="form-label">Contacto principal de la empresa</label>
                              <select name="usuario_contacto_id" id="usuario_contacto_id" class="form-select">
                                <option value="">-- Seleccionar usuario --</option>
                                <?php foreach ($usuariosEmpresa as $u): ?>
                                  <option value="<?= (int)$u['usuario_id'] ?>"
                                    <?= !empty($empresa['usuario_contacto_id']) && (int)$empresa['usuario_contacto_id'] === (int)$u['usuario_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombre'] ?? $u['email'], ENT_QUOTES, 'UTF-8') ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                              <small class="text-muted">
                                Este usuario será el contacto primario de la empresa.
                              </small>
                            </div>
                        
                            
                        </div>
                
                      <!-- Descripción del negocio -->
                            <div class="mb-6">
                              <label for="descripcion_negocio" class="form-label">Descripción del negocio</label>
                              <textarea
                                name="empresa_description"
                                rows="3"
                                maxlength="500" required
                                class="form-control"
                                id="descripcion_negocio"
                              ><?= htmlspecialchars($empresa['empresa_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                              <small class="text-muted">
                                Máximo 500 caracteres.
                              </small>
                            </div>
                          </div>
                      
                
                
                    
                      <button class="btn btn-primary nextBtn pull-right" type="button">
                        Siguiente
                      </button>
                    </div>
                  </div>
                </div>


                <!-- Step 2: Ubicación -->
                <div class="setup-content" id="step-2">
                    <div class="row">
                        <div class="row">
                          <!-- Dirección fiscal -->
                          <div class="col-md-5 mb-3">
                            <label for="direccion_fiscal" class="form-label">Dirección fiscal</label>
                            <textarea
                              name="direccion_fiscal"
                              rows="3" required
                              class="form-control"
                              id="direccion_fiscal"
                            ><?= htmlspecialchars($empresa['direccion_fiscal'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                          </div>
                        </div>
                
                        <?php if (!empty($empresa)): ?>
                        <script>
                          window.empresaUbicacion = {
                            pais:      '<?= $empresa['codigo_pais']      ?? '' ?>',
                            estado:    '<?= $empresa['codigo_estado']    ?? '' ?>',
                            municipio: '<?= $empresa['codigo_municipio'] ?? '' ?>',
                            parroquia: '<?= $empresa['codigo_parroquia'] ?? '' ?>',
                            moneda:    '<?= $empresa['moneda_codigo']    ?? '' ?>'
                          };
                        </script>
                        <?php endif; ?>
                
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label class="form-label">País <span class="text-danger">*</span></label>
                               <select class="form-select" name="codigo_pais" id="codigo_pais" required>
                                <option value="">Selecciona país...</option>
                                <?php foreach ($paises as $pais): ?>
                                    <option value="<?= htmlspecialchars($pais['codigo_pais']) ?>"
                                        <?= (!empty($empresa['codigo_pais']) && $empresa['codigo_pais'] == $pais['codigo_pais']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pais['nombre']) ?> (<?= $pais['codigo_pais'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                        </div>
                
                        <!-- Estado -->
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Estado/Región <span class="text-danger">*</span></label>
                                <select class="form-select" name="codigo_estado" id="codigo_estado" required disabled data-value="<?= $empresa['codigo_estado'] ?? '' ?>">
                                    <option value="">Primero selecciona país...</option>
                                </select>
                            </div>
                        </div>
                
                        <!-- Municipio -->
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Municipio/Ciudad <span class="text-danger">*</span></label>
                                <select class="form-select" name="codigo_municipio" id="codigo_municipio" required disabled data-value="<?= $empresa['codigo_municipio'] ?? '' ?>">
                                    <option value="">Primero selecciona estado...</option>
                                </select>
                            </div>
                        </div>
                
                        <!-- Parroquia -->
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Parroquia (opcional)</label>
                                <select name="codigo_parroquia" class="form-select" id="codigo_parroquia" data-value="<?= $empresa['codigo_parroquia'] ?? '' ?>">
                                    <option value="">Selecciona parroquia...</option>
                                </select>
                            </div>
                        </div>
                
                        <!-- Código Postal -->
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label class="form-label">Código Postal</label>
                                <input type="text" class="form-control" name="codigo_postal" 
                                       id="codigo_postal" value="<?= htmlspecialchars($empresa['codigo_postal'] ?? '') ?>">
                            </div>
                        </div>
                
                        <!-- Moneda -->
                        <div class="col-lg-4 col-md-6">
                          <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                              <label class="form-label">Moneda <span class="text-danger">*</span></label>
                              <select class="form-select" name="moneda_codigo" id="moneda_codigo" required disabled>
                                <option value="">Selecciona primero municipio...</option>
                              </select>
                            </div>
                          </div>
                        </div>
                    </div>
                    <br>
                    <button class="btn btn-primary nextBtn pull-right" type="button">
                          Siguiente
                    </button>
                </div>



                <!-- STEP 3: Branding -->
                <div class="setup-content" id="step-3">
                  <div class="col-xs-12">
                    <div class="col-md-12">
                
                      <div class="row">
                        <!-- Logo -->
                        <div class="col-md-6">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="mb-0">Logo de la empresa</h5>
                            </div>
                            <div class="card-body">
                              <div class="mb-3">
                                <label class="form-label d-block">Logo actual / seleccionado</label>
                                <img
                                  id="logo_preview"
                                  src="<?= !empty($empresa['logo_url']) ? ( (strpos(ltrim($empresa['logo_url'], '/'), 'public/') === 0) ? BASE_URL . ltrim($empresa['logo_url'], '/') : BASE_URL . 'public/' . ltrim($empresa['logo_url'], '/') ) : '' ?>"
                                  alt="Logo"
                                  class="img-fluid border rounded"
                                  style="max-width:200px; height:auto; <?= empty($empresa['logo_url']) ? 'display:none;' : '' ?>"
                                >
                              </div>
                
                              <div class="mb-3">
                                <label for="logo" class="form-label">Subir nuevo logo</label>
                                <input
                                  type="file"
                                  name="logo"
                                  id="logo"
                                  class="form-control"
                                  accept="image/*"
                                >
                                <small class="text-muted">Al seleccionar un archivo se mostrará la vista previa.</small>
                              </div>
                            </div>
                          </div>
                        </div>
                		
                        
                        <?php if (!empty($empresa)): ?>
                          <input type="hidden" name="logo_url_actual"
                                 value="<?= htmlspecialchars($empresa['logo_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="business_card_url_actual"
                                 value="<?= htmlspecialchars($empresa['business_card_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="empresa_documento_url_actual"
                                 value="<?= htmlspecialchars($empresa['empresa_documento_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          <input type="hidden" name="empresa_caratula_url_actual"
                                 value="<?= htmlspecialchars($empresa['empresa_caratula_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>

                        <!-- Tarjeta de negocios -->
                        <div class="col-md-6">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="mb-0">Tarjeta de negocios</h5>
                            </div>
                            <div class="card-body">
                              <div class="mb-3">
                                <label class="form-label d-block">Tarjeta actual / seleccionada</label>
                
                                <?php if (!empty($empresa['business_card_url'])): ?>
                                  <?php if (preg_match('/\.pdf$/i', $empresa['business_card_url'])): ?>
                                    <a href="<?= htmlspecialchars(BASE_URL . ltrim($empresa['business_card_url'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                      Ver tarjeta (PDF)
                                    </a>
                                  <?php else: ?>
                                    <img
                                      id="business_card_preview"
                                      src="<?= !empty($empresa['business_card_url']) ? ( (strpos(ltrim($empresa['business_card_url'], '/'), 'public/') === 0) ? BASE_URL . ltrim($empresa['business_card_url'], '/') : BASE_URL . 'public/' . ltrim($empresa['business_card_url'], '/') ) : '' ?>"
                                      alt="Tarjeta"
                                      class="img-fluid border rounded"
                                      style="max-width:200px; height:auto;"
                                    >
                                  <?php endif; ?>
                                <?php else: ?>
                                  <img
                                    id="business_card_preview"
                                    src=""
                                    alt="Tarjeta"
                                    class="img-fluid border rounded"
                                    style="max-width:200px; height:auto; display:none;"
                                  >
                                <?php endif; ?>
                
                                <div id="business_card_info"
                                     class="text-muted mt-2"
                                     style="<?= empty($empresa['business_card_url']) ? 'display:none;' : '' ?>">
                                </div>
                              </div>
                
                              <div class="mb-3">
                                <label for="tarjeta_presentacion" class="form-label">Subir nueva tarjeta</label>
                                <input
                                  type="file"
                                  name="tarjeta_presentacion"
                                  id="tarjeta_presentacion"
                                  class="form-control"
                                  accept="image/*,application/pdf"
                                >
                                <small class="text-muted">
                                  Puedes subir una imagen para vista previa directa o un PDF.
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                       
                        <!-- Documento principal de la empresa -->
                        <div class="col-md-6 mt-4">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="mb-0">Documento principal de la empresa (PDF)</h5>
                            </div>
                            <div class="card-body">
                              <div class="mb-3">
                                <label class="form-label d-block">Documento actual / seleccionado</label>
                        
                                <?php if (!empty($empresa['empresa_documento_url'])): ?>
                                  <a href="<?= htmlspecialchars(BASE_URL . ltrim($empresa['empresa_documento_url'], '/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                    Ver documento (PDF)
                                  </a>
                                <?php else: ?>
                                  <span class="text-muted">No hay documento cargado.</span>
                                <?php endif; ?>
                              </div>
                        
                              <div class="mb-3">
                                <label for="empresa_documento" class="form-label">Subir nuevo documento</label>
                                <input
                                  type="file"
                                  name="empresa_documento"
                                  id="empresa_documento"
                                  class="form-control"
                                  accept="application/pdf"
                                >
                                <small class="text-muted">
                                  Formato permitido: PDF.
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Imagen de carátula -->
                        <div class="col-md-6 mt-4">
                          <div class="card">
                            <div class="card-header">
                              <h5 class="mb-0">Imagen de carátula del documento</h5>
                            </div>
                            <div class="card-body">
                              <div class="mb-3">
                                <label class="form-label d-block">Carátula actual / seleccionada</label>
                        
                                <img
                                  id="empresa_caratula_preview"
                                  src="<?= !empty($empresa['empresa_caratula_url']) ? ( (strpos(ltrim($empresa['empresa_caratula_url'], '/'), 'public/') === 0) ? BASE_URL . ltrim($empresa['empresa_caratula_url'], '/') : BASE_URL . 'public/' . ltrim($empresa['empresa_caratula_url'], '/') ) : '' ?>"
                                  alt="Carátula del documento"
                                  class="img-fluid border rounded"
                                  style="max-width:200px; height:auto; <?= empty($empresa['empresa_caratula_url']) ? 'display:none;' : '' ?>"
                                >
                              </div>
                        
                              <div class="mb-3">
                                <label for="empresa_caratula" class="form-label">Subir nueva carátula</label>
                                <input
                                  type="file"
                                  name="empresa_caratula"
                                  id="empresa_caratula"
                                  class="form-control"
                                  accept="image/*"
                                >
                                <small class="text-muted">
                                  Formatos permitidos: imágenes (JPG, PNG, WEBP, etc.).
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                
                      <hr>
                
                      <!-- Link al contenedor avanzado -->
                      <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <div>
                          <strong>Archivos avanzados:</strong>
                          Para manejar carpetas, subcarpetas y múltiples documentos de la empresa,
                          usa el módulo de contenedor de archivos.
                        </div>
                        <?php if (!empty($empresa['id'])): ?>
                          <a class="btn btn-primary btn-sm"
                             href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>"
                             style="background-color: #28a745; border-color: #28a745; color: white;">
                            Archivos y Carpetas
                          </a>
                        <?php else: ?>
                          <span class="text-muted">
                            Guarda primero la empresa para habilitar el contenedor de archivos.
                          </span>
                        <?php endif; ?>
                      </div>

                      <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary nextBtn" type="button">
                          Siguiente
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- STEP 4: Monedas (solo visible para superadmin) -->
                <div class="setup-content" id="step-4">
                  <div class="col-xs-12">
                    <div class="col-md-12">

                      <h5 class="mb-4">Configuraci&oacute;n de Monedas</h5>
                      <div class="alert alert-info py-2">
                        <i class="fa fa-lock"></i> Esta secci&oacute;n es solo para administradores del sistema.
                        Los cambios afectan a todas las empresas.
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Moneda Base del Sistema <span class="text-danger">*</span></label>
                            <select class="form-select" name="moneda_base_id" id="moneda_base_id">
                              <option value="">Seleccionar moneda base...</option>
                              <?php foreach ($todasMonedas as $m): ?>
                                <option value="<?= $m['id'] ?>"
                                  <?= !empty($m['es_moneda_base']) ? 'selected' : '' ?>>
                                  <?= $m['codigo'] ?> &mdash; <?= $m['nombre'] ?> (<?= $m['simbolo'] ?>)
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                              Actual: <strong><?= $monedaBase['codigo'] ?? 'EUR' ?></strong>
                              (<?= $monedaBase['nombre'] ?? 'Euro' ?>).
                              Al cambiar se recalcula autom&aacute;ticamente la tasa de todas las monedas.
                            </small>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Moneda de Pago (<?= htmlspecialchars($empresa['nombre'] ?? 'esta empresa') ?>)</label>
                            <select class="form-select" name="moneda_pago_id">
                              <option value="">Seleccionar moneda de pago...</option>
                              <?php foreach ($todasMonedas as $m): ?>
                                <option value="<?= $m['id'] ?>"
                                  <?= (!empty($empresa['moneda_pago_id']) && (int)$empresa['moneda_pago_id'] === (int)$m['id']) ? 'selected' : '' ?>>
                                  <?= $m['codigo'] ?> &mdash; <?= $m['nombre'] ?> (<?= $m['simbolo'] ?>)
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                              Es la moneda en que los pacientes ven los montos a pagar.
                            </small>
                          </div>
                        </div>
                      </div>

                      <div class="alert alert-warning" id="alerta-cambio-base" style="display:none;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> Al cambiar la moneda base se recalcular&aacute;n todas
                        las tasas de cambio del sistema. Aseg&uacute;rate de que las tasas actuales est&aacute;n
                        correctas antes de continuar.
                      </div>

                      <hr>
                      <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary prevBtn" type="button">
                          &larr; Atr&aacute;s
                        </button>
                        <button class="btn btn-primary nextBtn" type="button">
                          Siguiente &rarr;
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- STEP 5: Administración -->
                <div class="setup-content" id="step-5">
                  <div class="col-xs-12">
                    <div class="col-md-12">

                      <h5 class="mb-4">Administraci&oacute;n</h5>
                      <div class="alert alert-info py-2">
                        <i class="fa fa-lock"></i> Esta secci&oacute;n es solo para administradores del sistema.
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="form-label">Serial de Licencia</label>
                            <input type="text" class="form-control" name="serial_licencia" maxlength="60"
                              value="<?= htmlspecialchars($empresa['serial_licencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="form-label">Identificador SAINT</label>
                            <input type="text" class="form-control" name="identificador_saint" maxlength="10"
                              value="<?= htmlspecialchars($empresa['identificador_saint'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="mb-3">
                            <label class="form-label">Fecha de Expiraci&oacute;n</label>
                            <input type="date" class="form-control" name="fecha_expiracion"
                              value="<?= htmlspecialchars($empresa['fecha_expiracion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                          </div>
                        </div>
                      </div>

                      <hr>
                      <h6 class="mb-3">Sincronizaci&oacute;n con SIMAC cloud</h6>
                      <div class="alert alert-info py-2">
                        <i class="fa fa-cloud"></i> Datos de conexi&oacute;n de esta cl&iacute;nica con SIMAC cloud.
                        El token lo define el administrador de SIMAC y es exclusivo de esta empresa.
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">URL de SIMAC cloud</label>
                            <input type="url" class="form-control" name="simac_cloud_url"
                              placeholder="https://simacweb.app"
                              value="<?= htmlspecialchars($empresa['simac_cloud_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <small class="text-muted">Si se deja vac&iacute;o, se usa el valor global del sistema.</small>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Token de sincronizaci&oacute;n</label>
                            <input type="text" class="form-control" name="simac_api_token" autocomplete="off"
                              placeholder="tok_simac_&lt;codigo&gt;_&lt;xxx&gt;"
                              value="<?= htmlspecialchars($empresa['simac_api_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <small class="text-muted">Token exclusivo de la empresa <?= htmlspecialchars($empresa['company_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>.</small>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label class="form-label">Carpeta local de control de par&aacute;metros</label>
                            <input type="text" class="form-control" name="ruta_control_parametros"
                              placeholder="public/uploads/empresas/1013/archivos/control_parametros"
                              value="<?= htmlspecialchars($empresa['ruta_control_parametros'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <small class="text-muted">
                              Ruta (relativa a la ra&iacute;z del webservice o absoluta) donde el sistema externo
                              deposita <code>prespuestos.json</code> y <code>servicios.json</code>. Si se deja vac&iacute;a,
                              se usa el valor por defecto de la empresa.
                            </small>
                          </div>
                        </div>
                      </div>

                      <hr>
                      <h6 class="mb-3">Carpeta facturas_pendientes_medico</h6>
                      <div class="alert alert-info py-2">
                        <i class="fa fa-folder"></i> Carpeta para recepci&oacute;n de archivos JSON desde sistema local (cl&iacute;nicas, independientes, hospitales, ambulatorios, CID).
                      </div>
                      <?php
                      $companyCode = $empresa['company_code'] ?? '';
                      $folderPath = '/public/uploads/empresas/' . $companyCode . '/archivos/facturas_pendientes_medico/';
                      $exists = !empty($companyCode) && is_dir(BASE_PATH . $folderPath);
                      $savedPath = '';
                      if (!empty($empresa['entity_folders'])) {
                          $decoded = json_decode($empresa['entity_folders'], true);
                          if (is_array($decoded) && isset($decoded['path'])) {
                              $savedPath = $decoded['path'];
                          }
                      }
                      ?>
                      <div class="row">
                        <div class="col-md-12 mb-3">
                          <div class="card border">
                            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                              <div>
                                <i class="fa fa-folder text-primary me-2"></i>
                                <strong>facturas_pendientes_medico</strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($folderPath, ENT_QUOTES, 'UTF-8') ?></small>
                              </div>
                              <div class="text-nowrap">
                                <?php if ($exists): ?>
                                  <span class="badge bg-success me-2"><i class="fa fa-check"></i> Creada</span>
                                <?php else: ?>
                                  <span class="badge bg-secondary me-2"><i class="fa fa-times"></i> No existe</span>
                                  <button type="button" class="btn btn-sm btn-outline-primary btn-crear-carpeta"
                                    data-company-id="<?= (int)($empresa['id'] ?? 0) ?>">
                                    <i class="fa fa-folder-plus"></i> Crear
                                  </button>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <input type="hidden" name="entity_folders_json" id="entity_folders_json" value="<?= htmlspecialchars($empresa['entity_folders'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>">

                      <hr>
                      <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary prevBtn" type="button">
                          &larr; Atr&aacute;s
                        </button>
                        <button class="btn btn-primary nextBtn" type="button">
                          Siguiente &rarr;
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- STEP 6: Preferencias -->
                <div class="setup-content" id="step-6">
                  <div class="col-xs-12">
                    <div class="col-md-12">
                
                      <h5 class="mb-4">Preferencias de la empresa</h5>
                
                      <div class="row">
                        <!-- Idioma -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Idioma de la interfaz <span class="text-danger">*</span></label>
                            <select class="form-select" name="idioma" required>
                              <option value="">Selecciona un idioma...</option>
                              <option value="es" <?= (!empty($empresa['idioma']) && $empresa['idioma'] == 'es') ? 'selected' : '' ?>>
                                Español
                              </option>
                              <option value="en" <?= (!empty($empresa['idioma']) && $empresa['idioma'] == 'en') ? 'selected' : '' ?>>
                                English
                              </option>
                              <option value="pt" <?= (!empty($empresa['idioma']) && $empresa['idioma'] == 'pt') ? 'selected' : '' ?>>
                                Português
                              </option>
                            </select>
                          </div>
                        </div>
                
                        <!-- Zona horaria -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Zona horaria <span class="text-danger">*</span></label>
                            <select class="form-select" name="timezone" required>
                              <option value="">Selecciona zona horaria...</option>
                              <option value="America/Caracas" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Caracas') ? 'selected' : '' ?>>
                                América/Caracas (VET)
                              </option>
                              <option value="America/New_York" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/New_York') ? 'selected' : '' ?>>
                                América/New York (EST)
                              </option>
                              <option value="America/Chicago" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Chicago') ? 'selected' : '' ?>>
                                América/Chicago (CST)
                              </option>
                              <option value="America/Denver" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Denver') ? 'selected' : '' ?>>
                                América/Denver (MST)
                              </option>
                              <option value="America/Los_Angeles" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Los_Angeles') ? 'selected' : '' ?>>
                                América/Los Angeles (PST)
                              </option>
                              <option value="America/Bogota" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Bogota') ? 'selected' : '' ?>>
                                América/Bogotá (COT)
                              </option>
                              <option value="America/Argentina/Buenos_Aires" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Argentina/Buenos_Aires') ? 'selected' : '' ?>>
                                América/Buenos Aires (ART)
                              </option>
                              <option value="America/Sao_Paulo" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'America/Sao_Paulo') ? 'selected' : '' ?>>
                                América/São Paulo (BRT)
                              </option>
                              <option value="Europe/Madrid" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'Europe/Madrid') ? 'selected' : '' ?>>
                                Europa/Madrid (CET)
                              </option>
                              <option value="Europe/London" <?= (!empty($empresa['timezone']) && $empresa['timezone'] == 'Europe/London') ? 'selected' : '' ?>>
                                Europa/Londres (GMT)
                              </option>
                            </select>
                          </div>
                        </div>
                      </div>
                
                      <hr>
                
                      <h6 class="mb-3">Notificaciones</h6>
                
                      <div class="row">
                        <!-- Notificaciones por email -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <div class="form-check">
                              <input type="checkbox" class="form-check-input" name="notif_email" id="notif_email" 
                                     value="1" <?= (!empty($empresa['notif_email'])) ? 'checked' : '' ?>>
                              <label class="form-check-label" for="notif_email">
                                Recibir notificaciones por email
                              </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                              Recibirás alertas sobre cambios importantes de la empresa.
                            </small>
                          </div>
                        </div>
                
                        <!-- Notificaciones por SMS -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <div class="form-check">
                              <input type="checkbox" class="form-check-input" name="notif_sms" id="notif_sms" 
                                  value="1" <?= (!empty($empresa['notif_sms'])) ? 'checked' : '' ?>>
                
                              <label class="form-check-label" for="notif_sms">
                                Recibir notificaciones por SMS
                              </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                              Recibirás alertas urgentes vía mensajes de texto (aplican costos).
                            </small>
                          </div>
                        </div>
                      </div>
                
                      <hr>
                
                      <!-- === NUEVO: REDES SOCIALES === -->
                      <h6 class="mb-3">Redes Sociales</h6>
                
                      <div class="row">
                        <div class="col-md-12">
                          <p class="text-muted">Agrega los perfiles de redes sociales de tu empresa.</p>
                        </div>
                      </div>
                
                      <div class="row">
                        <!-- Select de redes -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Selecciona una red social</label>
                            <select class="form-select" id="select_red_social">
                              <option value="">-- Selecciona una red --</option>
                              <?php foreach ($socialNetworks as $red): ?>
                                <option value="<?= htmlspecialchars($red['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                        data-icono="<?= htmlspecialchars($red['icono'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-nombre="<?= htmlspecialchars($red['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-placeholder="<?= htmlspecialchars($red['placeholder'], ENT_QUOTES, 'UTF-8') ?>">
                                  <?= htmlspecialchars($red['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                
                        <!-- Input URL -->
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">URL del perfil</label>
                            <input type="url" class="form-control" id="input_url_red" placeholder="https://...">
                          </div>
                        </div>
                      </div>
                
                      <div class="row">
                        <div class="col-md-12">
                          <button type="button" class="btn btn-outline-primary btn-sm" id="btn_agregar_red">
                            <i class="fa fa-plus"></i> Agregar red social
                          </button>
                        </div>
                      </div>
                
                      <!-- Tabla de redes agregadas -->
                      <div class="row mt-4">
                        <div class="col-md-12">
                          <div class="table-responsive">
                            <table class="table table-sm table-striped" id="tabla_redes_sociales">
                              <thead class="table-light">
                                <tr>
                                  <th>Red Social</th>
                                  <th>URL</th>
                                  <th>Acción</th>
                                </tr>
                              </thead>
                              <tbody id="tbody_redes_sociales">
                                <!-- Se llena dinámicamente con JavaScript -->
                              </tbody>
                            </table>
                          </div>
                          <!-- Inputs ocultos para guardar datos -->
                            <input type="hidden" name="redes_id" id="input_redes_id" 
                                value="<?= htmlspecialchars($empresa['redes_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="redes" id="input_redes" 
                                value="<?= htmlspecialchars($empresa['redes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="redes_url" id="input_redes_url" 
                                value="<?= htmlspecialchars($empresa['redes_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                        </div>
                      </div>
                
                      <hr>
                
                      <h6 class="mb-3">Términos y condiciones</h6>
                
                      <div class="row">
                        <div class="col-md-12">
                          <div class="mb-3">
                            <div class="form-check">
                              <input type="checkbox" class="form-check-input" name="acepta_terminos" id="acepta_terminos" 
                                     value="1" <?= (!empty($empresa['acepta_terminos'])) ? 'checked' : '' ?> required>
                              <label class="form-check-label" for="acepta_terminos">
                                <strong>Acepto los términos y condiciones de uso</strong>
                                <a href="#" target="_blank" class="ms-1">(leer)</a>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                
                      <div class="row">
                        <div class="col-md-12">
                          <div class="mb-4">
                            <div class="form-check">
                              <input type="checkbox" class="form-check-input" name="acepta_privacidad" id="acepta_privacidad" 
                                     value="1" <?= (!empty($empresa['acepta_privacidad'])) ? 'checked' : '' ?> required>
                              <label class="form-check-label" for="acepta_privacidad">
                                <strong>Acepto la política de privacidad y protección de datos</strong>
                                <a href="#" target="_blank" class="ms-1">(leer)</a>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                
                      <hr>
                      <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary prevBtn" type="button">
                          ← Atrás
                        </button>
                        <button class="btn btn-success" type="submit">
                          ✓ Finalizar y guardar empresa
                        </button>
                      </div>
                
                    </div>
                  </div>
                </div>



                </form>
              </div>
            </div>
          </div>
<?php require APP_PATH . '/views/layout/app_end.php'; ?>

<script src="<?= BASE_URL ?>assets/js/form-wizard/form-wizard-two.js"></script>
<script src="<?= BASE_URL ?>assets/js/ubicacion-cascada.js"></script>
<script src="<?= BASE_URL ?>assets/js/empresas-ubicacion-init.js"></script>
<script src="<?= BASE_URL ?>assets/js/empresas-redes-sociales.js"></script>
<script src="<?= BASE_URL ?>assets/js/tooltip-init.js"></script>
<script src="<?= BASE_URL ?>public/js/empresas_form.js"></script>

<?php if ($esSuperAdmin): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('step-monedas-nav').style.display = '';
    document.getElementById('step-admin-nav').style.display = '';

    var baseSelect = document.getElementById('moneda_base_id');
    var alerta = document.getElementById('alerta-cambio-base');
    if (baseSelect && alerta) {
        var currentVal = baseSelect.value;
        baseSelect.addEventListener('change', function() {
            alerta.style.display = (this.value !== currentVal) ? 'block' : 'none';
        });
    }

    // Crear carpeta facturas_pendientes_medico via AJAX
    document.querySelectorAll('.btn-crear-carpeta').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var companyId = this.getAttribute('data-company-id');
            var btnEl = this;

            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creando...';

            var formData = new FormData();
            formData.append('company_id', companyId);

            fetch('index.php?page=empresas_crear_carpeta_entidad', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var card = btnEl.closest('.card-body');
                    var badge = card.querySelector('.badge');
                    badge.className = 'badge bg-success me-2';
                    badge.innerHTML = '<i class="fa fa-check"></i> Creada';
                    btnEl.remove();
                } else {
                    alert('Error: ' + (data.error || 'Desconocido'));
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fa fa-folder-plus"></i> Crear';
                }
            })
            .catch(function() {
                alert('Error de conexi\u00f3n al crear la carpeta');
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fa fa-folder-plus"></i> Crear';
            });
        });
    });
});
</script>
<?php endif; ?>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
