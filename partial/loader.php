<?php
$url = $_SERVER['REQUEST_URI'];
$items = explode('/', $url);
$page = end($items);
?>
</head>
<?php 
if($page == 'button-builder.php'){
  echo '<body class="button-builder">';
}else{
  echo '<body>';
}
?>
  <!-- loader starts-->
  <div class="loader-wrapper" id="page-loader">
    <div class="loader-index"><span></span></div>
    <p style="margin-top: 20px; font-size: 16px; color: #333; font-weight: 500; text-align: center;">Por favor, espere...</p>
    <svg>
      <defs></defs>
      <filter id="goo">
        <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
        <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
      </filter>
    </svg>
  </div>
  <!-- loader ends-->
  
  <!-- tap on top starts-->
  <div class="tap-top"><i data-feather="chevrons-up"></i></div>
  <!-- tap on tap ends-->

  <script>
    // Ocultar loader cuando todo cargó
    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    });
  </script>
