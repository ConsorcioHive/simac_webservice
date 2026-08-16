var vacunasTable = null;
var modalVacunaEl = null;
var modalVacuna = null;
var _catalogoCache = [];
var _consultasCache = [];

function _ensureDataTables(cb) {
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) { cb(); return; }
    var poll = setInterval(function () {
        if (typeof jQuery === 'undefined') return;
        clearInterval(poll);
        if (jQuery.fn.DataTable) { cb(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js';
        s.onload = cb;
        document.head.appendChild(s);
        var css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css';
        document.head.appendChild(css);
    }, 30);
}

function _obtenerConsultaActiva() {
    var active = document.querySelector('#historialList .list-group-item.active');
    if (!active) return 0;
    var target = active.getAttribute('data-target');
    if (!target) return 0;
    return parseInt(target.replace('#detalle', ''), 10) || 0;
}

function _cargarDatosModal(pacienteId, savedCatalogo, savedConsulta) {
    var selCat = document.getElementById('catalogo_vacuna_id');
    var selCon = document.getElementById('consulta_id');

    if (selCat) {
        selCat.innerHTML = '<option value="">Seleccione vacuna...</option>';
        _catalogoCache.forEach(function (v) {
            var opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = v.nombre + (v.descripcion ? ' (' + v.descripcion + ')' : '');
            selCat.appendChild(opt);
        });
        if (savedCatalogo) selCat.value = savedCatalogo;
    }

    if (selCon) {
        selCon.innerHTML = '<option value="">Sin vincular (vacuna independiente)</option>';
        var pick = savedConsulta || _obtenerConsultaActiva() || 0;
        _consultasCache.forEach(function (c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = 'Consulta #' + c.id + ' — ' + c.fecha_consulta + (c.motivo_consulta ? ' (' + c.motivo_consulta + ')' : '');
            if (c.id == pick) opt.selected = true;
            selCon.appendChild(opt);
        });
        if (!selCon.value && savedConsulta) selCon.value = savedConsulta;
    }
}

function initVacunasModule(pacienteId) {
    if (!pacienteId) return;

    modalVacunaEl = document.getElementById('modalVacuna');
    if (modalVacunaEl) modalVacuna = new bootstrap.Modal(modalVacunaEl);

    if (modalVacunaEl) {
        modalVacunaEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('vacunaId').value = 0;
        });
    }

    document.getElementById('formVacuna')?.addEventListener('submit', function (e) {
        e.preventDefault();
        guardarVacuna(pacienteId);
    });

    Promise.all([
        fetch('index.php?page=catalogo_vacunas_ajax').then(function (r) { return r.json(); }),
        fetch('index.php?page=consulta_buscar_por_paciente_ajax&paciente_id=' + pacienteId).then(function (r) { return r.json(); })
    ]).then(function (results) {

        document.getElementById('btnNuevaVacuna')?.addEventListener('click', function () {
            abrirFormVacuna(pacienteId);
        });
        _catalogoCache = Array.isArray(results[0]) ? results[0] : [];
        _consultasCache = Array.isArray(results[1]) ? results[1] : [];

        _ensureDataTables(function () {
            if ($.fn.DataTable.isDataTable('#tablaVacunas')) {
                $('#tablaVacunas').DataTable().destroy();
            }
            vacunasTable = jQuery('#tablaVacunas').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'index.php?page=listar_vacunas_ajax&paciente_id=' + pacienteId,
                    type: 'GET'
                },
                columns: [
                    { data: 'fecha_aplicacion', title: 'Fecha' },
                    { data: 'vacuna_nombre', title: 'Vacuna' },
                    { data: 'dosis', title: 'Dosis' },
                    { data: 'marca', title: 'Marca' },
                    { data: 'lote', title: 'Lote' },
                    { data: 'proximo_refuerzo', title: 'Próximo Refuerzo' },
                    {
                        data: null,
                        title: 'Consulta',
                        orderable: false,
                        searchable: false,
                        render: function (row) {
                            if (!row.consulta_id) return '<span class="text-muted small">No vinculada</span>';
                            var activa = _obtenerConsultaActiva();
                            var irBtn = (row.consulta_id != activa)
                                ? '<button class="btn btn-link btn-xs p-0 ms-1 text-decoration-none" style="color:#1e3a5f;" onclick="irAConsulta(' + row.consulta_id + ')" title="Ir a esta consulta"><i class="fa fa-external-link"></i></button>'
                                : '';
                            var fecha = row.consulta_fecha || '?';
                            return '<span style="color:#495057;">#' + row.consulta_id + ' ' + fecha + irBtn + '</span>';
                        }
                    },
                    {
                        data: null,
                        title: 'Acciones',
                        orderable: false,
                        searchable: false,
                        className: 'text-nowrap',
                        render: function (row) {
                            return '<div class="d-flex gap-1">' +
                                '<button class="btn btn-primary btn-xs py-0 px-1 lh-1 rounded-pill" onclick="editarVacuna(' + row.id + ')" title="Editar"><i class="fa fa-edit"></i></button>' +
                                '<button class="btn btn-danger btn-xs py-0 px-1 lh-1 rounded-pill" onclick="eliminarVacuna(' + row.id + ')" title="Eliminar"><i class="fa fa-trash"></i></button>' +
                                '</div>';
                        }
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                responsive: true,
                drawCallback: function () {
                    if (typeof feather !== 'undefined') feather.replace();
                }
            });
        });
    }).catch(function (e) {
        console.warn('Error cargando datos del m\u00f3dulo vacunas', e);
    });
}

function abrirFormVacuna(pacienteId, data) {
    document.getElementById('vacunaId').value = data ? data.id : 0;
    document.getElementById('pacienteIdInput').value = pacienteId;

    if (data) {
        document.getElementById('fecha_aplicacion').value = data.fecha_aplicacion;
        document.getElementById('marca').value = data.marca || '';
        document.getElementById('lote').value = data.lote || '';
        document.getElementById('dosis').value = data.dosis || '';
        document.getElementById('proximo_refuerzo').value = data.proximo_refuerzo || '';
        document.getElementById('notas').value = data.notas || '';
        _cargarDatosModal(pacienteId, data.catalogo_vacuna_id, data.consulta_id);
    } else {
        document.getElementById('formVacuna').reset();
        document.getElementById('vacunaId').value = 0;
        document.getElementById('pacienteIdInput').value = pacienteId;
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_aplicacion').value = today;
        _cargarDatosModal(pacienteId, null, null);
    }

    if (modalVacuna) modalVacuna.show();
}

function editarVacuna(id) {
    fetch('index.php?page=obtener_vacuna_ajax&id=' + id)
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok) {
                abrirFormVacuna(0, res.data);
            } else {
                alert(res.msg);
            }
        })
        .catch(function (e) { alert('Error: ' + e.message); });
}

function guardarVacuna(pacienteId) {
    var btn = document.getElementById('btnGuardarVacuna');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Guardando...';

    var form = document.getElementById('formVacuna');
    var formData = new FormData(form);
    formData.append('paciente_id', pacienteId);

    fetch('index.php?page=guardar_vacuna_ajax', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok) {
                if (modalVacuna) modalVacuna.hide();
                if (vacunasTable) vacunasTable.ajax.reload(null, false);
            } else {
                alert(res.msg);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i> Guardar';
            }
        })
        .catch(function (e) {
            alert('Error: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save me-1"></i> Guardar';
        });
}

function eliminarVacuna(id) {
    if (!confirm('¿Está seguro de eliminar esta vacuna?')) return;
    var form = new FormData();
    form.append('id', id);
    fetch('index.php?page=eliminar_vacuna_ajax', { method: 'POST', body: form })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok) {
                if (vacunasTable) vacunasTable.ajax.reload(null, false);
            } else {
                alert(res.msg);
            }
        })
        .catch(function (e) { alert('Error: ' + e.message); });
}

function irAConsulta(consultaId) {
    document.getElementById('tab-portada-tab')?.click();
    var target = document.querySelector('#historialList .list-group-item[data-target="#detalle' + consultaId + '"]');
    if (target) {
        target.click();
        target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// ── Recetas / Tratamientos ──

var recetasTable = null;

function initRecetasModule(pacienteId) {
    if (!pacienteId) return;
    _ensureDataTables(function () {
        if ($.fn.DataTable.isDataTable('#tablaRecetas')) {
            $('#tablaRecetas').DataTable().destroy();
        }
        recetasTable = jQuery('#tablaRecetas').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'index.php?page=listar_recetas_ajax&paciente_id=' + pacienteId,
                type: 'GET'
            },
            columns: [
                {
                    data: null,
                    title: 'Fecha / Consulta',
                    className: 'text-nowrap',
                    render: function (row) {
                        var activa = _obtenerConsultaActiva();
                        var irBtn = (row.consulta_id_ref && row.consulta_id_ref != activa)
                            ? '<button class="btn btn-link btn-xs p-0 ms-1 text-decoration-none" style="color:#1e3a5f;" onclick="irAConsulta(' + row.consulta_id_ref + ')" title="Ir a esta consulta"><i class="fa fa-external-link"></i></button>'
                            : '';
                        return '<div><i class="fa fa-calendar me-1 text-muted"></i>' + row.fecha_consulta + '</div>' +
                            '<div class="small text-muted">#' + row.consulta_id_ref + irBtn + '</div>';
                    }
                },
                {
                    data: null,
                    title: 'Medicamento',
                    render: function (row) {
                        var sub = [];
                        if (row.dosis) sub.push(row.dosis);
                        if (row.cantidad) sub.push(row.cantidad);
                        return '<div><i class="fa fa-medkit me-1 text-muted"></i>' + (row.nombre_producto || '') + '</div>' +
                            (sub.length ? '<div class="small text-muted">' + sub.join(' - ') + '</div>' : '');
                    }
                },
                {
                    data: 'indicaciones',
                    title: 'Indicaciones',
                    className: 'text-wrap'
                }
            ],
            order: [[0, 'desc']],
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            responsive: true,
            columnDefs: [
                { width: '40%', targets: 2 }
            ],
            drawCallback: function () {
                if (typeof feather !== 'undefined') feather.replace();
            }
        });
    });
}
