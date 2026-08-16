var PACIENTE_ID = typeof window.PACIENTE_ID !== 'undefined' ? parseInt(window.PACIENTE_ID, 10) : 0;
var BASE_URL = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/';

// Estado local
var adjuntosData = [];
var indiceArrastrado = null;

function initAdjuntosModule(pacienteId) {
    PACIENTE_ID = pacienteId || PACIENTE_ID;
    if (!PACIENTE_ID) return;
    cargarAdjuntos();
}

// ==============================
// Carga inicial desde servidor
// ==============================
function cargarAdjuntos() {
    var grid = document.getElementById('grid-adjuntos');
    var empty = document.getElementById('adjuntos-empty');
    if (!grid) return;
    grid.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span class="text-muted small">Cargando archivos...</span></div>';
    fetch(BASE_URL + 'index.php?page=listar_adjuntos_ajax&paciente_id=' + PACIENTE_ID)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) { grid.innerHTML = ''; return; }
            adjuntosData = res.archivos || [];
            renderizarAdjuntos();
        })
        .catch(function() {
            grid.innerHTML = '<div class="col-12 text-center text-danger small py-3">Error al cargar archivos</div>';
        });
}

// ==============================
// Renderizar grid de cards
// ==============================
function renderizarAdjuntos() {
    var grid = document.getElementById('grid-adjuntos');
    var empty = document.getElementById('adjuntos-empty');
    var count = document.getElementById('adjuntos-count');
    if (!grid) return;
    grid.innerHTML = '';
    if (adjuntosData.length === 0) {
        if (empty) empty.classList.remove('d-none');
        if (count) count.textContent = '0 archivos';
        return;
    }
    if (empty) empty.classList.add('d-none');
    if (count) count.textContent = adjuntosData.length + ' archivo' + (adjuntosData.length !== 1 ? 's' : '');

    adjuntosData.forEach(function(item, index) {
        var col = document.createElement('div');
        col.className = 'col';

        var esPropio = item.origen === 'expediente';
        if (esPropio) {
            col.dataset.index = index;
            col.draggable = true;
            col.addEventListener('dragstart', handleDragStart);
            col.addEventListener('dragover', handleDragOver);
            col.addEventListener('drop', handleDrop);
            col.addEventListener('dragend', handleDragEnd);
        }

        var card = document.createElement('div');
        card.className = 'position-relative border rounded-3 overflow-hidden shadow-sm bg-white';
        card.style.cursor = 'pointer';

        // Thumbnail / icon
        var thumbContainer = document.createElement('div');
        thumbContainer.className = 'd-flex align-items-center justify-content-center w-100';
        thumbContainer.style.height = '120px';
        thumbContainer.style.background = '#f0f2f5';

        if (item.es_imagen) {
            var imgEl = document.createElement('img');
            imgEl.src = item.url;
            imgEl.alt = item.nombre_original;
            imgEl.style.maxWidth = '100%';
            imgEl.style.maxHeight = '100%';
            imgEl.style.objectFit = 'contain';
            thumbContainer.appendChild(imgEl);
        } else if (item.es_pdf) {
            thumbContainer.innerHTML = '<i class="fa fa-file-pdf-o" style="font-size:2.5rem;color:#dc3545;"></i>';
        } else if (item.es_video) {
            thumbContainer.innerHTML = '<i class="fa fa-file-video-o" style="font-size:2.5rem;color:#6f42c1;"></i>';
        } else {
            thumbContainer.innerHTML = '<i class="fa fa-file-o" style="font-size:2.5rem;color:#6c757d;"></i>';
        }
        card.appendChild(thumbContainer);

        // Click para preview
        card.addEventListener('click', function() {
            abrirPreviewAdjunto(index);
        });

        // Info bar
        var info = document.createElement('div');
        info.className = 'px-2 py-1 small';
        info.style.background = 'rgba(248,249,250,0.9)';
        info.style.fontSize = '0.75rem';
        info.style.lineHeight = '1.2';

        var nombre = document.createElement('div');
        nombre.className = 'text-truncate fw-medium';
        nombre.textContent = item.nombre_original;
        info.appendChild(nombre);

        if (esPropio && item.archivo_path) {
            var pathEl = document.createElement('div');
            pathEl.className = 'text-muted text-truncate';
            pathEl.style.fontSize = '0.6rem';
            pathEl.textContent = item.archivo_path;
            info.appendChild(pathEl);
        }

        if (item.origen === 'consulta') {
            var badge = document.createElement('span');
            badge.className = 'badge bg-info bg-opacity-10 text-info mt-1';
            badge.style.fontSize = '0.65rem';
            badge.textContent = 'Consulta #' + item.consulta_id;
            info.appendChild(badge);
        }

        // Barra inferior con flechas y X solo para items propios
        if (esPropio) {
            var bottomBar = document.createElement('div');
            bottomBar.className = 'd-flex align-items-center justify-content-between px-2 py-1 border-top';
            bottomBar.style.background = 'rgba(248,249,250,0.9)';

            var controls = document.createElement('div');
            controls.className = 'd-flex align-items-center gap-1';

            // Flecha izquierda
            var btnLeft = document.createElement('button');
            btnLeft.type = 'button';
            btnLeft.className = 'btn btn-sm p-0 border-0';
            btnLeft.innerHTML = '<i class="fa fa-chevron-left" style="font-size:0.7rem;color:#6c757d;"></i>';
            btnLeft.title = 'Mover izquierda';
            btnLeft.addEventListener('click', function(e) {
                e.stopPropagation();
                moverAdjunto(index, -1);
            });
            controls.appendChild(btnLeft);

            // Flecha derecha
            var btnRight = document.createElement('button');
            btnRight.type = 'button';
            btnRight.className = 'btn btn-sm p-0 border-0 ms-1';
            btnRight.innerHTML = '<i class="fa fa-chevron-right" style="font-size:0.7rem;color:#6c757d;"></i>';
            btnRight.title = 'Mover derecha';
            btnRight.addEventListener('click', function(e) {
                e.stopPropagation();
                moverAdjunto(index, 1);
            });
            controls.appendChild(btnRight);

            bottomBar.appendChild(controls);

            // Boton eliminar
            var btnDel = document.createElement('button');
            btnDel.type = 'button';
            btnDel.className = 'btn btn-sm p-0 border-0';
            btnDel.innerHTML = '<i class="fa fa-trash-o" style="font-size:0.8rem;color:#dc3545;"></i>';
            btnDel.title = 'Eliminar';
            btnDel.addEventListener('click', function(e) {
                e.stopPropagation();
                eliminarAdjunto(index);
            });
            bottomBar.appendChild(btnDel);

            card.appendChild(bottomBar);
        }

        col.appendChild(card);
        grid.appendChild(col);
    });
}

// ==============================
// Drag & Drop handlers
// ==============================
function handleDragStart(e) {
    indiceArrastrado = parseInt(e.currentTarget.dataset.index, 10);
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

function handleDrop(e) {
    e.preventDefault();
    var indiceDestino = parseInt(e.currentTarget.dataset.index, 10);
    if (Number.isNaN(indiceArrastrado) || Number.isNaN(indiceDestino)) return;
    if (indiceArrastrado === indiceDestino) return;

    var item = adjuntosData.splice(indiceArrastrado, 1)[0];
    adjuntosData.splice(indiceDestino, 0, item);
    indiceArrastrado = null;
    renderizarAdjuntos();
    guardarOrdenAdjuntos();
}

function handleDragEnd() {
    indiceArrastrado = null;
}

// ==============================
// Reordenar por flechas
// ==============================
function moverAdjunto(index, delta) {
    var newIndex = index + delta;
    if (newIndex < 0 || newIndex >= adjuntosData.length) return;
    var item = adjuntosData.splice(index, 1)[0];
    adjuntosData.splice(newIndex, 0, item);
    renderizarAdjuntos();
    guardarOrdenAdjuntos();
}

// ==============================
// Guardar orden en servidor
// ==============================
function guardarOrdenAdjuntos() {
    var items = [];
    adjuntosData.forEach(function(item, i) {
        if (item.origen === 'expediente') {
            items.push({ id: item.id, orden: i + 1 });
        }
    });
    var formData = new FormData();
    formData.append('paciente_id', PACIENTE_ID);
    formData.append('items', JSON.stringify(items));
    fetch(BASE_URL + 'index.php?page=reordenar_adjuntos_ajax', {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (!res.ok) console.error('Error al guardar orden');
    });
}

// ==============================
// Subir archivos (multi, batch de 5)
// ==============================
var archivosPendientes = [];
var subiendoAdjuntos = false;

function setupDropzoneAdjuntos() {
    var dropzone = document.getElementById('dropzone-adjuntos');
    var input = document.getElementById('input-adjuntos');
    if (!dropzone || !input) return;

    // Click en dropzone abre file input
    dropzone.addEventListener('click', function() {
        input.click();
    });

    // Drag & drop sobre dropzone
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.style.background = '#e9ecef';
        dropzone.style.borderColor = '#0099FF';
    });
    dropzone.addEventListener('dragleave', function() {
        dropzone.style.background = '#f8f9fa';
        dropzone.style.borderColor = '#1e3a5f';
    });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.style.background = '#f8f9fa';
        dropzone.style.borderColor = '#1e3a5f';
        if (e.dataTransfer.files.length) {
            procesarArchivosSeleccionados(e.dataTransfer.files);
        }
    });

    // Cambio en input file
    input.addEventListener('change', function() {
        if (this.files.length) {
            procesarArchivosSeleccionados(this.files);
        }
        this.value = '';
    });
}

function procesarArchivosSeleccionados(fileList) {
    var allowedExts = ['jpg','jpeg','png','gif','webp','pdf','mp4','avi','mov','mkv'];
    var maxSize = 10 * 1024 * 1024;

    for (var i = 0; i < fileList.length; i++) {
        var file = fileList[i];
        var ext = file.name.split('.').pop().toLowerCase();
        if (allowedExts.indexOf(ext) === -1) continue;
        if (file.size > maxSize) continue;
        archivosPendientes.push(file);
    }

    if (archivosPendientes.length > 0) {
        subirBatchAdjuntos();
    }
}

function subirBatchAdjuntos() {
    if (subiendoAdjuntos || archivosPendientes.length === 0) return;
    subiendoAdjuntos = true;

    var wrapper = document.getElementById('adjuntos-progress-wrapper');
    var bar = document.getElementById('adjuntos-progress-bar');
    var pct = document.getElementById('adjuntos-progress-pct');
    var text = document.getElementById('adjuntos-progress-text');
    if (wrapper) wrapper.classList.remove('d-none');

    var total = archivosPendientes.length;
    var subidos = 0;
    var batchSize = 5;

    function subirBatch() {
        var batch = archivosPendientes.splice(0, batchSize);
        if (batch.length === 0) {
            subiendoAdjuntos = false;
            if (wrapper) wrapper.classList.add('d-none');
            if (text) text.textContent = 'Subiendo archivos...';
            if (bar) bar.style.width = '0%';
            if (pct) pct.textContent = '0%';
            cargarAdjuntos();
            return;
        }

        var formData = new FormData();
        formData.append('paciente_id', PACIENTE_ID);
        batch.forEach(function(file, idx) {
            formData.append('archivos[' + idx + ']', file, file.name);
        });

        if (text) text.textContent = 'Subiendo ' + (subidos + 1) + '-' + Math.min(subidos + batch.length, total) + ' de ' + total + '...';

        fetch(BASE_URL + 'index.php?page=subir_adjuntos_ajax', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            subidos += batch.length;
            var pctVal = Math.round((subidos / total) * 100);
            if (bar) bar.style.width = pctVal + '%';
            if (pct) pct.textContent = pctVal + '%';
            subirBatch();
        })
        .catch(function() {
            subidos += batch.length;
            subirBatch();
        });
    }

    subirBatch();
}

// ==============================
// Eliminar archivo
// ==============================
function eliminarAdjunto(index) {
    var item = adjuntosData[index];
    if (!item || item.origen !== 'expediente') return;
    if (!confirm('Eliminar "' + item.nombre_original + '"?')) return;

    var formData = new FormData();
    formData.append('archivo_id', item.id);
    formData.append('paciente_id', PACIENTE_ID);

    fetch(BASE_URL + 'index.php?page=eliminar_adjunto_ajax', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.ok) {
            adjuntosData.splice(index, 1);
            renderizarAdjuntos();
        }
    });
}

// ==============================
// Preview Modal
// ==============================
function abrirPreviewAdjunto(index) {
    var item = adjuntosData[index];
    if (!item) return;

    var modal = document.getElementById('modalPreviewAdjunto');
    if (!modal) return;

    var nombre = document.getElementById('preview-adjunto-nombre');
    var img = document.getElementById('preview-adjunto-img');
    var pdf = document.getElementById('preview-adjunto-pdf');
    var video = document.getElementById('preview-adjunto-video');
    var noPreview = document.getElementById('preview-adjunto-no-preview');
    var loading = document.getElementById('preview-adjunto-loading');
    var descargar = document.getElementById('preview-adjunto-descargar');
    var imprimir = document.getElementById('preview-adjunto-imprimir');
    var emailBtn = document.getElementById('preview-adjunto-email');
    var whatsappBtn = document.getElementById('preview-adjunto-whatsapp');

    if (nombre) nombre.textContent = item.nombre_original;
    if (loading) loading.style.display = 'flex';

    [img, pdf, video, noPreview].forEach(function(el) {
        if (el) el.classList.add('d-none');
    });

    // Configurar descargar
    if (descargar) descargar.href = BASE_URL + 'index.php?page=descargar_adjunto&archivo_id=' + item.id;

    // Configurar imprimir
    if (imprimir) {
        imprimir.onclick = function() {
            if (item.es_pdf) {
                alert('Los archivos PDF ya tienen su propio botón de impresión en el visor.');
                return;
            }
            if (item.es_imagen) {
                var overlay = document.createElement('div');
                overlay.id = 'print-overlay-adjunto';
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:#fff;z-index:999999;display:flex;align-items:center;justify-content:center;';
                var img = document.createElement('img');
                img.src = item.url;
                img.style.cssText = 'max-width:100%;max-height:100%;object-fit:contain;';
                overlay.appendChild(img);
                document.body.appendChild(overlay);

                var cleanUp = function() { if (document.getElementById('print-overlay-adjunto')) document.getElementById('print-overlay-adjunto').remove(); };
                if (window.matchMedia) {
                    var mql = window.matchMedia('print');
                    var handler = function(e) { if (!e.matches) { cleanUp(); mql.removeListener(handler); } };
                    mql.addListener(handler);
                }
                window.onafterprint = cleanUp;

                setTimeout(function() { window.print(); }, 200);
                return;
            }
            alert('No se puede imprimir este tipo de archivo.');
        };
    }

    // Configurar email
    if (emailBtn) {
        emailBtn.onclick = function() {
            document.getElementById('email-adjunto-archivo-id').value = item.id;
            document.getElementById('email-adjunto-destinatario').value = '';
            document.getElementById('email-adjunto-asunto').value = 'Archivo adjunto: ' + item.nombre_original;
            document.getElementById('email-adjunto-mensaje').value = '';
            $('#modalPreviewAdjunto').modal('hide');
            setTimeout(function() {
                $('#modalEnviarAdjuntoEmail').modal('show');
            }, 300);
        };
    }

    // Configurar WhatsApp
    if (whatsappBtn) {
        whatsappBtn.onclick = function() {
            var btn = whatsappBtn;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

            var formData = new FormData();
            formData.append('tipo', 'adjunto');
            formData.append('archivo_id', item.id);
            formData.append('consulta_id', 0);

            fetch(BASE_URL + 'index.php?page=generar_url_publica_ajax', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var texto = '*Archivo adjunto*\n' + item.nombre_original;
                if (res.ok && res.url) {
                    texto += '\n' + res.url;
                }
                window.open('https://wa.me/?text=' + encodeURIComponent(texto), '_blank');
            })
            .catch(function() {
                var texto = '*Archivo adjunto*\n' + item.nombre_original;
                window.open('https://wa.me/?text=' + encodeURIComponent(texto), '_blank');
            })
            .then(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-whatsapp me-1"></i>WhatsApp';
            });
        };
    }

    // Mostrar preview segun tipo
    if (item.es_imagen) {
        if (img) {
            img.src = item.url;
            img.onload = function() {
                if (loading) loading.style.display = 'none';
                img.classList.remove('d-none');
            };
            img.onerror = function() {
                if (loading) loading.style.display = 'none';
                if (noPreview) noPreview.classList.remove('d-none');
            };
        }
    } else if (item.es_pdf) {
        if (pdf) {
            pdf.src = item.url;
            pdf.onload = function() {
                if (loading) loading.style.display = 'none';
                pdf.classList.remove('d-none');
            };
            // Fallback timeout
            setTimeout(function() {
                if (loading) loading.style.display = 'none';
                pdf.classList.remove('d-none');
            }, 1500);
        }
    } else if (item.es_video) {
        if (video) {
            video.src = item.url;
            video.onloadeddata = function() {
                if (loading) loading.style.display = 'none';
                video.classList.remove('d-none');
            };
            video.onerror = function() {
                if (loading) loading.style.display = 'none';
                if (noPreview) noPreview.classList.remove('d-none');
            };
            setTimeout(function() {
                if (loading) loading.style.display = 'none';
                video.classList.remove('d-none');
            }, 1500);
        }
    } else {
        if (loading) loading.style.display = 'none';
        if (noPreview) noPreview.classList.remove('d-none');
    }

    $('#modalPreviewAdjunto').modal('show');
}

// ==============================
// Inicializar dropzone al cargar la pagina
// ==============================
document.addEventListener('DOMContentLoaded', function() {
    setupDropzoneAdjuntos();
});
