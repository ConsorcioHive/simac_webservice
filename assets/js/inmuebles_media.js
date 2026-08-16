// inmuebles_media.js
// Lógica de la pestaña 4: solo imágenes

document.addEventListener("DOMContentLoaded", () => {
  const INMUEBLE_ID = typeof window.INMUEBLE_ID !== "undefined"
    ? parseInt(window.INMUEBLE_ID, 10)
    : 0;

  // Referencias DOM
  const dropzoneImagenes = document.getElementById("dropzone_imagenes");
  const inputImagenes = document.getElementById("input_imagenes");
  const gridImagenes = document.getElementById("grid_imagenes");
  
  // Estado local
  // { idTmp, idDb?, name, file?, previewUrl, esServidor? }
  let imagenesSeleccionadas = [];
  let imagenesParaBorrar = []; // <--- NUEVO
  let indiceArrastrado = null;

  // ==========================
  // Helpers generales
  // ==========================
  function crearIdTemporal() {
    return "tmp_" + Math.random().toString(36).substring(2, 9);
  }

  function limpiarGridImagenes() {
    if (gridImagenes) {
      gridImagenes.innerHTML = "";
    }
  }
  
  function handleDragStart(e) {
	  indiceArrastrado = parseInt(e.currentTarget.dataset.index, 10);
	  e.dataTransfer.effectAllowed = "move";
	}
	
	function handleDragOver(e) {
	  e.preventDefault(); // necesario para permitir drop
	  e.dataTransfer.dropEffect = "move";
	}
	
	async function handleDrop(e) {
	  e.preventDefault();
	  const indiceDestino = parseInt(e.currentTarget.dataset.index, 10);
	  if (Number.isNaN(indiceArrastrado) || Number.isNaN(indiceDestino)) return;
	  if (indiceArrastrado === indiceDestino) return;
	
	  const item = imagenesSeleccionadas.splice(indiceArrastrado, 1)[0];
	  imagenesSeleccionadas.splice(indiceDestino, 0, item);
	
	  indiceArrastrado = null;
	  renderizarImagenes();
	  await guardarOrdenEnServidor(); // <--- ESTA LÍNEA
	}

	
	function handleDragEnd() {
	  indiceArrastrado = null;
	}

  function renderizarImagenes() {
	  if (!gridImagenes) return;
	  limpiarGridImagenes();
	
	  imagenesSeleccionadas.forEach((img, index) => {
		const col = document.createElement("div");
		col.className = "col";
	
		// Drag & drop
		col.dataset.index = index;
		col.draggable = true;
		col.addEventListener("dragstart", handleDragStart);
		col.addEventListener("dragover", handleDragOver);
		col.addEventListener("drop", handleDrop);
		col.addEventListener("dragend", handleDragEnd);
	
		const card = document.createElement("div");
		card.className = "position-relative border rounded-3 overflow-hidden shadow-sm bg-white";
	
		// Imagen
		const imgEl = document.createElement("img");
		imgEl.src = img.previewUrl;
		imgEl.alt = img.name;
		imgEl.className = "w-100";
		imgEl.style.height = "130px";
		imgEl.style.objectFit = "cover";
	
		// Barra inferior con flechas y X
		const bottomBar = document.createElement("div");
		bottomBar.className =
		  "d-flex align-items-center justify-content-between px-2 py-1";
		bottomBar.style.background = "rgba(248,249,250,0.9)";
	
		const controls = document.createElement("div");
		controls.className = "d-flex align-items-center gap-1";
	
		// Flecha izquierda
		const btnLeft = document.createElement("button");
		btnLeft.type = "button";
		btnLeft.className = "btn btn-sm btn-outline-secondary py-0 px-2";
		btnLeft.innerHTML = "&larr;";
		btnLeft.disabled = index === 0;
		btnLeft.style.fontSize = "12px";
		btnLeft.addEventListener("click", () => {
		  console.log("CLICK FLECHA IZQ index =", index);
		  moverImagen(index, -1);
		});
	
		// Flecha derecha
		const btnRight = document.createElement("button");
		btnRight.type = "button";
		btnRight.className = "btn btn-sm btn-outline-secondary py-0 px-2";
		btnRight.innerHTML = "&rarr;";
		btnRight.disabled = index === imagenesSeleccionadas.length - 1;
		btnRight.style.fontSize = "12px";
		btnRight.addEventListener("click", () => {
		  console.log("CLICK FLECHA DER index =", index);
		  moverImagen(index, 1);
		});
	
		// X junto a las flechas (NO absoluta)
		const btnDel = document.createElement("button");
		btnDel.type = "button";
		btnDel.className = "btn btn-sm btn-outline-danger py-0 px-2";
		btnDel.textContent = "X";
		btnDel.style.fontSize = "12px";
		btnDel.addEventListener("click", () => {
		  console.log("CLICK X index =", index);
		  eliminarImagen(index);
		});
	
		controls.appendChild(btnLeft);
		controls.appendChild(btnRight);
		controls.appendChild(btnDel);
	
		bottomBar.appendChild(controls);
	
		// Nombre del archivo
		const nombreEl = document.createElement("div");
		nombreEl.className = "small text-truncate px-2 py-1 text-muted";
		nombreEl.textContent = img.name;
	
		// Montaje final
		card.appendChild(imgEl);
		card.appendChild(bottomBar);
		card.appendChild(nombreEl);
	
		col.appendChild(card);
		gridImagenes.appendChild(col);
	  });
	}




  // ==========================
  // Cargar imágenes desde backend
  // ==========================
  async function cargarMediaExistente() {
    if (!INMUEBLE_ID || INMUEBLE_ID <= 0) return;

    try {
      const res = await fetch(`index.php?page=inmuebles_media_listar&inmueble_id=${INMUEBLE_ID}`);
      const data = await res.json();
      if (!data.ok) return;

      const items = Array.isArray(data.items) ? data.items : [];

      // Limpiamos estado actual
      imagenesSeleccionadas = [];

      items.forEach(item => {
        if (item.tipo === "IMAGEN") {
          imagenesSeleccionadas.push({
            idTmp: "srv_" + item.id,
            idDb: item.id,
            name: item.nombre_original,
            file: null,
            previewUrl: item.url,
            esServidor: true
          });
        }
      });

      renderizarImagenes();
    } catch (e) {
      console.error("Error cargando media existente:", e);
    }
  }
  
  async function guardarOrdenEnServidor() {
	  const ordenIds = imagenesSeleccionadas
		.filter(img => img.idDb)
		.map((img, idx) => ({
		  id: img.idDb,
		  orden: idx + 1
		}));
	
	  if (ordenIds.length === 0) return;
	
	  try {
		const response = await fetch("app/services/inmuebles_media_orden_api.php", {
		  method: "POST",
		  headers: { "Content-Type": "application/json" },
		  body: JSON.stringify({
			inmueble_id: INMUEBLE_ID,
			items: ordenIds
		  })
		});
	
		const result = await response.json();
		if (result.ok) {
		  console.log("✅ Orden actualizado en servidor");
		} else {
		  console.error("❌ Error al guardar el orden:", result.message);
		}
	  } catch (e) {
		console.error("❌ Error de red al guardar orden:", e);
	  }
	}


  async function moverImagen(index, delta) {
    const nuevoIndex = index + delta;
    if (nuevoIndex < 0 || nuevoIndex >= imagenesSeleccionadas.length) return;

    // 1. Intercambio de posición en el array local
    const temp = imagenesSeleccionadas[index];
    imagenesSeleccionadas[index] = imagenesSeleccionadas[nuevoIndex];
    imagenesSeleccionadas[nuevoIndex] = temp;

    // 2. Renderizar cambios visuales de inmediato
    renderizarImagenes();
	await guardarOrdenEnServidor();

    // 3. Guardar orden en el servidor (solo imágenes ya existentes en BD)
    const ordenIds = imagenesSeleccionadas
      .filter(img => img.idDb)
      .map((img, idx) => ({
        id: img.idDb,
        orden: idx + 1
      }));

    if (ordenIds.length > 0) {
      try {
        const response = await fetch("app/services/inmuebles_media_orden_api.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            inmueble_id: INMUEBLE_ID,
            items: ordenIds
          })
        });

        const result = await response.json();
        if (result.ok) {
          console.log("✅ Orden actualizado en servidor");
        } else {
          console.error("❌ Error al guardar el orden:", result.message);
        }
      } catch (e) {
        console.error("❌ Error de red al guardar orden:", e);
      }
    }
  }

	function eliminarImagen(index) {
	  console.log('>>> eliminarImagen llamado, index =', index);
	  const img = imagenesSeleccionadas[index];
	  console.log('>>> img =', img);
	
	  if (img && img.idDb) {
		if (!imagenesParaBorrar.includes(img.idDb)) {
		  imagenesParaBorrar.push(img.idDb);
		}
	  }
	  console.log('>>> imagenesParaBorrar =', imagenesParaBorrar);
	
	  imagenesSeleccionadas.splice(index, 1);
	  renderizarImagenes();
	}


  // ==========================
  // Manejo de imágenes
  // ==========================
  function validarArchivoImagen(file) {
    if (!file.type.startsWith("image/")) {
      alert(`El archivo "${file.name}" no es una imagen válida.`);
      return false;
    }
    const mimeOk =
      file.type === "image/jpeg" ||
      file.type === "image/png" ||
      file.type === "image/webp";
    if (!mimeOk) {
      alert(`Formato no permitido para "${file.name}". Solo JPG, PNG o WEBP.`);
      return false;
    }
    return true;
  }

  function manejarArchivosImagenes(files) {
    if (!files || !files.length) return;

    const actuales = imagenesSeleccionadas.length;
    const disponibles = 20 - actuales;
    if (disponibles <= 0) {
      alert("Ya tienes 20 imágenes seleccionadas. No puedes agregar más.");
      return;
    }

    const lista = Array.from(files).slice(0, disponibles);

    lista.forEach(file => {
      if (!validarArchivoImagen(file)) return;

      const reader = new FileReader();
      reader.onload = e => {
        const url = e.target.result;
        imagenesSeleccionadas.push({
          idTmp: crearIdTemporal(),
          name: file.name,
          file,
          previewUrl: url
        });
        renderizarImagenes();
      };
      reader.readAsDataURL(file);
    });
  }

  // Click en dropzone abre input
  if (dropzoneImagenes && inputImagenes) {
    dropzoneImagenes.addEventListener("click", () => {
      inputImagenes.click();
    });

    // Drag & drop básico
    dropzoneImagenes.addEventListener("dragover", e => {
      e.preventDefault();
      dropzoneImagenes.classList.add("border-blue-400", "bg-blue-50");
    });

    dropzoneImagenes.addEventListener("dragleave", e => {
      e.preventDefault();
      dropzoneImagenes.classList.remove("border-blue-400", "bg-blue-50");
    });

    dropzoneImagenes.addEventListener("drop", e => {
      e.preventDefault();
      dropzoneImagenes.classList.remove("border-blue-400", "bg-blue-50");
      const files = e.dataTransfer ? e.dataTransfer.files : null;
      manejarArchivosImagenes(files);
    });
  }

  if (inputImagenes) {
    inputImagenes.addEventListener("change", e => {
      manejarArchivosImagenes(e.target.files);
      // Reseteamos el input para permitir seleccionar el mismo archivo de nuevo
      e.target.value = "";
    });
  }

  // ==========================
  // Guardar media (solo imágenes nuevas)
  // ==========================
  async function guardarMedia() {
  console.log(">>> guardarMedia EJECUTADA");

  if (!INMUEBLE_ID || INMUEBLE_ID <= 0) {
    console.warn("No hay INMUEBLE_ID válido para guardar media.");
    return { ok: false, message: "Sin ID de inmueble" };
  }

  const formData = new FormData();
  formData.append("inmueble_id", INMUEBLE_ID);

  // Imágenes nuevas: enviar también el orden actual
  imagenesSeleccionadas
    .filter(img => !img.esServidor && img.file)
    .forEach((img, index) => {
      formData.append("imagenes[]", img.file, img.name);
      formData.append("orden_imagenes[]", index + 1); // 1,2,3,...
    });

  // IDs de imágenes existentes que el usuario borró
  console.log("ANTES DE ENVIAR, imagenesParaBorrar =", imagenesParaBorrar);
  if (imagenesParaBorrar.length > 0) {
    const jsonEliminar = JSON.stringify(imagenesParaBorrar);
    console.log("APPEND eliminar_ids =", jsonEliminar);
    formData.append("eliminar_ids", jsonEliminar);
  } else {
    console.log("NO HAY imagenesParaBorrar, no se envía eliminar_ids");
  }

  // DEBUG: ver TODO lo que va en el FormData
  for (const [k, v] of formData.entries()) {
    console.log("FORMDATA:", k, v);
  }

  try {
    const res = await fetch("index.php?page=inmuebles_media_subir", {
      method: "POST",
      body: formData
    });
    const data = await res.json();
    console.log("RESPUESTA guardarMedia:", data);

    if (!data.ok) {
      console.error("Error en guardarMedia:", data);
      return data;
    }

    // Recargar media desde servidor
    await cargarMediaExistente();
    // Limpiar lista de borrados
    imagenesParaBorrar = [];

    return data;
  } catch (e) {
    console.error("Excepción en guardarMedia:", e);
    return { ok: false, message: "Error de red/servidor" };
  }
}

  // Exponer la función globalmente para que el JS principal pueda llamarla
  window.guardarInmuebleMedia = guardarMedia;

  // Cargar media existente al iniciar (si hay ID)
  cargarMediaExistente();

  // TODO futuro: aplicar resize antes de subir si lo deseas
});
// JavaScript Document