(function($) {
    'use strict';
    if (window._ubicacionCascadaInicializada) return;
    window._ubicacionCascadaInicializada = true;
    $(function() {
        setTimeout(function() {
            if (typeof initUbicacionCascada === 'function') {
                initUbicacionCascada();
            }
        }, 50);
    });
})(jQuery);