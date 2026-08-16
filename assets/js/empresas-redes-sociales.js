console.log('SCRIPT REDES - 5.0 (Estabilidad)');

if (!window.redesAgregadas) {
    window.redesAgregadas = [];
}

if (typeof window.actualizarTablaRedes !== 'function') {
    
    let actualizando = false; // Guard contra recursión

    window.actualizarTablaRedes = function() {
        if (actualizando) return;
        actualizando = true;

        const tbody = document.getElementById('tbody_redes_sociales');
        if (!tbody) { actualizando = false; return; }

        tbody.innerHTML = '';
        if (window.redesAgregadas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted small">No hay redes.</td></tr>';
        } else {
            window.redesAgregadas.forEach((red, index) => {
                const marcada = red.marcadaEliminar === true;
                const row = document.createElement('tr');
                if (marcada) { row.style.opacity = '0.5'; row.style.backgroundColor = '#fff5f5'; }

                row.innerHTML = `
                    <td class="align-middle py-1 ${marcada ? 'text-decoration-line-through text-danger' : ''}">
                        <i class="fa-brands ${getIconoRed(red.id)} me-1"></i> 
                        <span class="small fw-bold">${red.nombre}</span>
                    </td>
                    <td class="text-end align-middle py-1 pe-2" style="white-space: nowrap;">
                        <button type="button" class="btn btn-outline-info btn-xs btn-editar-red" data-index="${index}" style="padding: 1px 4px;"><i class="fa fa-edit"></i></button>
                        <button type="button" class="btn ${marcada ? 'btn-danger' : 'btn-outline-danger'} btn-xs btn-marcar-red" data-index="${index}" style="padding: 1px 4px;"><i class="fa ${marcada ? 'fa-undo' : 'fa-trash'}"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Sincronizar inputs (Limpiando espacios y nulos)
        const activas  = window.redesAgregadas.filter(r => !r.marcadaEliminar && r.url.trim() !== '');
        const inId     = document.getElementById('input_redes_id');
        const inNombre = document.getElementById('input_redes');
        const inUrl    = document.getElementById('input_redes_url');
        
        if (inId)     inId.value     = activas.map(r => r.id).join(',');
        if (inNombre) inNombre.value = activas.map(r => r.nombre).join(',');
        if (inUrl)    inUrl.value    = activas.map(r => r.url).join('|');

        actualizando = false;
    };

    function getIconoRed(redId) {
        const iconos = {'facebook':'fa-facebook','instagram':'fa-instagram','tiktok':'fa-tiktok','youtube':'fa-youtube','x':'fa-x-twitter','twitter':'fa-x-twitter','linkedin':'fa-linkedin','whatsapp':'fa-whatsapp','telegram':'fa-telegram'};
        return iconos[redId] || 'fa-link';
    }

    document.addEventListener('click', function(e) {
        const btnMarcar = e.target.closest('.btn-marcar-red');
        if (btnMarcar) {
            e.preventDefault();
            const idx = parseInt(btnMarcar.getAttribute('data-index'), 10);
            window.redesAgregadas[idx].marcadaEliminar = !window.redesAgregadas[idx].marcadaEliminar;
            window.actualizarTablaRedes();
            return;
        }

        const btnEditar = e.target.closest('.btn-editar-red');
        if (btnEditar) {
            e.preventDefault();
            const idx = parseInt(btnEditar.getAttribute('data-index'), 10);
            const red = window.redesAgregadas[idx];
            const selRed = document.getElementById('select_red_social');
            const inUrl  = document.getElementById('input_url_red');
            if (selRed) selRed.value = red.id;
            if (inUrl) { inUrl.value = red.url; inUrl.focus(); }
            red.marcadaEliminar = false;
            window.actualizarTablaRedes();
            return;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const btnAdd = document.getElementById('btn_agregar_red');
        const selRed = document.getElementById('select_red_social');
        const inUrl  = document.getElementById('input_url_red');
        const hiddenRedes = document.getElementById('input_redes');

        if (window.redesAgregadas.length === 0 && hiddenRedes && hiddenRedes.value) {
            const ids = document.getElementById('input_redes_id').value.split(',').filter(x => x);
            const nms = hiddenRedes.value.split(',').filter(x => x);
            const urls = document.getElementById('input_redes_url').value.split('|').filter(x => x);
            window.redesAgregadas = ids.map((id, i) => ({ id: id.trim(), nombre: nms[i] ? nms[i].trim() : '', url: urls[i] ? urls[i].trim() : '', marcadaEliminar: false }));
        }
        window.actualizarTablaRedes();

        if (btnAdd) {
            btnAdd.addEventListener('click', function(e) {
                e.preventDefault();
                const id = selRed.value;
                const url = inUrl.value.trim();
                const nombre = selRed.options[selRed.selectedIndex].text;
                if (!id || !url) return;
                const existente = window.redesAgregadas.find(r => r.id === id);
                if (existente) { existente.url = url; existente.marcadaEliminar = false; } 
                else { window.redesAgregadas.push({ id, nombre, url, marcadaEliminar: false }); }
                selRed.value = ''; inUrl.value = '';
                window.actualizarTablaRedes();
            });
        }
    });
}
