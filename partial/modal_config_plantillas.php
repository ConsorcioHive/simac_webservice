<div class="modal fade" id="modalConfigPlantillas" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:15px;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="fa fa-th-large text-primary me-2"></i>Configuración de Plantillas de Historia Clínica</h5>
                    <p class="text-muted small mb-0 mt-1">Selecciona las plantillas del sistema que estarán disponibles al crear consultas. Solo las activas aparecerán en el módulo.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="spinnerConfigPlantillas">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>
                    <p class="text-muted mt-2 mb-0">Cargando plantillas...</p>
                </div>
                <div id="configPlantillasContent" style="display:none;">
                <div class="row g-4">
                    <!-- Panel izquierdo: disponibles -->
                    <div class="col-5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0">Plantillas Disponibles</h6>
                            <span class="badge bg-primary" id="disponiblesCount">0</span>
                        </div>
                        <div class="border rounded-3 p-2" style="height:300px;overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0" id="tablaDisponibles">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px">
                                            <input type="checkbox" id="checkAllDisponibles">
                                        </th>
                                        <th>Plantilla</th>
                                        <th style="width:80px">Campos</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <p class="text-muted text-center py-4 mb-0 d-none" id="emptyDisponibles">No hay plantillas del sistema disponibles</p>
                        </div>
                    </div>

                    <!-- Botones centrales -->
                    <div class="col-2 d-flex flex-column align-items-center justify-content-center gap-3">
                        <button type="button" class="btn btn-primary rounded-pill px-3" id="btnAgregar" title="Agregar seleccionadas">
                            Agregar <i class="fa fa-arrow-right ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-3" id="btnQuitar" title="Quitar seleccionadas">
                            <i class="fa fa-arrow-left me-1"></i> Quitar
                        </button>
                    </div>

                    <!-- Panel derecho: activas -->
                    <div class="col-5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0">Activas en mi Clínica</h6>
                            <span class="badge bg-success" id="activasCount">0</span>
                        </div>
                        <div class="border rounded-3 p-2" style="height:300px;overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0" id="tablaActivas">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px">
                                            <input type="checkbox" id="checkAllActivas">
                                        </th>
                                        <th>Plantilla</th>
                                        <th style="width:80px">Campos</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <p class="text-muted text-center py-4 mb-0" id="emptyActivas">Ninguna plantilla seleccionada</p>
                        </div>
                    </div>
                </div>

                <!-- Banner advertencia -->
                <div class="alert alert-warning d-flex align-items-center gap-2 mt-4 mb-0 py-2 px-3">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>Si no configura ninguna plantilla, no podrá registrar consultas en el módulo nuevo.</span>
                </div>

                <!-- No preguntar de nuevo -->
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="chkNoPreguntar">
                    <label class="form-check-label" for="chkNoPreguntar">
                        No preguntar de nuevo
                    </label>
                </div>
                </div><!-- /configPlantillasContent -->
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm rounded-pill px-4" id="btnGuardarConfigPlantillas"><i class="fa fa-save me-1"></i>Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var plantillasDisponibles = [];
    var plantillasActivas = [];

    function cargarPlantillasParaModal() {
        document.getElementById('spinnerConfigPlantillas').style.display = '';
        document.getElementById('configPlantillasContent').style.display = 'none';
        fetch('index.php?page=plantillas_config_data_ajax')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.ok) return;
                plantillasDisponibles = res.disponibles || [];
                plantillasActivas = res.activas || [];
                renderDisponibles();
                renderActivas();
                document.getElementById('spinnerConfigPlantillas').style.display = 'none';
                document.getElementById('configPlantillasContent').style.display = '';
            });
    }

    function renderDisponibles() {
        var tbody = document.querySelector('#tablaDisponibles tbody');
        var empty = document.getElementById('emptyDisponibles');
        tbody.innerHTML = '';

        if (!plantillasDisponibles.length) {
            empty.classList.remove('d-none');
            document.getElementById('disponiblesCount').textContent = '0';
            return;
        }
        empty.classList.add('d-none');
        document.getElementById('disponiblesCount').textContent = plantillasDisponibles.length;

        plantillasDisponibles.forEach(function(p) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td><input type="checkbox" class="check-disponible" value="' + p.id + '"></td>' +
                '<td>' + p.nombre + '</td>' +
                '<td>' + (p.total_campos || 0) + '</td>';
            tbody.appendChild(tr);
        });
    }

    function renderActivas() {
        var tbody = document.querySelector('#tablaActivas tbody');
        var empty = document.getElementById('emptyActivas');
        tbody.innerHTML = '';

        if (!plantillasActivas.length) {
            empty.classList.remove('d-none');
            document.getElementById('activasCount').textContent = '0';
            return;
        }
        empty.classList.add('d-none');
        document.getElementById('activasCount').textContent = plantillasActivas.length;

        plantillasActivas.forEach(function(p) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td><input type="checkbox" class="check-activa" value="' + p.id + '"></td>' +
                '<td>' + p.nombre + '</td>' +
                '<td>' + (p.total_campos || 0) + '</td>';
            tbody.appendChild(tr);
        });
    }

    function moverSeleccionadas(origen, destino, checkboxClass) {
        var seleccionadas = [];
        document.querySelectorAll('.' + checkboxClass + ':checked').forEach(function(cb) {
            var id = parseInt(cb.value);
            var idx = origen.findIndex(function(p) { return p.id === id; });
            if (idx !== -1) {
                seleccionadas.push(origen[idx]);
                origen.splice(idx, 1);
            }
        });
        seleccionadas.forEach(function(p) { destino.push(p); });
        renderDisponibles();
        renderActivas();
    }

    document.getElementById('btnAgregar').addEventListener('click', function() {
        moverSeleccionadas(plantillasDisponibles, plantillasActivas, 'check-disponible');
    });

    document.getElementById('btnQuitar').addEventListener('click', function() {
        moverSeleccionadas(plantillasActivas, plantillasDisponibles, 'check-activa');
    });

    document.getElementById('checkAllDisponibles').addEventListener('change', function() {
        document.querySelectorAll('.check-disponible').forEach(function(cb) { cb.checked = this.checked; }, this);
    });

    document.getElementById('checkAllActivas').addEventListener('change', function() {
        document.querySelectorAll('.check-activa').forEach(function(cb) { cb.checked = this.checked; }, this);
    });

    document.getElementById('btnGuardarConfigPlantillas').addEventListener('click', function() {
        var ids = plantillasActivas.map(function(p) { return p.id; });
        var omitir = document.getElementById('chkNoPreguntar').checked ? 1 : 0;

        if (ids.length === 0 && !omitir) {
            alert('Debe activar al menos una plantilla o marcar "No preguntar de nuevo" para continuar.');
            return;
        }

        var formData = new FormData();
        formData.append('omitir', omitir);
        ids.forEach(function(id) { formData.append('plantilla_ids[]', id); });

        fetch('index.php?page=plantillas_config_guardar_ajax', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.ok) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalConfigPlantillas'));
                    if (modal) modal.hide();
                    if (typeof onPlantillasConfigGuardadas === 'function') {
                        onPlantillasConfigGuardadas(res);
                    }
                } else {
                    alert(res.msg);
                }
            });
    });

    // Cargar datos al abrir el modal
    document.getElementById('modalConfigPlantillas').addEventListener('show.bs.modal', function(e) {
        var btn = e.relatedTarget;
        var context = btn ? btn.getAttribute('data-context') : null;
        var isFromPage = context === 'page';

        document.getElementById('modalConfigPlantillas').querySelector('.alert-warning').style.display = isFromPage ? 'none' : '';
        document.getElementById('modalConfigPlantillas').querySelector('.form-check').style.display = isFromPage ? 'none' : '';
    });

    document.getElementById('modalConfigPlantillas').addEventListener('shown.bs.modal', function() {
        cargarPlantillasParaModal();
    });
});
</script>
