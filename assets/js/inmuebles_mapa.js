// inmuebles_mapa.js
// Lógica de la pestaña 5: mapa, dirección y marcador

let map, marker, geocoder, streetViewPanorama;
let ultimaLatLng = null;

function getDireccionFallback() {
  const partes = [
    window.INMUEBLE_ESTADO || "",
    window.INMUEBLE_MUNICIPIO || "",
    window.INMUEBLE_PARROQUIA || "",
    window.INMUEBLE_URBANIZACION || "",
  ].filter(Boolean);
  return partes.join(", ");
}

function actualizarInputsLatLng(pos) {
  const inputLat = document.getElementById("map_latitud");
  const inputLng = document.getElementById("map_longitud");
  if (!pos) return;

  const lat = typeof pos.lat === "function" ? pos.lat() : pos.lat;
  const lng = typeof pos.lng === "function" ? pos.lng() : pos.lng;

  if (inputLat) inputLat.value = lat.toFixed(6);
  if (inputLng) inputLng.value = lng.toFixed(6);
}

function geocodeAddress(address, silencio) {
  if (!geocoder || !address) return;

  console.log("Geocoding:", address);

  geocoder.geocode({ address }, (results, status) => {
    console.log("Geocode status:", status, results);
    if (status === "OK" && results[0]) {
      const loc = results[0].geometry.location;
      ultimaLatLng = loc;

      map.setCenter(loc);
      map.setZoom(16);

      if (!marker) {
        marker = new google.maps.Marker({
          map,
          position: loc,
          draggable: true,
        });

        marker.addListener("dragend", () => {
          const pos = marker.getPosition();
          ultimaLatLng = pos;
          actualizarInputsLatLng(pos); // actualizar al soltar
        });
      } else {
        marker.setPosition(loc);
      }

      actualizarInputsLatLng(loc); // actualizar al geocodificar
    } else {
      console.warn("Geocode falló:", status);
      if (!silencio) {
        alert("No se pudo localizar esa dirección en el mapa.");
      }

      if (!silencio) {
        const fb = getDireccionFallback();
        if (fb && fb !== address) {
          geocodeAddress(fb, true);
        }
      }
    }
  });
}

// Esta función será llamada por Google Maps (callback=initMap)
function initMap() {
  console.log("initMap global llamado");

  const mapDiv         = document.getElementById("map");
  const inputDireccion = document.getElementById("map_direccion");
  const btnBuscar      = document.getElementById("btn_map_buscar");
  const btnLimpiar     = document.getElementById("btn_map_limpiar");
  const btnCentrar     = document.getElementById("btn_map_centrar");
  const inputLat       = document.getElementById("map_latitud");
  const inputLng       = document.getElementById("map_longitud");

  if (!mapDiv) {
    console.warn("No se encontró #map");
    return;
  }

  geocoder = new google.maps.Geocoder();

  map = new google.maps.Map(mapDiv, {
    zoom: 15,
    center: { lat: 10.496, lng: -66.903 }, // Caracas
    mapTypeId: google.maps.MapTypeId.ROADMAP,
  });

  // OJO: esta función local sombrea la global, pero es la que usa initMap
  function actualizarInputsLatLng(pos) {
    if (!pos) return;
    const lat = typeof pos.lat === "function" ? pos.lat() : pos.lat;
    const lng = typeof pos.lng === "function" ? pos.lng() : pos.lng;

    if (inputLat) inputLat.value = lat.toFixed(6);
    if (inputLng) inputLng.value = lng.toFixed(6);
  }

  // Dirección base del inmueble (solo para primera geocodificación cuando NO hay coords)
  const base = window.INMUEBLE_DIRECCION_BASE && window.INMUEBLE_DIRECCION_BASE.trim()
    ? window.INMUEBLE_DIRECCION_BASE.trim()
    : getDireccionFallback();

  console.log("Dirección base para geocode:", base);

  if (inputDireccion) {
    inputDireccion.value = base;
  }

  // === PRIORIDAD 1: usar SIEMPRE coords guardadas si existen ===
  let latGuardada = inputLat && inputLat.value ? parseFloat(inputLat.value) : NaN;
  let lngGuardada = inputLng && inputLng.value ? parseFloat(inputLng.value) : NaN;
  const tieneCoordsGuardadas =
    !isNaN(latGuardada) &&
    !isNaN(lngGuardada) &&
    !(latGuardada === 0 && lngGuardada === 0); // si decides que 0,0 cuenta como "vacío"

  if (tieneCoordsGuardadas) {
    console.log("Usando coords guardadas:", latGuardada, lngGuardada);
    const loc = { lat: latGuardada, lng: lngGuardada };
    ultimaLatLng = loc;

    marker = new google.maps.Marker({
      map,
      position: loc,
      draggable: true,
    });

    marker.addListener("dragend", () => {
      const pos = marker.getPosition();
      ultimaLatLng = pos;
      actualizarInputsLatLng(pos);
    });

    map.setCenter(loc);
    map.setZoom(16);
    actualizarInputsLatLng(loc);

  } else if (base) {
    // === PRIORIDAD 2: solo si NO hay coords guardadas, geocodificar dirección base ===
    console.log("No hay coords guardadas, geocodificando base...");
    geocodeAddress(base, true);

  } else {
    // === PRIORIDAD 3: sin coords ni dirección, caer en Caracas ===
    const loc = { lat: 10.496, lng: -66.903 };
    ultimaLatLng = loc;
    marker = new google.maps.Marker({
      map,
      position: loc,
      draggable: true,
    });

    marker.addListener("dragend", () => {
      const pos = marker.getPosition();
      ultimaLatLng = pos;
      actualizarInputsLatLng(pos);
    });

    actualizarInputsLatLng(loc);
  }

  // Eventos UI (estos sí pueden modificar coords después)
  if (btnBuscar && inputDireccion) {
    btnBuscar.addEventListener("click", () => {
      const addr = inputDireccion.value.trim();
      if (!addr) return;
      geocodeAddress(addr, false);
    });
  }

  if (btnCentrar && inputDireccion) {
    btnCentrar.addEventListener("click", () => {
      const addr = inputDireccion.value.trim() || getDireccionFallback();
      if (!addr) return;
      geocodeAddress(addr, false);
    });
  }

  if (btnLimpiar && inputDireccion) {
    btnLimpiar.addEventListener("click", () => {
      inputDireccion.value = "";
    });
  }

  // Exponer para pestaña 6
  window.obtenerDatosMapa = function () {
    const pos = ultimaLatLng;
    const lat = pos ? (typeof pos.lat === "function" ? pos.lat() : pos.lat) : null;
    const lng = pos ? (typeof pos.lng === "function" ? pos.lng() : pos.lng) : null;

    return {
      direccionTexto: inputDireccion ? inputDireccion.value.trim() : "",
      lat,
      lng,
    };
  };
}


// Exponer initMap para el callback de Google
window.initMap = initMap;
