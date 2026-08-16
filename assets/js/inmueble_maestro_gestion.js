// IDs de imágenes marcadas para eliminar (se procesan al Guardar)
let idsParaEliminar = [];
document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // CARGA DE COMBOS BÁSICOS
    // =====================================================
	
	const inputFechaDisp = document.getElementById('fecha_disponible');
	if (inputFechaDisp && !inputFechaDisp.value) {
		const hoy = new Date();
		const yyyy = hoy.getFullYear();
		const mm = String(hoy.getMonth() + 1).padStart(2, '0');
		const dd = String(hoy.getDate()).padStart(2, '0');
		inputFechaDisp.value = `${yyyy}-${mm}-${dd}`;
	}
	

    // === TIPO OPERACION ===
	const selTipoOperacion = document.getElementById('tipo_operacion');
	if (selTipoOperacion) {
		selTipoOperacion.innerHTML = '';
		const opt0 = document.createElement('option');
		opt0.value = '';
		opt0.textContent = 'Seleccione...';
		selTipoOperacion.appendChild(opt0);
	
		fetch('index.php?page=get_tipos_operacion', { method: 'POST' })
			.then(r => r.json())
			.then(lista => {
				if (!Array.isArray(lista)) return;
				lista.forEach(item => {
					const opt = document.createElement('option');
					opt.value = item.codigo;     // antes item.Codigo
					opt.textContent = item.descrip; // antes item.Descrip
					selTipoOperacion.appendChild(opt);
				});
	
				aplicarValorInicialSelect('tipo_operacion');
			})
			.catch(err => { });
	}
	
	
	// === TIPO PROPIEDAD ===
	const selTipoPropiedad = document.getElementById('tipo_propiedad');
	if (selTipoPropiedad) {
		selTipoPropiedad.innerHTML = '';
		const opt0b = document.createElement('option');
		opt0b.value = '';
		opt0b.textContent = 'Seleccione...';
		selTipoPropiedad.appendChild(opt0b);
	
		fetch('index.php?page=get_tipos_propiedad', { method: 'POST' })
			.then(r => r.json())
			.then(lista => {
				if (!Array.isArray(lista)) return;
				lista.forEach(item => {
					const opt = document.createElement('option');
					opt.value = item.codigo;      // antes item.Codigo
					opt.textContent = item.descrip; // antes item.Descrip
					selTipoPropiedad.appendChild(opt);
				});
	
				aplicarValorInicialSelect('tipo_propiedad');
			})
			.catch(err => { });
	}


    // === HABITACIONES ===
	const selHabitaciones = document.getElementById('habitaciones');
	

	if (selHabitaciones) {
		selHabitaciones.innerHTML = '';
		const opt0c = document.createElement('option');
		opt0c.value = '';
		opt0c.textContent = 'Seleccione...';
		selHabitaciones.appendChild(opt0c);
	
		fetch('index.php?page=ajax_inmuebles_atributos', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: 'filtro_tipo=3' // HABITACIONES
		})
		.then(r => r.json())
		.then(res => {
			const lista = res.data || res;
	
			if (!Array.isArray(lista)) return;
			lista.forEach(item => {
				const opt = document.createElement('option');
				opt.value = item.id;          // USAR ID NUMÉRICO
				opt.textContent = item.Descrip;
				selHabitaciones.appendChild(opt);
			});
	
			aplicarValorInicialSelect('habitaciones');
		})
		.catch(err => { });
	}

	// === BAÑOS ===
	const selBanos = document.getElementById('banos');
	if (selBanos) {
		selBanos.innerHTML = '';
		const opt0d = document.createElement('option');
		opt0d.value = '';
		opt0d.textContent = 'Seleccione...';
		selBanos.appendChild(opt0d);
	
		fetch('index.php?page=ajax_inmuebles_atributos', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: 'filtro_tipo=4' // BAÑOS
		})
		.then(r => r.json())
		.then(res => {
			const lista = res.data || res;
	
			if (!Array.isArray(lista)) return;
			lista.forEach(item => {
				const opt = document.createElement('option');
				opt.value = item.id;          // USAR ID NUMÉRICO
				opt.textContent = item.Descrip;
				selBanos.appendChild(opt);
			});
	
			aplicarValorInicialSelect('banos');
		})
		.catch(err => { });
	}
	
	// === ESTACIONAMIENTO ===
	const selEst = document.getElementById('estacionamiento');
	if (selEst) {
		selEst.innerHTML = '';
		const opt0e = document.createElement('option');
		opt0e.value = '';
		opt0e.textContent = 'Seleccione...';
		selEst.appendChild(opt0e);
	
		fetch('index.php?page=ajax_inmuebles_atributos', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: 'filtro_tipo=5' // ESTACIONAMIENTO
		})
		.then(r => r.json())
		.then(res => {
			const lista = res.data || res;
	
			if (!Array.isArray(lista)) return;
			lista.forEach(item => {
				const opt = document.createElement('option');
				opt.value = item.id;          // USAR ID NUMÉRICO
				opt.textContent = item.Descrip;
				selEst.appendChild(opt);
			});
	
			aplicarValorInicialSelect('estacionamiento');
		})
		.catch(err => { });
	}


    // =====================================================
    // UBICACIÓN EN CASCADA (Pestaña 3)
    // =====================================================

    const selEstado       = document.getElementById('estado');
    const selMunicipio    = document.getElementById('municipio');
    const selParroquia    = document.getElementById('parroquia');
    const selUrbanizacion = document.getElementById('urbanizacion');

    function resetSelectUbicacion(select, placeholder) {
        if (!select) return;
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder || 'SELECCIONE...';
        select.appendChild(opt);
        select.disabled = true;
    }

    function cargarComboUbicacion(select, pagina, dataEnvio, placeholder) {
        if (!select) return;
        select.disabled = true;
        select.innerHTML = '';
        const optLoading = document.createElement('option');
        optLoading.value = '';
        optLoading.textContent = 'CARGANDO...';
        select.appendChild(optLoading);

        const body = new URLSearchParams();
        Object.keys(dataEnvio || {}).forEach(k => body.append(k, dataEnvio[k]));

        fetch('index.php?page=' + pagina, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        })
        .then(r => r.json())
        .then(response => {
            let lista = (response && response.data) ? response.data : response;
            select.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = placeholder || 'SELECCIONE...';
            select.appendChild(opt0);

            if (Array.isArray(lista) && lista.length > 0) {
                lista.forEach(item => {
                    const id_crudo = (item.id !== undefined)
                        ? item.id.toString().replace(/<[^>]*>?/gm, '').trim()
                        : '';
                    const nombre = (item.nombre || item.Descrip || id_crudo || '')
                        .toString().toUpperCase().trim();

                    const valorFinal = nombre;

                    const opt = document.createElement('option');
                    opt.value = valorFinal;
                    opt.textContent = nombre;
                    select.appendChild(opt);
                });

                aplicarValorInicialSelect(select.id);
                select.disabled = false;
            } else {
                const optSin = document.createElement('option');
                optSin.value = '';
                optSin.textContent = 'SIN RESULTADOS';
                select.appendChild(optSin);
                select.disabled = true;
            }
        })
        .catch(err => {
            console.error('Error cargando combo ' + pagina, err);
            select.innerHTML = '';
            const optErr = document.createElement('option');
            optErr.value = '';
            optErr.textContent = 'ERROR AL CARGAR';
            select.appendChild(optErr);
            select.disabled = true;
        });
    }
	
	
	function cargarComboUbicacionPromise(select, pagina, dataEnvio, placeholder) {
		return new Promise((resolve, reject) => {
			if (!select) return resolve();
	
			select.disabled = true;
			select.innerHTML = '';
			const optLoading = document.createElement('option');
			optLoading.value = '';
			optLoading.textContent = 'CARGANDO...';
			select.appendChild(optLoading);
	
			const body = new URLSearchParams();
			Object.keys(dataEnvio || {}).forEach(k => body.append(k, dataEnvio[k]));
	
			fetch('index.php?page=' + pagina, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			})
			.then(r => r.json())
			.then(response => {
				let lista = (response && response.data) ? response.data : response;
	
				select.innerHTML = '';
				const opt0 = document.createElement('option');
				opt0.value = '';
				opt0.textContent = placeholder || 'SELECCIONE...';
				select.appendChild(opt0);
	
				if (Array.isArray(lista) && lista.length > 0) {
					lista.forEach(item => {
						const id_crudo = (item.id !== undefined)
							? item.id.toString().replace(/<[^>]*>?/gm, '').trim()
							: '';
						const nombre = (item.nombre || item.Descrip || id_crudo || '')
							.toString().toUpperCase().trim();
	
						const opt = document.createElement('option');
						opt.value = nombre;
						opt.textContent = nombre;
						select.appendChild(opt);
					});
	
					aplicarValorInicialSelect(select.id);
					select.disabled = false;
				} else {
					const optSin = document.createElement('option');
					optSin.value = '';
					optSin.textContent = 'SIN RESULTADOS';
					select.appendChild(optSin);
					select.disabled = true;
				}
	
				resolve();
			})
			.catch(err => {
				console.error('Error cargando combo ' + pagina, err);
				select.innerHTML = '';
				const optErr = document.createElement('option');
				optErr.value = '';
				optErr.textContent = 'ERROR AL CARGAR';
				select.appendChild(optErr);
				select.disabled = true;
				resolve();
			});
		});
	}


    // Carga inicial de estados
	// Carga inicial según si hay datos de edición
	const tieneEstadoInicial = !!(selEstado && selEstado.getAttribute('data-valor'));
	
	if (tieneEstadoInicial) {
		inicializarUbicacionDesdeDataValores();
	} else if (selEstado) {
		resetSelectUbicacion(selEstado, 'SELECCIONE...');
		cargarComboUbicacion(selEstado, 'obtener_estados', {}, 'SELECCIONE...');
	}


    // Eventos de cascada
    if (selEstado && selMunicipio && selParroquia && selUrbanizacion) {
        selEstado.addEventListener('change', function () {
            const nombreEstado = this.value;

            resetSelectUbicacion(selMunicipio, 'SELECCIONE...');
            resetSelectUbicacion(selParroquia, 'SELECCIONE...');
            resetSelectUbicacion(selUrbanizacion, 'SELECCIONE...');

            if (nombreEstado) {
                cargarComboUbicacion(
                    selMunicipio,
                    'obtener_municipios',
                    { id_estado: nombreEstado },
                    'SELECCIONE...'
                );
            }
        });

        selMunicipio.addEventListener('change', function () {
            const nombreMun = this.value;

            resetSelectUbicacion(selParroquia, 'SELECCIONE...');
            resetSelectUbicacion(selUrbanizacion, 'SELECCIONE...');

            if (nombreMun) {
                cargarComboUbicacion(
                    selParroquia,
                    'obtener_parroquias',
                    { id_municipio: nombreMun },
                    'SELECCIONE...'
                );
            }
        });

        selParroquia.addEventListener('change', function () {
            const nombrePar = this.value;

            resetSelectUbicacion(selUrbanizacion, 'SELECCIONE...');

            if (nombrePar) {
                cargarComboUbicacion(
                    selUrbanizacion,
                    'obtener_urbanizaciones',
                    { id_parroquia: nombrePar },
                    'SELECCIONE...'
                );
            }
        });
    }

    // Carga en cascada con data-valor (modo edición)
	async function inicializarUbicacionDesdeDataValores() {
		if (!selEstado || !selMunicipio || !selParroquia || !selUrbanizacion) return;
	
		const estadoValor       = (selEstado.getAttribute('data-valor') || '').toUpperCase().trim();
		const municipioValor    = (selMunicipio.getAttribute('data-valor') || '').toUpperCase().trim();
		const parroquiaValor    = (selParroquia.getAttribute('data-valor') || '').toUpperCase().trim();
		const urbanizacionValor = (selUrbanizacion.getAttribute('data-valor') || '').toUpperCase().trim();
	
		if (!estadoValor) return;
	
		// 1) Estados
		await cargarComboUbicacionPromise(selEstado, 'obtener_estados', {}, 'SELECCIONE...');
		selEstado.value = estadoValor;
	
		if (!selEstado.value) {
			console.warn('No se pudo aplicar estado inicial:', estadoValor);
			return;
		}
	
		// 2) Municipios
		if (municipioValor) {
			await cargarComboUbicacionPromise(
				selMunicipio,
				'obtener_municipios',
				{ id_estado: selEstado.value },
				'SELECCIONE...'
			);
			selMunicipio.value = municipioValor;
		}
	
		if (!selMunicipio.value) {
			return;
		}
	
		// 3) Parroquias
		if (parroquiaValor) {
			await cargarComboUbicacionPromise(
				selParroquia,
				'obtener_parroquias',
				{ id_municipio: selMunicipio.value },
				'SELECCIONE...'
			);
			selParroquia.value = parroquiaValor;
		}
	
		if (!selParroquia.value) {
			return;
		}
	
		// 4) Urbanizaciones
		if (urbanizacionValor) {
			await cargarComboUbicacionPromise(
				selUrbanizacion,
				'obtener_urbanizaciones',
				{ id_parroquia: selParroquia.value },
				'SELECCIONE...'
			);
			selUrbanizacion.value = urbanizacionValor;
		}
	}


    
	
	function parseLatLong(latlong) {
	  if (!latlong) return null;
	
	  // Quitar espacios y separar por coma
	  const parts = latlong.replace(/\s+/g, '').split(',');
	  if (parts.length !== 2) return null;
	
	  const lat = parseFloat(parts[0]);
	  const lng = parseFloat(parts[1]);
	
	  if (Number.isNaN(lat) || Number.isNaN(lng)) {
		return null;
	  }
	
	  return { lat, lng };
	}
	
	let mapaInmueble = null;
	let marcadorInmueble = null;
	
	const fichaModalEl = document.getElementById('modalFichaTecnica'); // id real de tu modal
	if (fichaModalEl) {
	  fichaModalEl.addEventListener('shown.bs.modal', function () {
		if (mapaInmueble) {
		  setTimeout(() => {
			mapaInmueble.invalidateSize();
			// recentra en la última coord conocida
			if (marcadorInmueble) {
			  const pos = marcadorInmueble.getLatLng();
			  mapaInmueble.setView(pos, 16);
			}
		  }, 200);
		}
	  });
	}

	
	function pintarMapaInmueble(latlong) {
	  const coord = parseLatLong(latlong);
	  const mapaDiv = document.getElementById('mapa_inmueble');
	
	  if (!mapaDiv) {
		console.warn('No existe el div #mapa_inmueble en el DOM');
		return;
	  }
	
	  if (!coord) {
		mapaDiv.innerHTML = '<p class="text-center text-sm text-gray-500 mt-4">Sin coordenadas para este inmueble.</p>';
		return;
	  }
	
	  // Limpiar contenido (por si quedó el mensaje de "sin coordenadas")
	  mapaDiv.innerHTML = '';
	
	  setTimeout(() => {
		if (!mapaInmueble) {
		  mapaInmueble = L.map('mapa_inmueble').setView([coord.lat, coord.lng], 16);
	
		  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; OpenStreetMap contributors'
		  }).addTo(mapaInmueble);
	
		  marcadorInmueble = L.marker([coord.lat, coord.lng]).addTo(mapaInmueble);
		} else {
		  mapaInmueble.setView([coord.lat, coord.lng], 16);
	
		  if (marcadorInmueble) {
			marcadorInmueble.setLatLng([coord.lat, coord.lng]);
		  } else {
			marcadorInmueble = L.marker([coord.lat, coord.lng]).addTo(mapaInmueble);
		  }
		}
	
		// TRUCO IMPORTANTE: primero invalidateSize, luego recentrar
		mapaInmueble.invalidateSize();
		mapaInmueble.setView([coord.lat, coord.lng], 16);
	
	  }, 200);
	}




    // =====================================================
    // DECLARACIÓN / BOTÓN GUARDAR HABILITADO POR CHECK
    // =====================================================

    const chkDeclaracion = document.getElementById('acepta_declaracion');
    const btnGuardar     = document.getElementById('btn_guardar_inmueble');

    if (chkDeclaracion && btnGuardar) {
        btnGuardar.disabled = !chkDeclaracion.checked;

        chkDeclaracion.addEventListener('change', function () {
            btnGuardar.disabled = !this.checked;
        });
    }

    // Helper para aplicar data-valor en selects
	function aplicarValorInicialSelect(idSelect) {
		const select = document.getElementById(idSelect);
		if (!select) return;
	
		const valorInicial = select.getAttribute('data-valor');
		if (!valorInicial) return;
	
		// 1. Intentar asignar valor directo (por ID)
		select.value = valorInicial;
		
		// 2. Si no se asignó (porque no existe ese ID en el select)
		// intentamos buscar por texto (para datos legacy como "2", "3", etc.)
		if (select.value === "" || select.value === null || select.selectedIndex === -1) {
			const options = select.options;
			for (let i = 0; i < options.length; i++) {
				const optText = options[i].text.toUpperCase().trim();
				const valUpper = valorInicial.toString().toUpperCase().trim();
				
				// Match exacto de valor
				if (options[i].value == valUpper) {
					select.selectedIndex = i;
					break;
				}

				// Match por texto: "(X)"
				if (optText.includes('(' + valUpper + ')')) {
					select.selectedIndex = i;
					break;
				}

				// Match por texto: empieza por "X "
				if (optText.startsWith(valUpper + ' ')) {
					select.selectedIndex = i;
					break;
				}
			}
		}

		if (typeof llenarResumenPaso6 === 'function') llenarResumenPaso6();
	}


    // =====================================================
    // PESTAÑA 6: RESUMEN BÁSICO
    // =====================================================

    function llenarResumenPaso6() {
		console.log('llenarResumenPaso6 ejecutado');
		// Origen
		const selTipoOperacion   = document.getElementById("tipo_operacion");
		const selTipoPropiedad   = document.getElementById("tipo_propiedad");
		const inputPrecio        = document.getElementById("precio");
		const inputSuperficie    = document.getElementById("superficie");
		const selM2ha            = document.getElementById("m2ha");
		const inputTerreno       = document.getElementById("terreno");
		const selM2haT           = document.getElementById("m2ha_t");
	
		// Destino
		const rTipoOperacion     = document.getElementById("resumen_tipo_operacion");
		const rTipoPropiedad     = document.getElementById("resumen_tipo_propiedad");
		const rHabitaciones      = document.getElementById("resumen_habitaciones");
		const rBanos             = document.getElementById("resumen_banos");
		const rEstacionamientos  = document.getElementById("resumen_estacionamientos");
		const rPrecio            = document.getElementById("resumen_precio");
		const rSuperficie        = document.getElementById("resumen_superficie");
		const rTerreno           = document.getElementById("resumen_terreno");
	
		// 1) Tipo operación / propiedad (siguen leyendo del select)
		if (rTipoOperacion && selTipoOperacion) {
			const txt = selTipoOperacion.selectedIndex > -1
				? selTipoOperacion.options[selTipoOperacion.selectedIndex].text
				: "";
			rTipoOperacion.textContent = txt || "—";
		}
	
		if (rTipoPropiedad && selTipoPropiedad) {
			const txt = selTipoPropiedad.selectedIndex > -1
				? selTipoPropiedad.options[selTipoPropiedad.selectedIndex].text
				: "";
			rTipoPropiedad.textContent = txt || "—";
		}
	
		// 2) Habitaciones / Baños / Estacionamientos (Leemos de los selects actuales)
		const selHab = document.getElementById("habitaciones");
		const selBan = document.getElementById("banos");
		const selEst = document.getElementById("estacionamiento");

		if (rHabitaciones && selHab) {
			const txt = selHab.selectedIndex > 0 ? selHab.options[selHab.selectedIndex].text : "0 habitaciones";
			rHabitaciones.textContent = txt;
		}
	
		if (rBanos && selBan) {
			const txt = selBan.selectedIndex > 0 ? selBan.options[selBan.selectedIndex].text : "0 baños";
			rBanos.textContent = txt;
		}
	
		if (rEstacionamientos && selEst) {
			const txt = selEst.selectedIndex > 0 ? selEst.options[selEst.selectedIndex].text : "0 puestos";
			rEstacionamientos.textContent = txt;
		}
	
		// 3) Precio
		if (rPrecio && inputPrecio) {
			const val = parseFloat(inputPrecio.value || "0");
			rPrecio.textContent = val > 0
				? val.toLocaleString("es-VE", { minimumFractionDigits: 2 })
				: "—";
		}
	
		// 4) Superficie / Terreno
		if (rSuperficie && inputSuperficie) {
			const sup = parseFloat(inputSuperficie.value || "0");
			const unidad = selM2ha && selM2ha.value ? selM2ha.value : "m²";
			rSuperficie.textContent = sup > 0 ? `${sup} ${unidad}` : "—";
		}
	
		if (rTerreno && inputTerreno) {
			const terr = parseFloat(inputTerreno.value || "0");
			const unidadT = selM2haT && selM2haT.value ? selM2haT.value : "m²";
			rTerreno.textContent = terr > 0 ? `${terr} ${unidadT}` : "—";
		}
	}

    const tabStep6 = document.querySelector('#step6-tab, a[href="#step6"]');
    if (tabStep6) {
        tabStep6.addEventListener('shown.bs.tab', function () {
            llenarResumenPaso6();
        });
    }
	
		
	// =====================================================
	// INICIALIZACIONES AL CARGAR LA PÁGINA
	// =====================================================
	
	// Estado de publicación (select) con valor de BD
	aplicarValorInicialSelect('estado_publicacion');
	
	const id = $('#id_inmueble_hidden').val();
	console.log('ID HIDDEN:', id);

	// =====================================================
	// MARCAR IMÁGENES PARA ELIMINAR (SE BORRAN AL GUARDAR)
	// =====================================================
	window.marcarImagenBorrada = function(idMedia) {
		console.log('>>> ENTRÓ en marcarImagenBorrada, idMedia =', idMedia);
	
		// Quitamos temporalmente el confirm para no cortar el flujo
		// if (!idMedia) return;
	
		const idStr = idMedia.toString();
		console.log('idStr =', idStr);
	
		if (!Array.isArray(idsParaEliminar)) {
			idsParaEliminar = [];
		}
	
		idsParaEliminar.push(idStr);
		console.log('idsParaEliminar después de push:', idsParaEliminar);
	
		const $item = $(`.foto-item[data-id-media="${idStr}"]`);
		console.log('Elemento encontrado para data-id-media =', idStr, ' => ', $item.length);
		$item.fadeOut(200, function() { $(this).remove(); });
	};
	
	// Handler específico para el botón X de cada foto (elimina en servidor)
	$(document).on('click', '.btn-delete-foto', function (e) {
		e.preventDefault();
		e.stopPropagation();
	
		const $item   = $(this).closest('.foto-item');
		const idMedia = $item.data('id-media');
	
		console.log('Click X, idMedia =', idMedia);
	
		if (!idMedia) {
			console.error('No se encontró data-id-media en .foto-item');
			return;
		}
	
		if (!confirm('¿Eliminar esta imagen definitivamente?')) {
			return;
		}
	
		fetch('index.php?page=inmuebles_media_eliminar', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: 'id_media=' + encodeURIComponent(idMedia)
		})
		.then(r => r.json())
		.then(resp => {
			console.log('Respuesta eliminar media:', resp);
	
			if (!resp || !resp.ok) {
				alert(resp && resp.mensaje ? resp.mensaje : 'Error al eliminar imagen');
				return;
			}
	
			console.log('✅ Imagen eliminada en servidor, id_media =', idMedia);
			$item.fadeOut(200, function () { $(this).remove(); });
		})
		.catch(err => {
			console.error('Error de conexión al eliminar media', err);
			alert('Error de conexión al eliminar imagen');
		});
	});
	
	// =====================================================
	// SUBMIT: GUARDAR INMUEBLE (DATOS + IMÁGENES)
	// =====================================================
	const formInmueble = document.getElementById('form_inmueble');
	if (formInmueble && typeof btnGuardar !== 'undefined') {
		formInmueble.addEventListener('submit', function (e) {
			e.preventDefault();
	
			const editorCarac = document.getElementById('caracteristicas');
			const hiddenCarac = document.getElementById('caracteristicas_raw');
			if (editorCarac && hiddenCarac) {
				hiddenCarac.value = editorCarac.innerHTML.trim();
			}
	
			/*// --- SOLO UNA VEZ, SIN DUPLICAR ---
			const inputBorradas = document.getElementById('input_imagenes_borradas');
			if (inputBorradas && typeof idsParaEliminar !== 'undefined') {
				inputBorradas.value = JSON.stringify(idsParaEliminar);
			}
	
			let nuevoOrden = [];
			document.querySelectorAll('.foto-item').forEach(el => {
				let idMedia = el.getAttribute('data-id-media');
				if (idMedia) nuevoOrden.push(idMedia);
			});
			const inputOrden = document.getElementById('input_imagenes_orden');
			if (inputOrden) {
				inputOrden.value = JSON.stringify(nuevoOrden);
			}
	
			console.log('idsParaEliminar EN JS:', idsParaEliminar);
			console.log('input_imagenes_borradas.value =', inputBorradas ? inputBorradas.value : 'SIN INPUT');
			console.log('input_imagenes_orden.value   =', inputOrden ? inputOrden.value   : 'SIN INPUT');
*/	
			btnGuardar.disabled = true;
			btnGuardar.innerHTML = '<i class="fa fa-spin fa-spinner"></i> Guardando...';
	
			const formData = new FormData(formInmueble);
	
			fetch(formInmueble.action, {
				method: 'POST',
				body: formData
			})
			.then(r => r.text())
			.then(txt => {
				let resp;
				try { resp = JSON.parse(txt); } catch (e) {
					swal('¡Error!', 'Respuesta no válida del servidor.', 'error');
					btnGuardar.disabled = false;
					btnGuardar.innerHTML = 'Guardar inmueble';
					return;
				}
	
				if (!resp || !resp.ok) {
					swal({
						icon: 'error',
						title: '¡Atención!',
						text: resp && resp.mensaje ? resp.mensaje : 'Error al guardar inmueble'
					});
					btnGuardar.disabled = false;
					btnGuardar.innerHTML = 'Guardar inmueble';
					return;
				}
	
				const idInmueble = resp.id_inmueble;
				const idForm = document.getElementById('id_inmueble_hidden').value;
				if (!idForm) {
					swal({
						icon: 'success',
						title: '¡Excelente!',
						text: 'Inmueble creado correctamente.',
						timer: 2000,
						buttons: false
					}).then(() => {
						window.location.href = 'index.php?page=inmueble_maestro_gestion&id=' + idInmueble;
					});
				} else {
					swal({
						icon: 'success',
						title: '¡Guardado!',
						text: 'Los cambios se aplicaron correctamente.',
						timer: 1500,
						buttons: false
					}).then(() => {
						location.reload();
					});
				}
	
				let hiddenId = document.getElementById('id_inmueble_hidden');
					if (!hiddenId) {
					  hiddenId = document.createElement('input');
					  hiddenId.type = 'hidden';
					  hiddenId.id = 'id_inmueble_hidden';
					  hiddenId.name = 'id_inmueble';
					  formInmueble.appendChild(hiddenId);
					}
					hiddenId.value = idInmueble;
					window.INMUEBLE_ID = idInmueble;
					
					// NUEVO: llamar SIEMPRE a guardarInmuebleMedia si existe
					if (window.guardarInmuebleMedia) {
					  window.guardarInmuebleMedia()
						.then(respMedia => {
						  swal('¡Hecho!', 'Inmueble y fotos guardados correctamente.', 'success');
						  btnGuardar.disabled = false;
						  btnGuardar.innerHTML = 'Guardar inmueble';
						})
						.catch(errMedia => {
						  swal('Atención', 'Inmueble guardado, pero hubo un error con las imágenes.', 'warning');
						  btnGuardar.disabled = false;
						  btnGuardar.innerHTML = 'Guardar inmueble';
						});
					} else {
					  swal('¡Hecho!', 'Inmueble guardado correctamente.', 'success');
					  btnGuardar.disabled = false;
					  btnGuardar.innerHTML = 'Guardar inmueble';
					}
			})
			.catch(err => {
				swal('Error', 'Error de conexión', 'error');
				btnGuardar.disabled = false;
				btnGuardar.innerHTML = 'Guardar inmueble';
			});
		});
	}
	
}); // cierra document.addEventListener('DOMContentLoaded', ...)