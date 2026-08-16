<?php
// app/views/dashboard/index.php
// Dashboard administrativo (clonado del layout Cuba de SIMAC).
require APP_PATH . '/views/layout/app_start.php';
?>

<!-- KPIs -->
<div class="row">
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="card small-widget hover-up">
      <div class="card-body bg-primary text-white" style="border-radius: 10px;">
        <span class="text-white fw-bold mb-2 d-block fs-6">Usuarios</span>
        <div class="d-flex align-items-end gap-1">
          <h4 class="fw-bolder fs-3 text-white m-0"><?= $totalUsuarios ?? count($usuarios) ?></h4>
        </div>
        <div class="bg-gradient"><i class="fa fa-users fa-2x" style="opacity: 0.3; color: white;"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="card small-widget hover-up">
      <div class="card-body bg-success text-white" style="border-radius: 10px;">
        <span class="text-white fw-bold mb-2 d-block fs-6">Sincronizaciones</span>
        <div class="d-flex align-items-end gap-1">
          <h4 class="fw-bolder fs-3 text-white m-0"><?= (int)$totalSync ?></h4>
        </div>
        <div class="bg-gradient"><i class="fa fa-refresh fa-2x" style="opacity: 0.3; color: white;"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="card small-widget hover-up">
      <div class="card-body bg-<?= $conectado ? 'info' : 'secondary' ?> text-white" style="border-radius: 10px;">
        <span class="text-white fw-bold mb-2 d-block fs-6">Conexión SIMAC</span>
        <div class="d-flex align-items-end gap-1">
          <h4 class="fw-bolder fs-3 text-white m-0"><?= $conectado ? 'Activa' : 'Pendiente' ?></h4>
        </div>
        <div class="bg-gradient"><i class="fa fa-cloud fa-2x" style="opacity: 0.3; color: white;"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="card small-widget hover-up">
      <div class="card-body bg-warning text-white" style="border-radius: 10px;">
        <span class="text-white fw-bold mb-2 d-block fs-6">Última Sync</span>
        <div class="d-flex align-items-end gap-1">
          <h4 class="fw-bolder fs-3 text-white m-0"><?= $ultimoSync ? date('d/m', strtotime($ultimoSync)) : '—' ?></h4>
        </div>
        <div class="bg-gradient"><i class="fa fa-clock-o fa-2x" style="opacity: 0.3; color: white;"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header pb-0">
        <h5>Sincronizaciones (Últimos 12 meses)</h5>
      </div>
      <div class="card-body">
        <div id="chart-meses"></div>
      </div>
    </div>
  </div>
</div>

<!-- Tablas -->
<div class="row">
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header pb-0">
        <h5>Usuarios recientes</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($usuarios, 0, 10) as $u): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img class="img-fluid rounded-circle me-2" src="<?= BASE_URL ?>assets/images/dashboard/profile.png" width="35" height="35" style="object-fit: cover; border:1px solid #eee;">
                    <div>
                      <h6 class="mb-0"><?= htmlspecialchars($u['nombre'] . ' ' . ($u['apellido'] ?? '')) ?></h6>
                      <span class="f-light f-12"><?= htmlspecialchars($u['login']) ?></span>
                    </div>
                  </div>
                </td>
                <td><span class="badge badge-light-primary"><?= htmlspecialchars($u['rol']) ?></span></td>
                <td><span class="badge badge-light-<?= $u['estado'] === 'activo' ? 'success' : 'danger' ?>"><?= htmlspecialchars($u['estado']) ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($usuarios)): ?>
              <tr><td colspan="3" class="text-center text-muted">Sin usuarios.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header pb-0">
        <h5>Últimos registros de sincronización</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover">
            <thead><tr><th>Tipo</th><th>Dirección</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['tipo']) ?></td>
                <td><span class="badge badge-light-secondary"><?= htmlspecialchars($l['direccion']) ?></span></td>
                <td><span class="badge badge-light-<?= $l['estado'] === 'ok' ? 'success' : 'danger' ?>"><?= htmlspecialchars($l['estado']) ?></span></td>
                <td class="f-light f-12"><?= htmlspecialchars($l['creado_en']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($logs)): ?>
              <tr><td colspan="4" class="text-center text-muted">Sin registros.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>assets/js/chart/apex-chart/apex-chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  var mesesLabels = <?= json_encode($mesesLabels) ?>;
  var serieSync   = <?= json_encode($serieSync) ?>;
  var optionsMeses = {
    series: [{ name: 'Sincronizaciones', data: serieSync }],
    chart: { type: 'area', height: 350, toolbar: { show: false }, parentHeightOffset: 0 },
    colors: ['#1e3a5f'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: mesesLabels, tickPlacement: 'between' },
    yaxis: { min: 0, labels: { formatter: function(val) { return Math.round(val); } } },
    legend: { position: 'top' },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 100] } }
  };
  new ApexCharts(document.querySelector("#chart-meses"), optionsMeses).render();
});
</script>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
