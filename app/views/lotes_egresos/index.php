<?php
// app/views/lotes_egresos/index.php
require APP_PATH . '/views/layout/app_start.php';

// Códigos ya en disco (para marcar "Descargado" en pendientes)
$localSet = [];
foreach ($lotesLocales as $l) {
    $localSet[$l['codigo']] = true;
}
?>
<style>
  .lotes-estructura pre {
    background: #1e3a5f !important;
    color: #fff !important;
    border: 0;
    font-size: .78rem;
    line-height: 1.45;
  }
  .lotes-estructura pre .cmt { color: #9fd0ff; }
  .lote-fila:hover td { background: #f0f6ff; }
</style>

<!-- TOP: lotes ya en el equipo local -->
<div class="row mb-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fa fa-folder-open me-2"></i>Lotes descargados (local)</h5>
        <span class="badge bg-secondary"><?= count($lotesLocales) ?></span>
      </div>
      <div class="card-body py-2">
        <?php if (empty($lotesLocales)): ?>
          <div class="text-center text-muted py-3 mb-0">
            <i class="fa fa-inbox fa-lg me-1"></i> Aún no hay lotes en el equipo local.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Lote</th>
                  <th>Bajado</th>
                  <th>Adm.</th>
                  <th>Archivos</th>
                  <th>JSON</th>
                  <th>Estado</th>
                  <th>Detalle</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($lotesLocales as $l): ?>
                <tr class="lote-fila" data-codigo="<?= htmlspecialchars($l['codigo'], ENT_QUOTES) ?>" style="cursor:pointer;" title="Ver contenido del lote">
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
                  <td><span class="badge bg-info text-dark"><i class="fa fa-check me-1"></i>Descargado</span></td>
                  <td class="small">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-ver-lote" data-codigo="<?= htmlspecialchars($l['codigo'], ENT_QUOTES) ?>">
                      <i class="fa fa-folder-open"></i> Ver
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mt-2 mb-0">
            Clic en una fila o en <strong>Ver</strong> para explorar carpetas y archivos.
            Orden: más reciente primero. Ruta relativa:
            <code>public/uploads/empresas/&lt;code&gt;/archivos/lotes_egresos/</code>
            (la ruta absoluta se ve en el modal; el ERP recoge la data de ahí).
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MEDIO: pendientes (izq) + estructura (der) -->
<div class="row mb-3">
  <div class="col-xl-7 col-lg-6">
    <div class="card h-100">
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
                  <th>Local</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($pendientesNube as $p):
                $cod = (string)($p['codigo_lote'] ?? '');
                $enLocal = isset($localSet[$cod]);
              ?>
                <tr>
                  <td><code class="small"><?= htmlspecialchars($cod) ?></code></td>
                  <td class="small"><?= htmlspecialchars(substr((string)($p['enviado_local_at'] ?? ''), 0, 16)) ?></td>
                  <td class="small"><?= (int)($p['total_admisiones'] ?? 0) ?></td>
                  <td>
                    <?php if ($enLocal): ?>
                      <span class="badge bg-info text-dark">Descargado</span>
                    <?php else: ?>
                      <span class="badge bg-light text-dark border">No</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <form method="post" action="index.php?page=lotes_egresos_traer" class="d-inline">
                      <input type="hidden" name="codigo_lote" value="<?= htmlspecialchars($cod, ENT_QUOTES) ?>">
                      <button type="submit" class="btn btn-sm <?= $enLocal ? 'btn-outline-secondary' : 'btn-outline-primary' ?> py-0 px-2"
                              title="<?= $enLocal ? 'Ya está en disco: re-descarga solo JSON (adjuntos intactos)' : 'Descargar lote' ?>">
                        <i class="fa fa-download"></i> <?= $enLocal ? 'Re-traer' : 'Traer' ?>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mt-2 mb-0">
            Solo lotes con estado <strong>Enviado</strong> en la nube. Tras confirmar pasan a <strong>Recibido</strong> y salen de esta lista.
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-5 col-lg-6">
    <div class="card h-100 lotes-estructura">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-sitemap me-2"></i>Estructura por lote <span class="badge bg-light text-dark ms-1" style="font-size:.65rem;">referencia</span></h5>
      </div>
      <div class="card-body">
        <p class="small mb-2" style="color:#1e3a5f;">
          Cómo se organizan los archivos en disco al descargar un lote.
          Es solo consulta: el contenido real se ve con <strong>Ver</strong> en la lista de arriba.
        </p>
<pre class="mb-0 p-3 rounded">&lt;codigo_lote&gt;/
├── consolidado_&lt;codigo_lote&gt;.json   <span class="cmt">// datos completos de cada admisión</span>
├── manifiesto_&lt;codigo_lote&gt;.json    <span class="cmt">// índice del lote</span>
├── admision_&lt;id&gt;/                   <span class="cmt">// id = clave interna BD (no es el #)</span>
│   ├── adjunto1.pdf                 <span class="cmt">// PDFs, imágenes, etc.</span>
│   └── adjunto2.jpg
└── admision_&lt;id2&gt;/
    └── …                            <span class="cmt">// en Ver: admision_8 → #2 · nombre</span></pre>
      </div>
    </div>
  </div>
</div>

<!-- ABAJO: formulario traer por código -->
<div class="row mb-3">
  <div class="col-xl-7 col-lg-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-download me-2"></i>Traer lote de la nube</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($msg)): ?>
          <div class="alert alert-<?= (strpos($msg, 'Error') !== false || strpos($msg, 'fall') !== false) ? 'danger' : 'success' ?> py-2"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <p class="small text-muted mb-3">
          Descarga (o re-descarga) un lote por código. Si ya está en disco se avisa en el mensaje:
          los JSON se actualizan y los adjuntos existentes no se vuelven a bajar.
        </p>

        <form method="post" action="index.php?page=lotes_egresos_traer" class="mb-3">
          <div class="input-group">
            <input type="text" class="form-control" name="codigo_lote" placeholder="Ej: 20260923204831_1013_0001_L1" required>
            <button type="submit" class="btn btn-primary"><i class="fa fa-cloud-download me-1"></i>Traer / Re-traer</button>
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
  </div>
</div>

<!-- Modal: explorador de carpetas del lote -->
<div class="modal fade" id="modalExplorarLote" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-folder-open me-2"></i><span id="explorarTitulo">Lote</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="explorarMeta" class="small text-muted mb-2"></div>
        <div class="mb-2">
          <div class="small fw-semibold" style="color:#1e3a5f;">Ruta local (para el ERP)</div>
          <div class="input-group input-group-sm">
            <input type="text" class="form-control font-monospace" id="explorarRutaLocal" readonly
                   value="" title="Ruta absoluta en este equipo" style="font-size:.75rem;">
            <button type="button" class="btn btn-outline-secondary" id="btnCopiarRuta"
                    title="Copiar ruta al portapapeles"><i class="fa fa-copy"></i> Copiar</button>
          </div>
        </div>
        <nav aria-label="migas" class="mb-2">
          <ol class="breadcrumb mb-0 small" id="explorarMigas"></ol>
        </nav>
        <div id="explorarCuerpo" class="border rounded p-2" style="min-height:180px;">
          <div class="text-muted text-center py-4">Cargando…</div>
        </div>
        <p class="small text-muted mt-2 mb-0">
          En carpetas <code>admision_&lt;id&gt;</code>, el número tras <code>_</code> es el
          <strong>id interno</strong> de la BD; al lado se muestra el <strong>correlativo #</strong>
          (el número de consulta que se ve en el sistema) y, si el manifiesto lo incluye, el paciente.
        </p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var _codigo = '';
  var _sub = '';
  var _urlBase = '';
  var _rutaLocal = '';

  function esc(s) {
    return (s === null || s === undefined) ? '' : String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function fmtTam(n) {
    if (n === null || n === undefined) return '—';
    n = Number(n) || 0;
    if (n < 1024) return n + ' B';
    if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
    return (n / 1048576).toFixed(2) + ' MB';
  }

  function icono(f) {
    if (f.tipo === 'dir') return '<i class="fa fa-folder text-warning me-2"></i>';
    var ext = (f.nombre.split('.').pop() || '').toLowerCase();
    if (ext === 'json') return '<i class="fa fa-file-code-o text-success me-2"></i>';
    if (ext === 'pdf') return '<i class="fa fa-file-pdf-o text-danger me-2"></i>';
    if (['jpg','jpeg','png','gif','webp','bmp'].indexOf(ext) >= 0) return '<i class="fa fa-file-image-o text-info me-2"></i>';
    if (['doc','docx'].indexOf(ext) >= 0) return '<i class="fa fa-file-word-o text-primary me-2"></i>';
    if (['xls','xlsx','csv'].indexOf(ext) >= 0) return '<i class="fa fa-file-excel-o text-success me-2"></i>';
    return '<i class="fa fa-file-o text-secondary me-2"></i>';
  }

  function urlArchivo(ruta) {
    return _urlBase + ruta.split('/').map(encodeURIComponent).join('/');
  }

  function pintarMigas() {
    var h = '<li class="breadcrumb-item"><a href="#" class="explorar-root">' + esc(_codigo) + '</a></li>';
    if (_sub) {
      var partes = _sub.split('/');
      var acc = '';
      partes.forEach(function (p, i) {
        acc = acc ? acc + '/' + p : p;
        var last = (i === partes.length - 1);
        h += '<li class="breadcrumb-item' + (last ? ' active' : '') + '">'
          + (last ? esc(p) : '<a href="#" class="explorar-nav" data-sub="' + esc(acc) + '">' + esc(p) + '</a>')
          + '</li>';
      });
    }
    document.getElementById('explorarMigas').innerHTML = h;
  }

  function etiquetaAdm(f) {
    // admision_8 → mostrar también correlativo #2 y nombre si el manifiesto lo trae
    if (f.tipo !== 'dir' || f.admision_id === undefined || f.admision_id === null) {
      return '<span class="fw-semibold">' + esc(f.nombre) + '/</span>';
    }
    var a = f.admision || {};
    var partes = [];
    if (a.correlativo !== null && a.correlativo !== undefined && a.correlativo !== '') {
      partes.push('#' + esc(String(a.correlativo)));
    }
    if (a.paciente_nombre) {
      partes.push(esc(a.paciente_nombre));
    }
    var badge = partes.length
      ? ' <span class="badge bg-primary text-white">' + partes.join(' · ') + '</span>'
      : '';
    var tip = 'id interno=' + f.admision_id
      + (a.correlativo ? ', correlativo=#' + a.correlativo : '');
    return '<span class="fw-semibold" title="' + tip + '">' + esc(f.nombre) + '/</span>' + badge;
  }

  function pintarItemRuta(f) {
    // Ruta absoluta bajo el nombre (la usa el ERP para recoger la data)
    if (!f.ruta_local) return '';
    return '<div class="small text-muted font-monospace" style="font-size:.7rem;word-break:break-all;">'
      + esc(f.ruta_local) + '</div>';
  }

  function cargar(codigo, sub) {
    _codigo = codigo;
    _sub = sub || '';
    document.getElementById('explorarTitulo').textContent = codigo + (_sub ? ' / ' + _sub : '');
    pintarMigas();
    var box = document.getElementById('explorarCuerpo');
    box.innerHTML = '<div class="text-muted text-center py-4">Cargando…</div>';
    var rutaInput = document.getElementById('explorarRutaLocal');
    if (rutaInput) rutaInput.value = '';

    fetch('index.php?page=lotes_egresos_explorar&codigo=' + encodeURIComponent(codigo) + '&sub=' + encodeURIComponent(_sub))
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) {
          box.innerHTML = '<div class="alert alert-warning py-2 mb-0">' + esc((res && res.msg) || 'No se pudo cargar.') + '</div>';
          return;
        }
        _urlBase = res.url_base || '';
        _rutaLocal = res.ruta_local || '';
        if (rutaInput) {
          // Al navegar subcarpetas, completar ruta local = base + sub
          rutaInput.value = (_rutaLocal || '')
            + (res.sub ? res.sub.split('/').join('\\') + '\\' : '');
        }
        var metaEl = document.getElementById('explorarMeta');
        if (res.meta && res.meta.total_admisiones) {
          metaEl.innerHTML = 'Empresa: <strong>' + esc(res.meta.empresa || '—') + '</strong>'
            + ' · Admisiones: <strong>' + res.meta.total_admisiones + '</strong>'
            + ' · Docs manifiesto: <strong>' + res.meta.total_documentos + '</strong>'
            + (res.meta.fecha ? ' · ' + esc(res.meta.fecha) : '');
        } else {
          metaEl.textContent = '';
        }

        if (!res.items || !res.items.length) {
          box.innerHTML = '<div class="text-center text-muted py-4 mb-0">Carpeta vacía.</div>';
          return;
        }

        var h = '<div class="list-group list-group-flush">';
        if (_sub) {
          var padre = _sub.split('/').slice(0, -1).join('/');
          h += '<a href="#" class="list-group-item list-group-item-action explorar-nav" data-sub="' + esc(padre) + '">'
            + '<i class="fa fa-level-up text-muted me-2"></i>.. (subir)</a>';
        }
        res.items.forEach(function (f) {
          if (f.tipo === 'dir') {
            h += '<a href="#" class="list-group-item list-group-item-action explorar-nav" data-sub="' + esc(f.ruta) + '">'
              + icono(f) + etiquetaAdm(f)
              + '<span class="text-muted small float-end">' + esc(f.mtime) + '</span>'
              + pintarItemRuta(f) + '</a>';
          } else {
            h += '<div class="list-group-item d-flex flex-column py-1">'
              + '<div class="d-flex align-items-center">'
              + icono(f) + '<span class="flex-grow-1">' + esc(f.nombre) + '</span>'
              + '<span class="text-muted small me-3">' + fmtTam(f.tam) + '</span>'
              + '<a class="btn btn-sm btn-outline-primary py-0 px-2" href="' + esc(urlArchivo(f.ruta)) + '" target="_blank" rel="noopener" title="Abrir en pestaña"><i class="fa fa-external-link"></i></a>'
              + '</div>'
              + pintarItemRuta(f)
              + '</div>';
          }
        });
        h += '</div>';
        box.innerHTML = h;
      })
      .catch(function () {
        box.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error de conexión.</div>';
      });
  }

  function abrir(codigo) {
    var modalEl = document.getElementById('modalExplorarLote');
    cargar(codigo, '');
    try {
      if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        return;
      }
    } catch (e) {}
    if (window.jQuery && jQuery.fn.modal) { jQuery(modalEl).modal('show'); return; }
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
  }

  document.addEventListener('click', function (e) {
    var fila = e.target.closest('.lote-fila, .btn-ver-lote');
    if (fila) {
      var cod = fila.getAttribute('data-codigo');
      if (cod) {
        e.preventDefault();
        abrir(cod);
        return;
      }
    }
    var nav = e.target.closest('.explorar-nav, .explorar-root');
    if (nav) {
      e.preventDefault();
      var sub = nav.classList.contains('explorar-root') ? '' : (nav.getAttribute('data-sub') || '');
      cargar(_codigo, sub);
    }
    var copiar = e.target.closest('#btnCopiarRuta');
    if (copiar) {
      e.preventDefault();
      var inp = document.getElementById('explorarRutaLocal');
      if (!inp || !inp.value) return;
      inp.select();
      inp.setSelectionRange(0, 999999);
      var ok = false;
      try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
      if (ok) {
        copiar.innerHTML = '<i class="fa fa-check"></i> Copiado';
        setTimeout(function () {
          copiar.innerHTML = '<i class="fa fa-copy"></i> Copiar';
        }, 1500);
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(inp.value).catch(function () {});
      }
    }
  });
})();
</script>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
