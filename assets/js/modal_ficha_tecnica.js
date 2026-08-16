/**
 * JS Compartido para la Ficha Técnica (Modal)
 * Carga datos vía AJAX y rellena el modal #modalFichaTecnica
 */

window.cargarFichaTecnica = function(idInmueble) {
    console.log("CARGANDO FICHA TECNICA DEL ID: ", idInmueble);
    if (!idInmueble) return;

    // Mostrar modal usando jQuery para máxima compatibilidad
    $('#modalFichaTecnica').modal('show');

    // Limpiar contenido previo o mostrar loading
    $('#ficha_titulo').text('CARGANDO...');
    $('#fichaCarouselInner').empty().append('<div class="carousel-item active text-center p-5"><i class="fa fa-spin fa-spinner fa-3x text-muted"></i></div>');

    $.getJSON('index.php', {
        page: 'inmueble_ficha_json',
        id_inmueble: idInmueble
    }, function (resp) {
        if (!resp || !resp.ok) {
            alert(resp && resp.error ? resp.error : 'No se pudo cargar la ficha técnica.');
            return;
        }

        const d   = resp.data || {};
        const inm = d.inmueble || {};
        const emp = d.empresa || {};
        const ase = d.asesor || {};
        const dir = inm.direccion || {};

        // === TEXTOS BÁSICOS ===
        $('#ficha_titulo').text(inm.titulo || 'SIN NOMBRE');
        $('#ficha_codigo_publico').text(d.codigo_publico || '');
        $('#ficha_direccion_linea1').text(dir.linea1 || '');
        $('#ficha_direccion_linea2').text(dir.linea2 || '');
        
        $('#ficha_ubicacion').text(
            [dir.estado, dir.municipio, dir.parroquia, dir.urbanizacion]
                .filter(Boolean)
                .join(', ')
        );

        // === BADGES ===
        $('#ficha_tipo_operacion').text(inm.tipo_operacion_desc || inm.tipo_operacion || '—');
        $('#ficha_tipo_propiedad').text(inm.tipo_propiedad_desc || inm.tipo_propiedad || '—');
        $('#ft_estatus').text(inm.estatus || 'ACTIVO');

        // === PRECIOS ===
        if (inm.precio != null) {
            const pVal = Number(inm.precio);
            $('#ficha_precio').text('$' + pVal.toLocaleString('es-VE', { minimumFractionDigits: 0 }));
        }
        if (inm.precio_anterior != null && Number(inm.precio_anterior) > 0) {
            const pAnt = Number(inm.precio_anterior);
            $('#ficha_precio_anterior').text('Antes: $' + pAnt.toLocaleString('es-VE', { minimumFractionDigits: 0 }));
        } else {
            $('#ficha_precio_anterior').text('');
        }

        // === CARACTERÍSTICAS ===
        $('#ficha_habitaciones').html('<i class="fa fa-bed me-1"></i> ' + (inm.habitaciones_desc || (inm.habitaciones || 0) + ' HAB'));
        $('#ficha_banos').html('<i class="fa fa-bath me-1"></i> ' + (inm.banos_desc || (inm.banos || 0) + ' BAÑOS'));
        $('#ficha_estacionamientos').html('<i class="fa fa-car me-1"></i> ' + (inm.estacionamiento_desc || (inm.estacionamiento || 0) + ' PUESTOS'));
        
        const m2c = inm.m2_construccion || 0;
        const m2t = inm.m2_terreno || 0;
        $('#ficha_superficie').html('<i class="fa fa-arrows-alt me-1"></i> ' + m2c + ' m² C / ' + m2t + ' m² T');

        $('#ft_condominio').text('$' + (inm.condominio || 0));
        $('#ft_publicado').text(inm.publicado || 1);

        // === DESCRIPCIÓN ===
        let desc = inm.descripcion || '';
        $('#ficha_caracteristicas_texto').html(desc.replace(/\n/g, '<br>'));

        // === EMPRESA ===
        $('#empresa_nombre').text(emp.nombre || 'INMOCLICKS');
        $('#empresa_slogan').text(emp.slogan || '');
        $('#info_company_code').text(emp.company_code || '');
        $('#info_usuario_nombre').text(ase.nombre || '');
        $('#info_usuario_id').text(ase.id || '');

        // === ASESOR ===
        $('#agente_nombre').text(ase.nombre || '');
        $('#agente_cargo').text(ase.cargo || 'Asesor Inmobiliario');
        $('#agente_telefono').html('<i class="fa fa-phone me-1"></i> ' + (ase.telefono || ''));
        $('#agente_email').html('<i class="fa fa-envelope-o me-1"></i> ' + (ase.email || ''));
        
        if (ase.foto) {
            const fotoUrl = ase.foto.startsWith('http') ? ase.foto : window.BASE_URL + ase.foto.replace(/^\/+/, '');
            $('#agente_foto').attr('src', fotoUrl);
        }

        // === FOTOS / CARRUSEL ===
        const $inner   = $('#fichaCarouselInner').empty();
        const $thumbsD = $('#fichaThumbsDesktop').empty();
        const $thumbsM = $('#fichaThumbsMobile').empty();
        
        let fotos = Array.isArray(inm.fotos) ? inm.fotos : [];
        if (fotos.length && typeof fotos[0] === 'object') {
            fotos = fotos.slice().sort((a, b) => (a.orden || 0) - (b.orden || 0)).map(f => f.ruta || f.url || '');
        }
        fotos = fotos.filter(Boolean);

        const baseUrl = window.BASE_URL + 'public/';
        const placeholder = window.BASE_URL + 'assets/images/no-image.png';

        if (!fotos.length) {
            $inner.append(`<div class="carousel-item active"><img src="${placeholder}" class="d-block w-100 img-fluid rounded" alt="Sin imagen"></div>`);
        } else {
            fotos.forEach((ruta, idx) => {
                const url = ruta.startsWith('http') ? ruta : baseUrl + ruta;
                $inner.append(`<div class="carousel-item ${idx === 0 ? 'active' : ''}"><img src="${url}" class="d-block w-100 img-fluid rounded" style="max-height: 500px; object-fit: contain; background: #000;" alt="Foto ${idx+1}" onerror="this.src='${placeholder}'"></div>`);
                
                const thumbHtml = `<img src="${url}" class="img-thumbnail ficha-thumb me-1 mb-1" data-index="${idx}" style="width:60px; height:45px; object-fit:cover; cursor:pointer;" alt="Thumb ${idx+1}" onerror="this.src='${placeholder}'">`;
                $thumbsD.append(thumbHtml);
                $thumbsM.append(thumbHtml);
            });

            // Lógica de thumbnails
            $('.ficha-thumb').on('click', function() {
                const idx = $(this).data('index');
                $('#fichaCarousel').carousel(idx);
            });
        }

        // === MAPA ===
        const metaId = inm.id || idInmueble;
        $('#meta_id').text(metaId);
        $('#meta_company').text(emp.id || '');
        $('#meta_user').text(ase.id || '');
        
        $('#dt_m2_construccion').text(m2c + ' m²');
        $('#dt_m2_terreno').text(m2t + ' m²');
        
        if (inm.precio && m2c > 0) {
            const vM2 = (Number(inm.precio) / m2c).toLocaleString('es-VE', { minimumFractionDigits: 2 });
            $('#dt_valor_m2_const').text('$' + vM2 + ' / m²');
        }

        // Si existe la función de pintar mapa (está en inmueble_maestro_gestion.js o global)
        if (inm.latlong && typeof window.pintarMapaInmueble === 'function') {
            window.pintarMapaInmueble(inm.latlong);
        }

    }).fail(function() {
        alert('Error de conexión al cargar la ficha.');
    });
};
