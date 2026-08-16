(function($) {
    'use strict';

    function initUbicacionCascada(options) {
        var cfg = $.extend({
            pais: '#codigo_pais',
            estado: '#codigo_estado',
            municipio: '#codigo_municipio',
            parroquia: '#codigo_parroquia',
            postal: '#codigo_postal',
            monedaInfo: '#moneda_info',
            monedaCodigo: '#moneda_codigo',
            baseUrl: (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'assets/ajax/ubicacion.php'
        }, options || {});

        var $pais         = $(cfg.pais),
            $estado       = $(cfg.estado),
            $municipio    = $(cfg.municipio),
            $parroquia    = $(cfg.parroquia),
            $postal       = $(cfg.postal),
            $monedaInfo   = $(cfg.monedaInfo),
            $monedaCodigo = $(cfg.monedaCodigo);
        
        // Leer valores iniciales de los atributos data-value o de window.empresaUbicacion si existe
        var estadoValor = (typeof window.empresaUbicacion !== 'undefined' && window.empresaUbicacion.estado) ? window.empresaUbicacion.estado : $estado.attr('data-value');
        var municipioValor = (typeof window.empresaUbicacion !== 'undefined' && window.empresaUbicacion.municipio) ? window.empresaUbicacion.municipio : $municipio.attr('data-value');
        var parroquiaValor = (typeof window.empresaUbicacion !== 'undefined' && window.empresaUbicacion.parroquia) ? window.empresaUbicacion.parroquia : $parroquia.attr('data-value');
        
        var postalValor = $postal.val(); // Al cargar la pagina, el input ya tiene el valor (si PHP lo puso)
        var monedaValor = (typeof window.empresaUbicacion !== 'undefined' && window.empresaUbicacion.moneda) ? window.empresaUbicacion.moneda : $monedaCodigo.attr('data-value');

        console.log('[cascada] valores iniciales:', {
            estadoValor: estadoValor,
            municipioValor: municipioValor,
            parroquiaValor: parroquiaValor,
            monedaValor: monedaValor,
            paisVal: $pais.val()
        });

        // Función para cargar estados
        function cargarEstados(pais, estadoSeleccionado) {
            if (!pais) return;
            
            $estado
                .prop('disabled', true)
                .html('<option value="">Cargando...</option>');
            $municipio
                .prop('disabled', true)
                .html('<option value="">Primero selecciona estado...</option>');
            $parroquia
                .prop('disabled', true)
                .html('<option value="">Primero selecciona municipio...</option>');
            $monedaCodigo
                .prop('disabled', true)
                .html('<option value="">Selecciona municipio...</option>');

            $.get(cfg.baseUrl, { action: 'estados', pais: pais }, function(html) {
                $estado.html(html).prop('disabled', false);
                
                // Seleccionar el estado guardado si existe
                if (estadoSeleccionado) {
                    $estado.val(estadoSeleccionado);
                    // Disparar cambio para cargar municipios
                    setTimeout(function() {
                        $estado.trigger('change');
                    }, 100);
                }
            });
        }

        // Función para cargar municipios
        function cargarMunicipios(pais, estado, municipioSeleccionado) {
            if (!estado) return;
            
            $municipio
                .prop('disabled', true)
                .html('<option value="">Cargando...</option>');
            $parroquia
                .prop('disabled', true)
                .html('<option value="">Primero selecciona municipio...</option>');
            $monedaCodigo
                .prop('disabled', true)
                .html('<option value="">Selecciona municipio...</option>');

            $.get(cfg.baseUrl, {
                action: 'municipios',
                pais: pais,
                estado: estado
            }, function(html) {
                $municipio.html(html).prop('disabled', false);
                
                // Seleccionar el municipio guardado si existe
                if (municipioSeleccionado) {
                    console.log('[cascada] seleccionando municipio:', municipioSeleccionado, 'opciones disponibles:', $municipio.find('option[value="' + municipioSeleccionado + '"]').length);
                    $municipio.val(municipioSeleccionado);
                    console.log('[cascada] municipio seleccionado final:', $municipio.val());
                    // Disparar cambio para cargar parroquias
                    setTimeout(function() {
                        $municipio.trigger('change');
                    }, 100);
                }
            });
        }

        // Función para cargar parroquias, info y monedas
        function cargarParroquias(pais, estado, municipio, parroquiaSeleccionada) {
            if (!municipio) return;
            
            $parroquia
                .prop('disabled', true)
                .html('<option value="">Cargando...</option>');
            $monedaCodigo
                .prop('disabled', true)
                .html('<option value="">Cargando monedas...</option>');

            // 1) Cargar parroquias
            $.get(cfg.baseUrl, {
                action: 'parroquias',
                pais: pais,
                estado: estado,
                municipio: municipio
            }, function(html) {
                $parroquia.html(html).prop('disabled', false);
                
                // Seleccionar la parroquia guardada si existe
                if (parroquiaSeleccionada) {
                    $parroquia.val(parroquiaSeleccionada);
                }
            });

            // 2) Cargar info (postal y moneda por defecto)
            $.getJSON(cfg.baseUrl, {
                action: 'info',
                pais: pais,
                estado: estado,
                municipio: municipio
            }, function(data) {
                // Solo auto-completar si no hay un valor pre-cargado
                if (!postalValor && data.codigo_postal && $postal.length)       $postal.val(data.codigo_postal);
                if (!monedaValor && data.moneda && $monedaInfo.length)          $monedaInfo.val(data.moneda);
            });

            // 3) Cargar monedas disponibles
            $.getJSON(cfg.baseUrl, {
                action: 'monedas',
                pais: pais,
                estado: estado,
                municipio: municipio
            }, function(response) {
                console.log('monedas response:', response);

                var $moneda = $monedaCodigo;
                $moneda.html('<option value="">Selecciona moneda...</option>');

                if (response.monedas_disponibles && response.monedas_disponibles.length > 0) {
                    var defaultToSelect = monedaValor ? monedaValor : response.moneda_default;
                    response.monedas_disponibles.forEach(function(m) {
                        var selected = (m.codigo === defaultToSelect) ? 'selected' : '';
                        $moneda.append(
                            '<option value="' + m.codigo + '" ' + selected + '>' +
                            m.nombre + ' (' + m.simbolo + ')' +
                            '</option>'
                        );
                    });
                    $moneda.prop('disabled', false);
                    monedaValor = null; // Limpiar para futuros cambios manuales
                } else {
                    $moneda.prop('disabled', true);
                }
            }).fail(function(xhr) {
                console.error('Error cargando monedas', xhr.responseText);
                $monedaCodigo
                    .prop('disabled', true)
                    .html('<option value="">Error cargando monedas</option>');
            });
        }

        // Cambio de país -> cargar estados
        $pais.off('change').on('change', function() {
            var v = $(this).val();
            // Al cambiar de país de forma manual, limpiamos los valores iniciales guardados
            estadoValor = null;
            municipioValor = null;
            parroquiaValor = null;
            if (v) {
                cargarEstados(v, null);
            } else {
                // Limpiar selects dependientes
                $estado.html('<option value="">Seleccione estado...</option>').prop('disabled', true);
                $municipio.html('<option value="">Seleccione municipio...</option>').prop('disabled', true);
                $parroquia.html('<option value="">Seleccione parroquia...</option>').prop('disabled', true);
            }
        });

        // Cambio de estado -> cargar municipios
        $estado.off('change').on('change', function() {
            var v = $(this).val();
            if (v) {
                var mVal = null;
                if (municipioValor) {
                    mVal = municipioValor;
                    municipioValor = null; // Limpiar para futuros cambios manuales del estado
                }
                cargarMunicipios($pais.val(), v, mVal);
            } else {
                // Limpiar selects dependientes
                $municipio.html('<option value="">Seleccione municipio...</option>').prop('disabled', true);
                $parroquia.html('<option value="">Seleccione parroquia...</option>').prop('disabled', true);
            }
        });

        // Cambio de municipio -> cargar parroquias, info y monedas
        $municipio.off('change').on('change', function() {
            var v = $(this).val();
            if (v) {
                var pVal = null;
                if (parroquiaValor) {
                    pVal = parroquiaValor;
                    parroquiaValor = null; // Limpiar para futuros cambios manuales del municipio
                }
                // Al cambiar el municipio, si fue manual limpiamos postalValor y monedaValor
                // para que el sistema auto-complete con los default
                if (window.event && window.event.type === 'change') {
                    postalValor = null;
                    monedaValor = null;
                }
                cargarParroquias($pais.val(), $estado.val(), v, pVal);
            } else {
                // Limpiar selects dependientes
                $parroquia.html('<option value="">Seleccione parroquia...</option>').prop('disabled', true);
            }
        });

        // Inicializar cascada cuando el DOM esté listo
        // Pequeño delay para asegurar que el DOM esté completamente listo
        setTimeout(function() {
            var paisInicial = $pais.val();
            if (paisInicial && paisInicial !== '') {
                cargarEstados(paisInicial, estadoValor);
            }
        }, 100);
    }

    window.initUbicacionCascada = initUbicacionCascada;
})(jQuery);