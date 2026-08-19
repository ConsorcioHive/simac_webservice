<?php
// app/views/sync/index.php
require APP_PATH . '/views/layout/app_start.php';
function mostrarArchivosSync($lista) {
    if (empty($lista)) return '<p class="text-muted small mb-0"><em>Sin archivos.</em></p>';
    echo '<ul class="mb-0">';
    foreach ($lista as $f) echo '<li>' . htmlspecialchars($f) . '</li>';
    echo '</ul>';
}
?>
<div class="row">
  <div class="col-xl-4 col-lg-5">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Sincronización con SIMAC (nube)</h5>
      </div>
      <div class="card-body">
        <p class="small">Modo actual: <strong><?= !empty($GLOBALS['_sync_stub']) ? 'STUB (simulado, sin nube real)' : 'REAL (envía a SIMAC_CLOUD_URL)' ?></strong></p>
        <?php if (!empty($_GET['m'])): ?>
          <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['m']) ?></div>
        <?php endif; ?>
        <div class="d-grid gap-2">
          <form method="post" action="index.php?page=sync">
            <input type="hidden" name="_action" value="subir_archivos">
            <button type="submit" class="btn btn-success w-100"><i class="fa fa-upload me-2"></i>Subir control de parámetros</button>
          </form>
          <form method="post" action="index.php?page=sync">
            <input type="hidden" name="_action" value="leer_respuesta">
            <button type="submit" class="btn btn-outline-success w-100"><i class="fa fa-check-circle me-2"></i>Leer respuesta de la nube</button>
          </form>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Colocar JSON de control de parámetros</h5>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-2">Selecciona los dos archivos generados por el sistema externo. Se guardan en la carpeta configurada de la clínica y se suben a la nube automáticamente.</p>
        <form method="post" action="index.php?page=sync" enctype="multipart/form-data">
          <input type="hidden" name="_action" value="colocar_json">
          <div class="mb-2">
            <label class="form-label small mb-1">prespuestos.json</label>
            <input type="file" class="form-control form-control-sm" name="prespuestos_json" accept=".json,application/json" required>
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">servicios.json</label>
            <input type="file" class="form-control form-control-sm" name="servicios_json" accept=".json,application/json" required>
          </div>
          <button type="submit" class="btn btn-success w-100"><i class="fa fa-upload me-2"></i>Colocar y sincronizar</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Contenedor de archivos (transición)</h5>
      </div>
      <div class="card-body">
        <p class="small mb-1"><strong>Inbox</strong> (entrada — colocar aquí los archivos a mover):</p>
        <?php mostrarArchivosSync($inbox); ?>
        <hr>
        <p class="small mb-1"><strong>Outbox</strong> (salida — listos para subir a la nube):</p>
        <?php mostrarArchivosSync($outbox); ?>
      </div>
    </div>
  </div>

  <div class="col-xl-8 col-lg-7">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Registro de sincronización</h5>
        <div class="d-flex gap-2">
          <form method="post" action="index.php?page=sync" onsubmit="return confirm('¿Vaciar todo el historial de sincronización local?');">
            <input type="hidden" name="_action" value="vaciar_log">
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash me-1"></i>Vaciar historial</button>
          </form>
          <a href="index.php?page=sync_log" class="btn btn-outline-secondary btn-sm"><i class="fa fa-history me-1"></i>Ver historial completo</a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr><th>Fecha</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th>Mensaje</th></tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td class="f-light f-12"><?= htmlspecialchars($l['creado_en']) ?></td>
                <td><?= htmlspecialchars($l['tipo']) ?></td>
                <td><span class="badge badge-light-secondary"><?= htmlspecialchars($l['direccion']) ?></span></td>
                <td><span class="badge badge-light-<?= ($l['estado'] ?? '') === 'ok' ? 'success' : 'danger' ?>"><?= htmlspecialchars($l['estado']) ?></span></td>
                <td class="f-light f-12"><?= htmlspecialchars(substr($l['mensaje'] ?? '', 0, 80)) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
              <tr><td colspan="5" class="text-center text-muted">Sin registros.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>