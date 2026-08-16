<?php
// app/views/sync/log.php — Historial local completo de sincronización.
require APP_PATH . '/views/layout/app_start.php';
$totalPaginas = max(1, (int)ceil($total / $porPagina));
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0"><i class="fa fa-history me-2"></i>Historial local de sincronización</h5>
        <span class="small text-muted"><?= $total ?> eventos registrados</span>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-3">
          Aquí se registra todo lo sucedido en <strong>este webservice</strong>: subida de control de
          parámetros, detección de archivos nuevos, respuesta de la nube y transiciones de archivos.
          El historial de la <strong>nube</strong> se consulta en el módulo Control de Parámetros
          (pestaña <em>Historial de Sincronización</em>).
        </p>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Dirección</th>
                <th>Registros</th>
                <th>Estado</th>
                <th>Mensaje</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="7" class="text-center text-muted py-3">Sin eventos registrados.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td class="text-muted"><?= (int)$l['id'] ?></td>
                  <td class="f-12 text-nowrap"><?= htmlspecialchars($l['creado_en'] ?? '') ?></td>
                  <td><span class="badge badge-light-primary"><?= htmlspecialchars($l['tipo'] ?? '') ?></span></td>
                  <td><span class="badge badge-light-secondary"><?= htmlspecialchars($l['direccion'] ?? '') ?></span></td>
                  <td class="text-center"><?= (int)($l['registros'] ?? 0) ?></td>
                  <td>
                    <?php if (($l['estado'] ?? '') === 'ok'): ?>
                      <span class="badge badge-light-success">OK</span>
                    <?php else: ?>
                      <span class="badge badge-light-danger"><?= htmlspecialchars($l['estado'] ?? '') ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="f-12"><?= htmlspecialchars($l['mensaje'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <nav class="mt-3">
          <ul class="pagination pagination-sm justify-content-center mb-0">
            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="index.php?page=sync_log&pagina=<?= $pagina - 1 ?>">&laquo;</a>
            </li>
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
              <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
                <a class="page-link" href="index.php?page=sync_log&pagina=<?= $p ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
              <a class="page-link" href="index.php?page=sync_log&pagina=<?= $pagina + 1 ?>">&raquo;</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require APP_PATH . '/views/layout/app_end.php'; ?>