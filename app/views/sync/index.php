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
        <?php if (!empty($_GET['carpeta'])): $carpetaEsperada = trim((string)$_GET['carpeta']); ?>
          <div class="alert alert-info py-2" id="cp-status">
            <strong>Proceso en la nube:</strong> paquete <code><?= htmlspecialchars($carpetaEsperada) ?></code> recibido. Esperando que el proceso del VPS termine... <span id="cp-lastcheck"></span>
          </div>
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
        <p class="small text-muted mb-2">Selecciona los archivos generados por el sistema externo. Se guardan en la carpeta configurada de la clínica y se suben a la nube automáticamente. Todos los tipos son opcionales e independientes (p. ej. puedes subir solo <code>clientes.json</code>).</p>
        <form method="post" action="index.php?page=sync" enctype="multipart/form-data">
          <input type="hidden" name="_action" value="colocar_json">
          <div class="mb-2">
            <label class="form-label small mb-1">prespuestos.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="prespuestos_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">servicios.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="servicios_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">clientes.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="clientes_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">convenios.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="convenios_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">medicos.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="medicos_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">productos.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="productos_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">existencias.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="existencias_json" accept=".json,application/json">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">almacenes.json <span class="text-muted">(opcional)</span></label>
            <input type="file" class="form-control form-control-sm" name="almacenes_json" accept=".json,application/json">
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
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0"><i class="fa fa-history me-2"></i>Registro de sincronización — <?= htmlspecialchars($GLOBALS['_sync_empresa'] ?? '') ?></h5>
        <div class="d-flex gap-2">
          <form method="post" action="index.php?page=sync" onsubmit="return confirm('¿Vaciar todo el historial de sincronización local?');">
            <input type="hidden" name="_action" value="vaciar_log">
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash me-1"></i>Vaciar historial</button>
          </form>
          <a href="index.php?page=sync_log" class="btn btn-outline-secondary btn-sm"><i class="fa fa-history me-1"></i>Ver historial completo</a>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <form method="get" action="index.php" class="d-flex gap-2 mb-0">
            <input type="hidden" name="page" value="sync">
            <input type="hidden" name="por_pagina" value="<?= (int)$porPagina ?>">
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control form-control-sm" placeholder="Buscar en cualquier campo...">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
            <?php if ($q !== ''): ?>
              <a href="index.php?page=sync&por_pagina=<?= (int)$porPagina ?>" class="btn btn-sm btn-outline-secondary"><i class="fa fa-times"></i></a>
            <?php endif; ?>
          </form>
          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Registros por página</span>
            <select class="form-select form-select-sm" style="width:auto;" onchange="location.href='index.php?page=sync&q=<?= urlencode($q) ?>&por_pagina='+this.value;">
              <?php foreach ([10, 15, 25, 50, 100] as $n): ?>
                <option value="<?= $n ?>" <?= $porPagina === $n ? 'selected' : '' ?>><?= $n ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr><th>Fecha</th><th>Tipo</th><th>Dirección</th><th>Estado</th><th>Mensaje</th></tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td class="f-light f-12 text-nowrap"><?= htmlspecialchars($l['creado_en']) ?></td>
                <td><?= htmlspecialchars($l['tipo']) ?></td>
                <td><span class="badge badge-light-secondary"><?= htmlspecialchars($l['direccion']) ?></span></td>
                <td><span class="badge badge-light-<?= ($l['estado'] ?? '') === 'ok' ? 'success' : 'danger' ?>"><?= htmlspecialchars($l['estado']) ?></span></td>
                <td class="f-light f-12"><?= htmlspecialchars(substr($l['mensaje'] ?? '', 0, 80)) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
              <tr><td colspan="5" class="text-center text-muted"><?= $q !== '' ? 'Sin resultados para "' . htmlspecialchars($q) . '".' : 'Sin registros.' ?></td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPaginas > 1 || $q !== ''): ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
          <span class="small text-muted"><?= $total ?> eventos en total<?= $q !== '' ? ' (filtrados)' : '' ?></span>
          <?php if ($totalPaginas > 1): ?>
          <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
              <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="index.php?page=sync&q=<?= urlencode($q) ?>&por_pagina=<?= $porPagina ?>&pagina=<?= $pagina - 1 ?>">&laquo;</a>
              </li>
              <?php
              $inicio = max(1, $pagina - 2);
              $fin = min($totalPaginas, $pagina + 2);
              for ($p = $inicio; $p <= $fin; $p++): ?>
                <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
                  <a class="page-link" href="index.php?page=sync&q=<?= urlencode($q) ?>&por_pagina=<?= $porPagina ?>&pagina=<?= $p ?>"><?= $p ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                <a class="page-link" href="index.php?page=sync&q=<?= urlencode($q) ?>&por_pagina=<?= $porPagina ?>&pagina=<?= $pagina + 1 ?>">&raquo;</a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>

<?php if (!empty($carpetaEsperada)): ?>
<script>
(function () {
    var carpeta = <?= json_encode($carpetaEsperada) ?>;
    var intentos = 0, maxIntentos = 30, intervalo = 10000;
    var box = document.getElementById('cp-status');
    var last = document.getElementById('cp-lastcheck');
    function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
    function fin() {
        if (last) last.textContent = '';
    }
    function poll() {
        fetch('index.php?page=sync_respuesta', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                intentos++;
                if (res && res.ok && res.paquete === carpeta && res.data && res.data.estado) {
                    if (res.data.estado === 'procesado') {
                        box.className = 'alert alert-success py-2';
                        box.innerHTML = '<strong>Proceso en el VPS terminó con éxito.</strong> ' + esc(res.data.mensaje || '')
                            + (res.data.detalle ? '<br><small>' + esc(res.data.detalle) + '</small>' : '');
                        fin();
                        return;
                    }
                    if (res.data.estado === 'rechazado') {
                        box.className = 'alert alert-danger py-2';
                        box.innerHTML = '<strong>El VPS rechazó el paquete.</strong> ' + esc(res.data.mensaje || '');
                        fin();
                        return;
                    }
                }
                if (intentos >= maxIntentos) {
                    box.className = 'alert alert-warning py-2';
                    box.innerHTML = 'El paquete se subió, pero todavía no se confirma el procesamiento en la nube. Puedes usar <em>Leer respuesta de la nube</em> más tarde.';
                    fin();
                    return;
                }
                if (last) last.textContent = 'Última comprobación: ' + new Date().toLocaleTimeString();
                setTimeout(poll, intervalo);
            })
            .catch(function () {
                intentos++;
                if (intentos >= maxIntentos) {
                    box.className = 'alert alert-warning py-2';
                    box.innerHTML = 'No se pudo confirmar el procesamiento. Usa <em>Leer respuesta de la nube</em> más tarde.';
                    fin();
                    return;
                }
                setTimeout(poll, intervalo);
            });
    }
    setTimeout(poll, intervalo);
})();
</script>
<?php endif; ?>