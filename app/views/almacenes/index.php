<?php
// app/views/almacenes/index.php
require APP_PATH . '/views/layout/app_start.php';
?>
<div class="row">
  <div class="col-xl-5 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-upload me-2"></i>Subir almacenes.json</h5>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-3">Selecciona el archivo <code>almacenes.json</code> generado por el sistema externo. Se guardará en la carpeta de control de parámetros y se subirá a la nube automáticamente.</p>

        <?php if (!empty($msg)): ?>
          <div class="alert alert-<?= strpos($msg, 'Error') !== false ? 'danger' : 'success' ?> py-2"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=almacenes_colocar" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label fw-semibold">almacenes.json</label>
            <input type="file" class="form-control" name="almacenes_json" accept=".json,application/json" required>
          </div>
          <button type="submit" class="btn btn-success w-100"><i class="fa fa-cloud-upload me-2"></i>Colocar y sincronizar</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Estructura esperada</h5>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-2">Cada registro de almacén debe contener:</p>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light"><tr><th>Campo</th><th>Tipo</th><th>Descripción</th></tr></thead>
            <tbody>
              <tr><td><code>CodUbic</code></td><td>string</td><td>Código del almacén</td></tr>
              <tr><td><code>Descrip</code></td><td>string</td><td>Nombre / descripción</td></tr>
              <tr><td><code>CodSucu</code></td><td>string|null</td><td>Código de sucursal</td></tr>
              <tr><td><code>Clase</code></td><td>string|null</td><td>Clase de almacén</td></tr>
              <tr><td><code>Represent</code></td><td>string</td><td>Representante</td></tr>
              <tr><td><code>Direc1</code></td><td>string|null</td><td>Dirección línea 1</td></tr>
              <tr><td><code>Direc2</code></td><td>string|null</td><td>Dirección línea 2</td></tr>
              <tr><td><code>Telef</code></td><td>string|null</td><td>Teléfono</td></tr>
              <tr><td><code>Pais</code></td><td>int</td><td>Código del país</td></tr>
              <tr><td><code>Estado</code></td><td>int</td><td>Código del estado</td></tr>
              <tr><td><code>Ciudad</code></td><td>int</td><td>Código de la ciudad</td></tr>
              <tr><td><code>EsVirtual</code></td><td>int</td><td>0 = físico, 1 = virtual</td></tr>
              <tr><td><code>CodAlte</code></td><td>string</td><td>Código alterno</td></tr>
              <tr><td><code>Activo</code></td><td>int</td><td>1 = activo, 0 = inactivo</td></tr>
              <tr><td><code>Printer</code></td><td>int</td><td>Impresora asignada</td></tr>
              <tr><td><code>Municipio</code></td><td>int</td><td>Código del municipio</td></tr>
              <tr><td><code>ZipCode</code></td><td>string|null</td><td>Código postal</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-7 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-list me-2"></i>Almacenes actuales</h5>
      </div>
      <div class="card-body">
        <?php if (empty($almacenesActuales)): ?>
          <div class="text-center text-muted py-4">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            <p>No hay almacenes cargados. Sube un archivo <code>almacenes.json</code> para comenzar.</p>
          </div>
        <?php else: ?>
          <p class="small text-muted mb-2">Registros en <code>almacenes.json</code>: <strong><?= is_array($almacenesActuales) ? count($almacenesActuales) : 0 ?></strong></p>
          <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-hover table-striped">
              <thead class="table-light" style="position: sticky; top: 0;">
                <tr>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Sucursal</th>
                  <th>Dirección</th>
                  <th>Activo</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($almacenesActuales as $a): ?>
                <tr>
                  <td><code><?= htmlspecialchars($a['CodUbic'] ?? '') ?></code></td>
                  <td><?= htmlspecialchars($a['Descrip'] ?? '') ?></td>
                  <td><?= htmlspecialchars($a['CodSucu'] ?? '-') ?></td>
                  <td class="small"><?= htmlspecialchars($a['Direc1'] ?? '-') ?></td>
                  <td>
                    <span class="badge badge-light-<?= (!empty($a['Activo']) ? 'success' : 'danger') ?>">
                      <?= (!empty($a['Activo']) ? 'Sí' : 'No') ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
