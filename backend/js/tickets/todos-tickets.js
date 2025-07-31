/**
 * TODOS-TICKETS.JS - Sistema Completo Unificado
 */

let table;
let selectizeArea;
let notificacionActiva = false;
const loadingOverlay = document.getElementById("loadingOverlay");

// Mostrar/Ocultar loading
function showLoading() {
  if (loadingOverlay) loadingOverlay.classList.add("active");
}

function hideLoading() {
  if (loadingOverlay) loadingOverlay.classList.remove("active");
}

// ===== INICIALIZACIÓN PRINCIPAL =====
document.addEventListener("DOMContentLoaded", function () {
  inicializarTabla();
  inicializarSelectize();
  inicializarFiltrosBasicos();
  inicializarFiltrosAvanzados();
  inicializarBotonesFecha();
});

// Inicializar DataTable
function inicializarTabla() {
  table = $("#ticketsTable").DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
    },
    responsive: true,
    pageLength: 25,
    order: [[5, "desc"]],
    columnDefs: [
      { targets: [3], width: "200px", className: "description-column" },
      { targets: [4], orderable: false, className: "estado-column" },
      { targets: [0], className: "ticket-column" },
      { targets: [5], className: "date-column" },
    ],
    drawCallback: function () {
      // Aplicar animaciones a las filas
      $(this.api().table().node())
        .find("tbody tr")
        .each(function (index) {
          $(this).css("--row-index", index + 1);
          $(this).addClass("fade-in");
        });
    },
  });
}

// Inicializar Selectize para áreas
function inicializarSelectize() {
  console.log("Inicializando Selectize...");

  if (typeof $.fn.selectize !== "undefined") {
    try {
      if ($("#filtroArea")[0].selectize) {
        $("#filtroArea")[0].selectize.destroy();
      }

      selectizeArea = $("#filtroArea").selectize({
        placeholder: "Escribir para buscar área...",
        allowEmptyOption: true,
        searchField: ["text"],
        onChange: function (valor) {
          aplicarFiltroArea(valor);
        },
      })[0].selectize;

      console.log("Selectize OK");
    } catch (error) {
      console.error("Error Selectize:", error);
      $("#filtroArea").on("change", function () {
        aplicarFiltroArea($(this).val());
      });
    }
  } else {
    console.log("Selectize no disponible");
    $("#filtroArea").on("change", function () {
      aplicarFiltroArea($(this).val());
    });
  }
}

// Inicializar filtros básicos (existentes)
function inicializarFiltrosBasicos() {
  // Filtro de estado mejorado
  $("#filtroEstado")
    .off("change")
    .on("change", function () {
      const estadoId = this.value;
      console.log("Estado seleccionado:", estadoId);
      aplicarFiltroEstado(estadoId);
    });

  // Limpiar filtros con animación
  $("#limpiarFiltros")
    .off("click")
    .on("click", function () {
      limpiarTodosFiltros();
    });
}

// Inicializar filtros avanzados
function inicializarFiltrosAvanzados() {
  // Búsqueda general
  $("#filtroBusqueda")
    .off("input")
    .on(
      "input",
      retrasarEjecucion(function () {
        const termino = $(this).val();
        table.search(termino).draw();
      }, 500)
    );

  // Fechas personalizadas
  $("#filtroFechaDesde, #filtroFechaHasta")
    .off("change")
    .on("change", function () {
      aplicarFiltroFechaPersonalizada();
    });

  // Toggle filtros avanzados
  $("#filtersToggle")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      alternarFiltrosAvanzados();
    });
}

// Inicializar botones de fecha rápida
function inicializarBotonesFecha() {
  $(document)
    .off("click", ".date-quick-btn")
    .on("click", ".date-quick-btn", function (e) {
      e.preventDefault();

      $(".date-quick-btn").removeClass("active");
      $(this).addClass("active");

      const filtro = $(this).data("filter");
      aplicarFiltroFechaRapida(filtro);
    });
}

// ===== FILTROS =====

// Filtro de estado por ID
function aplicarFiltroEstado(estadoId) {
  // Limpiar filtros anteriores de estado
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(
    (fn) => fn.nombre !== "estado"
  );

  if (estadoId && estadoId !== "") {
    const filtroEstado = function (settings, data, dataIndex) {
      const fila = $(settings.nTable).find("tbody tr").eq(dataIndex);
      const estadoFila = fila.attr("data-estado-id");
      return estadoFila == estadoId;
    };
    filtroEstado.nombre = "estado";
    $.fn.dataTable.ext.search.push(filtroEstado);
  }

  table.draw();
}

// Filtro de área
function aplicarFiltroArea(areaNombre) {
  if (!areaNombre || areaNombre === "") {
    table.column(2).search("").draw();
    return;
  }
  table
    .column(2)
    .search("^" + areaNombre + "$", true, false)
    .draw();
}

// Filtro de fecha personalizada
function aplicarFiltroFechaPersonalizada() {
  const desde = $("#filtroFechaDesde").val();
  const hasta = $("#filtroFechaHasta").val();

  // Limpiar filtros de fecha anteriores
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(
    (fn) => fn.nombre !== "fecha"
  );

  if (desde || hasta) {
    $(".date-quick-btn").removeClass("active");

    const filtroFecha = function (settings, data, dataIndex) {
      const fechaColumna = data[5];
      if (!fechaColumna) return true;

      try {
        const partes = fechaColumna.split(" ")[0].split("/");
        const fecha = new Date(partes[2], partes[1] - 1, partes[0]);

        const fechaDesde = desde ? new Date(desde) : null;
        const fechaHasta = hasta ? new Date(hasta + "T23:59:59") : null;

        if (fechaDesde && fechaHasta) {
          return fecha >= fechaDesde && fecha <= fechaHasta;
        } else if (fechaDesde) {
          return fecha >= fechaDesde;
        } else if (fechaHasta) {
          return fecha <= fechaHasta;
        }
        return true;
      } catch (error) {
        return true;
      }
    };

    filtroFecha.nombre = "fecha";
    $.fn.dataTable.ext.search.push(filtroFecha);
  }

  table.draw();
}

// Filtro de fecha rápida
function aplicarFiltroFechaRapida(tipoFiltro) {
  // Limpiar filtros de fecha anteriores
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(
    (fn) => fn.nombre !== "fecha"
  );

  const hoy = new Date();
  let desde = "";
  let hasta = "";

  switch (tipoFiltro) {
    case "todos":
      $("#filtroFechaDesde").val("");
      $("#filtroFechaHasta").val("");
      table.draw();
      return;

    case "hoy":
      desde = hasta = formatearFecha(hoy);
      break;

    case "ayer":
      const ayer = new Date(hoy.getTime() - 24 * 60 * 60 * 1000);
      desde = hasta = formatearFecha(ayer);
      break;

    case "esta-semana":
      const inicioSemana = new Date(hoy);
      inicioSemana.setDate(hoy.getDate() - hoy.getDay());
      desde = formatearFecha(inicioSemana);
      hasta = formatearFecha(hoy);
      break;

    case "semana-pasada":
      const finSemanaPasada = new Date(hoy);
      finSemanaPasada.setDate(hoy.getDate() - hoy.getDay() - 1);
      const inicioSemanaPasada = new Date(finSemanaPasada);
      inicioSemanaPasada.setDate(finSemanaPasada.getDate() - 6);
      desde = formatearFecha(inicioSemanaPasada);
      hasta = formatearFecha(finSemanaPasada);
      break;

    case "este-mes":
      const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      desde = formatearFecha(inicioMes);
      hasta = formatearFecha(hoy);
      break;
  }

  $("#filtroFechaDesde").val(desde);
  $("#filtroFechaHasta").val(hasta);

  if (desde || hasta) {
    const filtroFecha = function (settings, data, dataIndex) {
      const fechaColumna = data[5];
      if (!fechaColumna) return true;

      try {
        const partes = fechaColumna.split(" ")[0].split("/");
        const fecha = new Date(partes[2], partes[1] - 1, partes[0]);
        const fechaDesde = desde ? new Date(desde) : null;
        const fechaHasta = hasta ? new Date(hasta + "T23:59:59") : null;

        if (fechaDesde && fechaHasta) {
          return fecha >= fechaDesde && fecha <= fechaHasta;
        } else if (fechaDesde) {
          return fecha >= fechaDesde;
        } else if (fechaHasta) {
          return fecha <= fechaHasta;
        }
        return true;
      } catch (error) {
        return true;
      }
    };

    filtroFecha.nombre = "fecha";
    $.fn.dataTable.ext.search.push(filtroFecha);
  }

  table.draw();
}

// ===== UTILIDADES =====

// Limpiar todos los filtros
function limpiarTodosFiltros() {
  // Limpiar inputs básicos
  $("#filtroEstado, #filtroBusqueda, #filtroFechaDesde, #filtroFechaHasta").val(
    ""
  );

  // Limpiar Selectize o select normal
  if (selectizeArea) {
    selectizeArea.setValue("");
  } else {
    $("#filtroArea").val("");
  }

  // Limpiar botones de fecha
  $(".date-quick-btn").removeClass("active");
  $('.date-quick-btn[data-filter="todos"]').addClass("active");

  // Limpiar todos los filtros de DataTable
  $.fn.dataTable.ext.search = [];
  table.search("").columns().search("").draw();

  // Efecto visual
  $("#limpiarFiltros").addClass("btn-success");
  setTimeout(() => $("#limpiarFiltros").removeClass("btn-success"), 1000);

  mostrarNotificacion("Filtros limpiados correctamente", "success");
}

// Toggle filtros avanzados
function alternarFiltrosAvanzados() {
  const contenido = $("#filtersContent");
  const boton = $("#filtersToggle");

  if (contenido.hasClass("collapsed")) {
    contenido.removeClass("collapsed").addClass("expanded");
    boton.html('<i class="material-icons">expand_less</i> Ocultar Filtros');
  } else {
    contenido.removeClass("expanded").addClass("collapsed");
    boton.html('<i class="material-icons">expand_more</i> Más Filtros');
  }
}

// Formatear fecha para input
function formatearFecha(fecha) {
  const año = fecha.getFullYear();
  const mes = String(fecha.getMonth() + 1).padStart(2, "0");
  const dia = String(fecha.getDate()).padStart(2, "0");
  return `${año}-${mes}-${dia}`;
}

// Retrasar ejecución (debounce)
function retrasarEjecucion(func, espera) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func.apply(this, args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, espera);
  };
}

// ===== CAMBIO DE ESTADO =====

// Cambiar estado de ticket
function cambiarEstadoDirecto(selectElement) {
  const idIncidente = selectElement.getAttribute("data-id");
  const nuevoEstadoTexto = selectElement.value.toLowerCase();
  const estadoOriginal = selectElement.getAttribute("data-original");

  if (selectElement.value === estadoOriginal) return;

  // Mapeo de estados
  let estadoMapeado = "";
  switch (nuevoEstadoTexto) {
    case "pendiente":
      estadoMapeado = "pendiente";
      break;
    case "en proceso":
      estadoMapeado = "proceso";
      break;
    case "resuelto":
      estadoMapeado = "resuelto";
      break;
    default:
      estadoMapeado = "pendiente";
  }

  const data = {
    id_incidente: parseInt(idIncidente),
    nuevo_estado: estadoMapeado,
  };

  // Efectos visuales
  selectElement.style.opacity = "0.6";
  selectElement.style.transform = "scale(0.95)";
  selectElement.disabled = true;

  const row = selectElement.closest("tr");
  row.style.backgroundColor = "rgba(52, 152, 219, 0.1)";

  showLoading();

  // Petición AJAX
  fetch("/helpdesk_mpp2.0/backend/php/desk/actualizar_estado.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading();

      if (data.exito) {
        // Actualizar clase del select
        selectElement.className =
          "estado-select " + getBadgeClass(selectElement.value);
        selectElement.setAttribute("data-original", selectElement.value);

        // Restaurar apariencia
        selectElement.style.opacity = "1";
        selectElement.style.transform = "scale(1)";
        selectElement.disabled = false;

        // Animación de éxito
        row.style.backgroundColor = "rgba(39, 174, 96, 0.1)";
        setTimeout(() => (row.style.backgroundColor = ""), 2000);

        // Actualizar estadísticas y notificar
        actualizarEstadisticasGenerales();
        mostrarNotificacion("Estado actualizado correctamente", "success");
      } else {
        revertirCambio(selectElement, estadoOriginal);
        mostrarNotificacion("Error: " + data.mensaje, "error");
      }
    })
    .catch((error) => {
      hideLoading();
      console.error("Error:", error);
      revertirCambio(selectElement, estadoOriginal);
      mostrarNotificacion("Error de conexión al actualizar el estado", "error");
    });
}

// Obtener clase CSS para badge de estado
function getBadgeClass(estado) {
  switch (estado.toLowerCase()) {
    case "pendiente":
      return "badge-pendiente";
    case "en proceso":
      return "badge-proceso";
    case "resuelto":
      return "badge-resuelto";
    default:
      return "badge-pendiente";
  }
}

// Revertir cambios en caso de error
function revertirCambio(selectElement, estadoOriginal) {
  selectElement.value = estadoOriginal;
  selectElement.style.opacity = "1";
  selectElement.style.transform = "scale(1)";
  selectElement.disabled = false;
  selectElement.closest("tr").style.backgroundColor = "";
}

// ===== ESTADÍSTICAS =====

// Actualizar estadísticas generales
function actualizarEstadisticasGenerales() {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try {
        const response = JSON.parse(xhr.responseText);
        if (response.total !== undefined) {
          animarNumero("total-general", response.total);
          animarNumero("pendientes-general", response.pendientes);
          animarNumero("proceso-general", response.proceso);
          animarNumero("resueltos-general", response.resueltos);
        }
      } catch (e) {
        console.log("Respuesta no es JSON válido");
      }
    }
  };
  xhr.send("ajax=estadisticas_generales");
}

// Animar números en estadísticas
function animarNumero(elementId, valorFinal) {
  const elemento = document.getElementById(elementId);
  if (!elemento) return;

  const valorInicial = parseInt(elemento.textContent) || 0;
  const diferencia = valorFinal - valorInicial;
  const duracion = 1000;
  const incremento = diferencia / (duracion / 16);

  let valorActual = valorInicial;

  const timer = setInterval(() => {
    valorActual += incremento;
    if (
      (incremento > 0 && valorActual >= valorFinal) ||
      (incremento < 0 && valorActual <= valorFinal)
    ) {
      valorActual = valorFinal;
      clearInterval(timer);
    }
    elemento.textContent = Math.round(valorActual);
  }, 16);
}

// ===== NOTIFICACIONES =====

// Mostrar notificación
function mostrarNotificacion(mensaje, tipo = "info") {
  if (notificacionActiva) return;

  notificacionActiva = true;

  let contenedor = document.getElementById("notificaciones");
  if (!contenedor) {
    contenedor = document.createElement("div");
    contenedor.id = "notificaciones";
    contenedor.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        `;
    document.body.appendChild(contenedor);
  }

  const notificacion = document.createElement("div");
  notificacion.className = `notificacion notificacion-${tipo}`;
  notificacion.style.cssText = `
        background: ${tipo === "success" ? "#d4edda" : "#f8d7da"};
        color: ${tipo === "success" ? "#155724" : "#721c24"};
        border: 1px solid ${tipo === "success" ? "#c3e6cb" : "#f5c6cb"};
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: all 0.3s ease;
        opacity: 0;
        cursor: pointer;
    `;
  notificacion.textContent = mensaje;

  contenedor.appendChild(notificacion);

  // Animar entrada
  setTimeout(() => {
    notificacion.style.transform = "translateX(0)";
    notificacion.style.opacity = "1";
  }, 100);

  // Auto-ocultar
  const ocultarNotificacion = () => {
    notificacion.style.transform = "translateX(100%)";
    notificacion.style.opacity = "0";
    setTimeout(() => {
      if (notificacion.parentNode) {
        notificacion.parentNode.removeChild(notificacion);
      }
      notificacionActiva = false;
    }, 300);
  };

  setTimeout(ocultarNotificacion, 3000);
  notificacion.addEventListener("click", ocultarNotificacion);
}

// ===== FUNCIONES AUXILIARES =====

// Cerrar modal
function cerrarModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.style.display = "none";
}

// Cerrar modal al hacer click fuera
window.onclick = function (event) {
  const modals = document.querySelectorAll(".modal-overlay");
  modals.forEach((modal) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
};
