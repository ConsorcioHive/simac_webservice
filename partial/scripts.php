 <?php
// Evitar que este bloque de scripts globales se incluya más de una vez
if (!defined('GLOBAL_SCRIPTS_LOADED')): 
    define('GLOBAL_SCRIPTS_LOADED', true);
?>
<!-- latest jquery-->
<script src="<?= BASE_URL ?>assets/js/jquery.min.js"></script>
<!-- Bootstrap js-->
<script src="<?= BASE_URL ?>assets/js/bootstrap/bootstrap.bundle.min.js"></script>
<!-- feather icon js-->
<script src="<?= BASE_URL ?>assets/js/icons/feather-icon/feather.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/icons/feather-icon/feather-icon.js"></script>
<!-- scrollbar js-->
<script src="<?= BASE_URL ?>assets/js/scrollbar/simplebar.js"></script>
<script src="<?= BASE_URL ?>assets/js/scrollbar/custom.js"></script>
<!-- Sidebar jquery-->
<script src="<?= BASE_URL ?>assets/js/config.js"></script>
<!-- Plugins JS start-->
<script src="<?= BASE_URL ?>assets/js/chart/apex-chart/apex-chart.js"></script>
<script src="<?= BASE_URL ?>assets/js/chart/apex-chart/stock-prices.js"></script>
<script id="menu" src="<?= BASE_URL ?>assets/js/sidebar-menu.js"></script>
<script src="<?= BASE_URL ?>assets/js/slick/slick.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/slick/slick.js"></script>
<script src="<?= BASE_URL ?>assets/js/header-slick.js"></script>
<!-- Plugins JS Ends-->
<!-- Theme js-->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
<?php endif; ?>

<script>
    if (typeof window.BASE_URL === 'undefined') {
        window.BASE_URL = '<?= BASE_URL ?>';
    }
</script>

<script>
$(document).ready(function() {
    window.setCookie = function(name, value, days = 365) {
        const d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        
        const paths = ['/', '/inmoclicks.com/', window.location.pathname];
        paths.forEach(p => {
            document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + p + ";";
        });

        document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
    };

    window.getCookie = function(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for(let i=0;i < ca.length;i++) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return decodeURIComponent(c.substring(nameEQ.length,c.length));
        }
        return null;
    };

    window.gestionarComparativo = function(btn, id) {
        const COOKIE_NAME = 'inmoclicks_comparativo';
        let list = [];
        try {
            const raw = getCookie(COOKIE_NAME);
            if (raw) list = JSON.parse(raw);
        } catch(e) { 
            list = []; 
        }
        
        list = list.map(item => Number(item));
        const targetId = Number(id);
        const index = list.indexOf(targetId);

        if (index > -1) {
            list.splice(index, 1);
            $(btn).removeClass('active text-danger').addClass('text-dark');
        } else {
            if (list.length >= 4) {
                alert('Solo puedes comparar hasta 4 inmuebles.');
                return;
            }
            list.push(targetId);
            $(btn).addClass('active text-danger').removeClass('text-dark');
        }

        const stringified = JSON.stringify(list);
        setCookie(COOKIE_NAME, stringified);
        localStorage.setItem(COOKIE_NAME, stringified);
        
        if (typeof actualizarBadgesInmoGlobal === 'function') actualizarBadgesInmoGlobal();
        
        if (window.location.href.indexOf('page=web_comparativo') > -1) {
            location.reload();
        }
    };

    window.gestionarFavorito = function(btn, id) {
        const COOKIE_NAME_FAV = 'inmoclicks_favoritos';
        let list = [];
        try {
            const raw = getCookie(COOKIE_NAME_FAV);
            if (raw) list = JSON.parse(raw);
        } catch(e) { list = []; }

        list = list.map(item => Number(item));
        const targetId = Number(id);
        const index = list.indexOf(targetId);

        if (index > -1) {
            list.splice(index, 1);
            $(btn).removeClass('active text-danger').addClass('text-dark');
            $(btn).find('i').removeClass('fa-heart').addClass('fa-heart-o');
        } else {
            list.push(targetId);
            $(btn).addClass('active text-danger').removeClass('text-dark');
            $(btn).find('i').removeClass('fa-heart-o').addClass('fa-heart');
        }

        setCookie(COOKIE_NAME_FAV, JSON.stringify(list));
        if (typeof actualizarBadgesInmoGlobal === 'function') actualizarBadgesInmoGlobal();

        if (window.location.href.indexOf('page=web_comparativo') > -1) {
            location.reload();
        }
    };

    window.limpiarTodoComparativo = function() {
        const emptyVal = JSON.stringify([]);
        setCookie('inmoclicks_comparativo', emptyVal);
        localStorage.setItem('inmoclicks_comparativo', emptyVal);

        if (typeof actualizarBadgesInmoGlobal === 'function') actualizarBadgesInmoGlobal();
        location.reload();
    };

    /**
     * Sincroniza los contadores (badges) de Favoritos y Comparativa
     */
    function actualizarBadgesInmoGlobal() {
        const getCountFromCookie = (name) => {
            try {
                const val = getCookie(name);
                if (!val) return 0;
                const arr = JSON.parse(val);
                return Array.isArray(arr) ? arr.length : 0;
            } catch(e) { return 0; }
        };

        const favCount = getCountFromCookie('inmoclicks_favoritos');
        const compCount = getCountFromCookie('inmoclicks_comparativo');

        const badgesFav = ['#badge_favoritos_top', '#badge_favoritos_side', '#favoritos-badge', '#badge_favoritos_header', '#badge_favoritos_dropdown'];
        const badgesComp = ['#badge_comparativo_top', '#badge_comparativo_side', '#comparativo-badge', '#badge_comparativo_header'];

        badgesFav.forEach(id => { if ($(id).length) $(id).text(favCount); });
        badgesComp.forEach(id => { if ($(id).length) $(id).text(compCount); });

        // Control de visibilidad para Dashboard (IDs del topbar.php)
        $('#li-favoritos-top').toggle(favCount > 0);
        $('#li-favoritos-dropdown-top').toggle(favCount > 0);
        $('#li-comparativo-top').toggle(compCount > 0);

        // Control de visibilidad para Web (IDs del layout.php)
        $('#btn-favoritos-web').toggle(favCount > 0);
        $('#btn-comparativo-web').toggle(compCount > 0);

        // Update the Dropdown Cart/Favoritos List
        if ($('#lista_favoritos_dropdown').length) {
            $.ajax({
                url: 'index.php?page=ajax_favoritos_dropdown',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.html) {
                        $('#lista_favoritos_dropdown').html(res.html);
                    }
                }
            });
        }
    }

    // Cargar Resumen de Actividad del Mercado (Dropdown Topbar)
    function cargarResumenActividad() {
        if ($('#contenidoActividadMercado').length) {
            $.ajax({
                url: 'index.php?page=ajax_resumen_mercado',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.html) {
                        $('#contenidoActividadMercado').html(res.html);
                        if (res.novedades > 0) {
                            $('#badge_actividad_top').text(res.novedades).show();
                        } else {
                            $('#contenidoActividadMercado').html('<li class="text-center py-3 text-muted small">Sin actividad en las últimas 48h</li>');
                            $('#badge_actividad_top').hide();
                        }
                    } else {
                        $('#contenidoActividadMercado').html('<li class="text-center py-3 text-muted small">Sin actividad en las últimas 48h</li>');
                    }
                },
                error: function() {
                    $('#contenidoActividadMercado').html('<li class="text-center py-3 text-danger small">Error al cargar actividad</li>');
                }
            });
        }
    }

    // Ejecutar al cargar
    actualizarBadgesInmoGlobal();
    cargarResumenActividad();
    
    // Recargar cada 5 minutos
    setInterval(cargarResumenActividad, 300000);

    // Exponer globalmente para que otros scripts la llamen tras modificar cookies
    window.actualizarBadgesInmoGlobal = actualizarBadgesInmoGlobal;
});
</script>

<!--
DESACTIVADA LA BARRA LATERAL DE CONFIGURACION DEL LAYOUT
<script src="< ?= BASE_URL ?>assets/js/theme-customizer/customizer.js"></script>
-->

<?php if (!empty($page_js) && is_array($page_js)): ?>
  <?php foreach ($page_js as $js): ?>
    <?php if (preg_match('#^https?://#', $js)): ?>
      <!-- Si es URL absoluta, la usamos tal cual -->
      <script src="<?= $js ?>"></script>
    <?php else: ?>
      <!-- Si es ruta relativa, se antepone BASE_URL -->
      <script src="<?= BASE_URL . $js ?>"></script>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>





<style>
    /* Forzamos que el cuerpo no tenga margen cuando el sidebar se oculte */
    #pageWrapper.close_icon .page-body {
        margin-left: 0px !important;
        transition: 0.3s;
    }
</style>


