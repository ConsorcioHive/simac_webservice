<?php
// app/views/lotes_egresos/index.php
require APP_PATH . '/views/layout/app_start.php';
?>
<div class="row">
  <div class="col-xl-5 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-download me-2"></i>Traer lote de la nube</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($msg)): ?>
          <div class="alert alert-<?= (strpos($msg, 'Error') !== false || strpos($msg, 'fall') !== false) ? 'danger' : 'success' ?> py-2"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <p class="small text-muted mb-3">
          Descarga un lote de egreso desde SIMAC cloud y lo guarda en
          <code>public/uploads/empresas/&lt;code&gt;/archivos/lotes_egresos/&lt;lote&gt;/</code>
          con el consolidado, el manifiesto y una carpeta por admisión con sus adjuntos.
        </p>

        <form method="post" action="index.php?page=lotes_egresos_traer" class="mb-3">
          <div class="input-group">
            <input type="text" class="form-control" name="codigo_lote" placeholder="Ej: 20260923204831_1013_0001_L1" required>
            <button type="submit" class="btn btn-primary"><i class="fa fa-cloud-download me-1"></i>Traer</button>
          </div>
        </form>

        <form method="post" action="index.php?page=lotes_egresos_traer_todos">
          <button type="submit" class="btn btn-success w-100">
            <i class="fa fa-refresh me-2"></i>Traer todos los pendientes
            <span class="badge bg-dark ms-1"><?= count($pendientesNube) ?></span>
          </button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fa fa-cloud me-2"></i>Pendientes en la nube</h5>
        <span class="badge bg-warning text-dark"><?= count($pendientesNube) ?></span>
      </div>
      <div class="card-body">
        <?php if (!empty($pendientesError)): ?>
          <div class="alert alert-warning py-2 small mb-0"><?= htmlspecialchars($pendientesError) ?></div>
        <?php elseif (empty($pendientesNube)): ?>
          <p class="text-muted small mb-0"><em>No hay lotes esperando descarga (estado Enviado).</em></p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Código</th>
                  <th>Enviado</th>
                  <th>Adm.</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($pendientesNube as $p): $cod = (string)($p['codigo_lote'] ?? ''); ?>
                <tr>
                  <td><code class="small"><?= htmlspecialchars($cod) ?></code></td>
                  <td class="small"><?= htmlspecialchars(substr((string)($p['enviado_local_at'] ?? ''), 0, 16)) ?></td>
                  <td class="small"><?= (int)($p['total_admisiones'] ?? 0) ?></td>
                  <td class="text-end">
                    <form method="post" action="index.php?page=lotes_egresos_traer" class="d-inline">
                      <input type="hidden" name="codigo_lote" value="<?= htmlspecialchars($cod, ENT_QUOTES) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fa fa-download"></i> Traer</button>
                    </form>
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

  <div class="col-xl-7 col-lg-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fa fa-folder-open me-2"></i>Lotes descargados</h5>
        <span class="badge bg-secondary"><?= count($lotesLocales) ?></span>
      </div>
      <div class="card-body">
        <?php if (empty($lotesLocales)): ?>
          <div class="text-center text-muted py-4">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            <p class="mb-0">Aún no hay lotes en el equipo local.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Lote</th>
                  <th>Bajado</th>
                  <th>Adm.</th>
                  <th>Archivos</th>
                  <th>JSON</th>
                  <th>Detalle</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($lotesLocales as $l): ?>
                <tr>
                  <td><code class="small"><?= htmlspecialchars($l['codigo']) ?></code></td>
                  <td class="small"><?= htmlspecialchars($l['fecha']) ?></td>
                  <td class="small"><?= $l['total_admisiones'] !== null ? (int)$l['total_admisiones'] : count($l['admisiones']) ?></td>
                  <td class="small"><?= (int)$l['docs_totales'] ?></td>
                  <td>
                    <?php if ($l['json_ok']): ?>
                      <span class="badge bg-success">OK</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Incompleto</span>
                    <?php endif; ?>
                  </td>
                  <td class="small">
                    <?php foreach ($l['admisiones'] as $a): ?>
                      <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($a['carpeta']) ?> · <?= (int)$a['docs'] ?> docs</span>
                    <?php endforeach; ?>
                    <?php if (empty($l['admisiones'])): ?>
                      <span class="text-muted">sin adjuntos</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mt-3 mb-0">
            Ruta base: <code>public/uploads/empresas/&lt;code&gt;/archivos/lotes_egresos/</code>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Estructura por lote</h5>
      </div>
      <div class="card-body">
        <pre class="small mb-0 bg-light p-3 rounded">&lt;codigo_lote&gt;/
├── consolidado_&lt;codigo_lote&gt;.json   (JSON completo de cada admisión)
├── manifiesto_&lt;codigo_lote&gt;.json    (índice del lote)
├── admision_&lt;id&gt;/
│   ├── adjunto1.pdf
│   └── adjunto2.jpg
└── admision_&lt;id2&gt;/
    └── ...</pre>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
