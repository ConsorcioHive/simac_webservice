// ========================================
// VALIDACIÓN COMPLETA DEL FORMULARIO
// ========================================

// ID global del contenedor de errores
const errorContainerId = 'empresa-error-container';

// Listener principal: validar TODO al hacer submit final
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="index.php?page=empresas_guardar"]');
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;

    submitBtn.addEventListener('click', function(e) {
        // Limpiar errores previos
        limpiarErrores();

        const errores = validarFormularioCompleto();
        console.log('DEBUG ERRORES:', JSON.stringify(errores, null, 2));

        if (errores.length > 0) {
            e.preventDefault();

            // Mostrar errores en Paso 4
            mostrarErrores(errores);

            // Ir automáticamente al paso de Preferencias (step-6)
            if (typeof showStep === 'function') {
                showStep(6);
            } else if (typeof mostrarPaso === 'function') {
                mostrarPaso(4);
            }

            return false;
        }

        // Si no hay errores, se deja que el form se envíe normalmente
    });
});

// -------- Función principal de validación --------
function validarFormularioCompleto() {
    let errores = [];

    // PASO 1
    const paso1Errores = validarPaso1();
    if (paso1Errores.length > 0) {
        errores.push({
            paso: 1,
            nombre: 'PASO 1: Datos Básicos',
            errores: paso1Errores
        });
    }

    // PASO 2
    const paso2Errores = validarPaso2();
    if (paso2Errores.length > 0) {
        errores.push({
            paso: 2,
            nombre: 'PASO 2: Ubicación',
            errores: paso2Errores
        });
    }

    // PASO 3 (opcional)
    const paso3Errores = validarPaso3();
    if (paso3Errores.length > 0) {
        errores.push({
            paso: 3,
            nombre: 'PASO 3: Branding',
            errores: paso3Errores
        });
    }

    // PASO 5 (Administración — sin validaciones obligatorias)
    const paso5Errores = validarPaso5();
    if (paso5Errores.length > 0) {
        errores.push({
            paso: 5,
            nombre: 'PASO 5: Administración',
            errores: paso5Errores
        });
    }

    // PASO 6
    const paso6Errores = validarPaso6();
    if (paso6Errores.length > 0) {
        errores.push({
            paso: 6,
            nombre: 'PASO 6: Preferencias',
            errores: paso6Errores
        });
    }

    return errores;
}

// -------- VALIDACIONES POR PASO --------

function validarPaso1() {
    let errores = [];

    // Código empresa
    let codigo = document.querySelector('input[name="company_code"]');
    if (!codigo || !codigo.value.trim()) {
        errores.push('Código de empresa requerido');
    }

    // Nombre
    let nombre = document.querySelector('input[name="nombre"]');
    if (!nombre || !nombre.value.trim()) {
        errores.push('Nombre de la empresa requerido');
    }

    // Tipo
    let tipo = document.querySelector('select[name="tipo"]');
    if (!tipo || tipo.value === '') {
        errores.push('Tipo de empresa requerido');
    }

    // RIF
    let rif = document.querySelector('input[name="rif"]');
    if (!rif || !rif.value.trim()) {
        errores.push('RIF es requerido');
    } else if (!validarRIF(rif.value)) {
        errores.push('RIF inválido (debe comenzar con J, V, E o G y seguir de dígitos)');
    }

    // Teléfono
    let telefono = document.querySelector('input[name="telefono"]');
    if (!telefono || !telefono.value.trim()) {
        errores.push('Teléfono requerido');
    }

    // Email
    let email = document.querySelector('input[name="email"]');
    if (!email || !email.value.trim()) {
        errores.push('Email requerido');
    } else if (!validarEmail(email.value)) {
        errores.push('Email inválido');
    }

    // Descripción
    let descripcion = document.querySelector('textarea[name="empresa_description"]');
    if (!descripcion || !descripcion.value.trim()) {
        errores.push('Descripción requerida');
    }

    return errores;
}

function validarPaso2() {
    let errores = [];

    // País
    let pais = document.querySelector('select[name="codigo_pais"]');
    if (!pais || !pais.value || pais.value === 'Selecciona país...') {
        errores.push('País requerido');
    }

    // Estado
    let estado = document.querySelector('select[name="codigo_estado"]');
    if (!estado || !estado.value || estado.value === 'Primero selecciona país...') {
        errores.push('Estado/Región requerido');
    }

    // Municipio
    let municipio = document.querySelector('select[name="codigo_municipio"]');
    if (!municipio || !municipio.value || municipio.value === 'Primero selecciona estado...') {
        errores.push('Municipio/Ciudad requerido');
    }

    // Dirección fiscal
    let direccion = document.querySelector('textarea[name="direccion_fiscal"]');
    if (!direccion || !direccion.value.trim()) {
        errores.push('Dirección fiscal requerida');
    }

    // Moneda
    let moneda = document.querySelector('select[name="moneda_codigo"]');
    if (!moneda || !moneda.value || moneda.value === 'Selecciona primero municipio...') {
        errores.push('Moneda requerida');
    }

    return errores;
}

function validarPaso3() {
    let errores = [];
    // Paso 3 opcional
    return errores;
}

function validarPaso5() {
    let errores = [];
    // Todos los campos son opcionales
    return errores;
}

function validarPaso6() {
    let errores = [];

    // Idioma
    let idioma = document.querySelector('select[name="idioma"]');
    if (!idioma || idioma.value === '' || idioma.value === 'Selecciona un idioma...') {
        errores.push('Idioma requerido');
    }

    // Timezone
    let timezone = document.querySelector('select[name="timezone"]');
    if (!timezone || timezone.value === '' || timezone.value === 'Selecciona zona horaria...') {
        errores.push('Zona horaria requerida');
    }

    // Términos y condiciones
    let terminos = document.querySelector('input[name="acepta_terminos"]');
    if (!terminos || !terminos.checked) {
        errores.push('Debes aceptar los términos y condiciones');
    }

    // Privacidad
    let privacidad = document.querySelector('input[name="acepta_privacidad"]');
    if (!privacidad || !privacidad.checked) {
        errores.push('Debes aceptar la política de privacidad');
    }

    return errores;
}

// -------- FUNCIONES DE APOYO --------

function validarRIF(rif) {
    rif = rif.replace(/-/g, '');
    return /^[JVEG]\d{7,10}$/.test(rif);
}

function validarEmail(email) {
    let re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function mostrarErrores(erroresAgrupados) {
    limpiarErrores();

    const stepEl = document.querySelector('#step-6') || document;
    let container = document.getElementById(errorContainerId);

    if (!container) {
        container = document.createElement('div');
        container.id = errorContainerId;
        container.style.marginTop = '0';
        container.style.paddingTop = '0';
        container.style.marginBottom = '0';
        stepEl.prepend(container);
    }

    let html = `
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-top: 0 !important; margin-bottom: 1.5rem !important; padding-top: 1rem !important; display: flex; flex-wrap: wrap; align-items: flex-start; gap: 2rem;">
    `;

    erroresAgrupados.forEach(grupo => {
        html += `
            <div style="flex: 0 1 auto; white-space: normal; word-wrap: break-word;">
                <h6 class="text-danger" style="margin-bottom: 0.5rem;">
                    <a href="#" onclick="mostrarPaso(${grupo.paso}); return false;" style="color: inherit; text-decoration: none;">
                        ${grupo.nombre}
                    </a>
                </h6>
                <p style="font-size: 0.9rem; font-style: italic; color: #333; margin: 0; white-space: normal; word-wrap: break-word;">
                    ${grupo.errores.join(', ')}
                </p>
            </div>
        `;
    });

    html += `
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    container.innerHTML = html;
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function limpiarErrores() {
    let container = document.getElementById(errorContainerId);
    if (container) {
        container.innerHTML = '';
    }
}

function mostrarPaso(paso) {
    // currentStep global la maneja el wizard jQuery
    currentStep = paso;
    if (typeof showStep === 'function') {
        showStep(paso);
    } else if (typeof setStep === 'function') {
        setStep(paso);
    } else if (typeof goToStep === 'function') {
        goToStep(paso);
    } else {
        console.log('Función de paso no encontrada. Pasos disponibles:', window);
    }
}

// Limpieza de guiones en RIF antes de guardar
document.querySelector('input[name="rif"]')?.addEventListener('change', function() {
    this.value = this.value.replace(/-/g, '');
});

// ========================================
// WIZARD (jQuery) para steps
// ========================================
var currentStep = 1;   // global para usar también en mostrarPaso
var totalSteps = 6;

$(document).ready(function () {

  function isStepVisible(step) {
    if (step === 4) return $('#step-monedas-nav').is(':visible');
    if (step === 5) return $('#step-admin-nav').is(':visible');
    return true;
  }

  $(document).on('click', '.nextBtn', function () {
    if (!validateStep(currentStep)) {
      return false;
    }

    var next = currentStep + 1;
    while (next < totalSteps && !isStepVisible(next)) {
      next++;
    }
    if (next <= totalSteps) {
      currentStep = next;
      showStep(currentStep);
    }
  });

  $(document).on('click', '.prevBtn', function () {
    var prev = currentStep - 1;
    while (prev > 1 && !isStepVisible(prev)) {
      prev--;
    }
    if (prev >= 1) {
      currentStep = prev;
      showStep(currentStep);
    }
  });

  $('.stepwizard-step a').click(function (e) {
    e.preventDefault();
    var stepNum = parseInt($(this).text());

    currentStep = stepNum;
    showStep(currentStep);
  });

  function showStep(step) {
    $('.setup-content').hide();
    $('#step-' + step).show();
    updateWizardButtons(step);
  }

  function updateWizardButtons(step) {
    $('.stepwizard-step a')
      .removeClass('btn-primary btn-light')
      .attr('style', 'background:#e9ecef!important;color:#495057!important;border-color:#ced4da!important;')
      .filter('[href="#step-' + step + '"]')
      .attr('style', 'background:#1e3a5f!important;color:#fff!important;border-color:#1e3a5f!important;');
  }

  function validateStep(step) {
    var isValid = true;

    if (step === 1) {
      var nombre = $('input[name="nombre"]').val().trim();
      if (!nombre) {
        alert('El nombre de la empresa es obligatorio.');
        return false;
      }
    }

    if (step === 2) {
      var pais = $('select[name="codigo_pais"]').val();
      var estado = $('select[name="codigo_estado"]').val();
      var municipio = $('select[name="codigo_municipio"]').val();
      var moneda = $('select[name="moneda_codigo"]').val();

      if (!pais || !estado || !municipio) {
        alert('País, Estado y Municipio son obligatorios.');
        return false;
      }

      if (!moneda) {
        alert('Debes seleccionar una moneda.');
        return false;
      }
    }

    if (step === 3) {
      // sin validaciones obligatorias por ahora
      return true;
    }

    return isValid;
  }

  // Inicializar: mostrar Step 1
  showStep(1);
});

// ========================================
// PREVIEW LOGO Y TARJETA PRESENTACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function () {
  // PREVIEW LOGO
  var inputLogo   = document.getElementById('logo');
  var previewLogo = document.getElementById('logo_preview');

  if (inputLogo && previewLogo) {
    inputLogo.addEventListener('change', function (event) {
      var file = event.target.files[0];
      if (!file) return;

      if (!file.type.match('image.*')) {
        alert('Selecciona un archivo de imagen válido para el logo.');
        inputLogo.value = '';
        return;
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        previewLogo.src = e.target.result;
        previewLogo.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  }

  // PREVIEW TARJETA
  var inputCard   = document.getElementById('tarjeta_presentacion');
  var previewCard = document.getElementById('business_card_preview');
  var infoCard    = document.getElementById('business_card_info');

  if (inputCard && (previewCard || infoCard)) {
    inputCard.addEventListener('change', function (event) {
      var file = event.target.files[0];
      if (!file) return;

      // Imagen
      if (file.type.match('image.*')) {
        var reader = new FileReader();
        reader.onload = function (e) {
          if (previewCard) {
            previewCard.src = e.target.result;
            previewCard.style.display = 'block';
          }
          if (infoCard) {
            infoCard.textContent = '';
            infoCard.style.display = 'none';
          }
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        // PDF
        if (previewCard) {
          previewCard.style.display = 'none';
          previewCard.src = '';
        }
        if (infoCard) {
          infoCard.textContent = 'Archivo PDF seleccionado: ' + file.name;
          infoCard.style.display = 'block';
        }
      } else {
        alert('Formato no soportado para la tarjeta. Usa imagen o PDF.');
        inputCard.value = '';
        if (previewCard) {
          previewCard.style.display = 'none';
          previewCard.src = '';
        }
        if (infoCard) {
          infoCard.textContent = '';
          infoCard.style.display = 'none';
        }
      }
    });
  }
});
