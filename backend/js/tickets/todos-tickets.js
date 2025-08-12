/**
 * TODOS-TICKETS.JS - VERSIÓN OPTIMIZADA CON FILTRO AJAX
 * Filtro de estado funcionando con consulta directa a BD
 */

let table;
let selectizeArea;
let notificacionActiva = false;
const loadingOverlay = document.getElementById("loadingOverlay");

// ================== LOADING ==================
function showLoading() {
  if (loadingOverlay) loadingOverlay.classList.add("active");
}

function hideLoading() {
  if (loadingOverlay) loadingOverlay.classList.remove("active");
}

// ================== INICIALIZACIÓN ==================
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Iniciando sistema de tickets...");

  inicializarTabla();
  inicializarSelectize();
  inicializarFiltrosBasicos();
  inicializarFiltrosAvanzados();
  inicializarBotonesFecha();

  // Aplicar badges después de cargar todo
  setTimeout(inicializarBadges, 300);
  window.addEventListener("load", () => setTimeout(inicializarBadges, 500));
});

// ================== BADGES CORREGIDO ==================
function getBadgeClass(estado) {
  switch (estado) {
    case "Pendiente":
      return "badge-pendiente";
    case "En proceso":
      return "badge-proceso";
    case "Resuelto":
      return "badge-resuelto";
    default:
      return "badge-pendiente";
  }
}

function inicializarBadges() {
  const selects = document.querySelectorAll(".estado-select");

  if (selects.length === 0) {
    console.log("⚠️ No hay selects encontrados, reintentando...");
    setTimeout(inicializarBadges, 1000);
    return;
  }

  selects.forEach((select) => {
    const estado = select.value.trim();
    const claseCorrecta = getBadgeClass(estado);
    select.className = "estado-select " + claseCorrecta;
    console.log(`🎨 Badge aplicado: ${estado} -> ${claseCorrecta}`);
  });

  console.log(
    `✅ Badges aplicados correctamente a ${selects.length} elementos`
  );
}

// ================== DATATABLE MEJORADO ==================
function inicializarTabla() {
  if ($.fn.DataTable.isDataTable("#ticketsTable")) {
    $("#ticketsTable").DataTable().destroy();
  }

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
      setTimeout(inicializarBadges, 200);
      $(this.api().table().node())
        .find("tbody tr")
        .each((index, row) => {
          $(row)
            .addClass("fade-in")
            .css("--row-index", index + 1);
        });
    },
    initComplete: function () {
      console.log("📊 DataTable inicializada correctamente");
      setTimeout(inicializarBadges, 400);
    },
  });
}

// ================== SELECTIZE ==================
function inicializarSelectize() {
  if (typeof $.fn.selectize !== "undefined") {
    try {
      if ($("#filtroArea")[0].selectize) {
        $("#filtroArea")[0].selectize.destroy();
      }

      selectizeArea = $("#filtroArea").selectize({
        placeholder: "Escribir para buscar área...",
        allowEmptyOption: true,
        searchField: ["text"],
        onChange: aplicarFiltroArea,
      })[0].selectize;

      console.log("🔍 Selectize inicializado");
    } catch (e) {
      console.error("❌ Error Selectize:", e);
      $("#filtroArea").on("change", function () {
        aplicarFiltroArea($(this).val());
      });
    }
  } else {
    $("#filtroArea").on("change", function () {
      aplicarFiltroArea($(this).val());
    });
  }
}

// ================== FILTROS BÁSICOS ==================
function inicializarFiltrosBasicos() {
  $("#filtroEstado")
    .off("change")
    .on("change", function () {
      const estadoId = this.value;
      console.log("🔽 Filtro de estado seleccionado:", estadoId);
      aplicarFiltroEstado(estadoId);
    });

  $("#limpiarFiltros").off("click").on("click", limpiarTodosFiltros);
}

// ================== FILTROS AVANZADOS ==================
function inicializarFiltrosAvanzados() {
  $("#filtroBusqueda")
    .off("input")
    .on(
      "input",
      retrasarEjecucion(function () {
        const valor = $(this).val();
        table.search(valor).draw();
        console.log("🔍 Búsqueda aplicada:", valor);
      }, 500)
    );

  $("#filtroFechaDesde, #filtroFechaHasta")
    .off("change")
    .on("change", aplicarFiltroFechaPersonalizada);

  $("#filtersToggle").off("click").on("click", alternarFiltrosAvanzados);
}

function inicializarBotonesFecha() {
  $(document)
    .off("click", ".date-quick-btn")
    .on("click", ".date-quick-btn", function (e) {
      e.preventDefault();
      $(".date-quick-btn").removeClass("active");
      $(this).addClass("active");

      const filtro = $(this).data("filter");
      aplicarFiltroFechaRapida(filtro);
      console.log("📅 Filtro de fecha aplicado:", filtro);
    });
}

// ================== FILTRO ESTADO AJAX OPTIMIZADO ==================
function aplicarFiltroEstado(estadoId) {
  console.log("🔍 Aplicando filtro AJAX de estado:", estadoId);

  showLoading();

  $.ajax({
    url: "/sisti/backend/ajax/estadisticas_generales.php",
    method: "POST",
    data: { estado_id: estadoId },
    dataType: "json",
    success: function (response) {
      hideLoading();

      if (response.success) {
        // Limpiar tabla actual
        table.clear();

        // Agregar nuevos datos
        if (response.tickets && response.tickets.length > 0) {
          response.tickets.forEach(function (ticket) {
            const estadoClass = getBadgeClass(ticket.EstadoNombre);
            const fechaFormateada = formatearFecha(ticket.Fecha_Creacion);
            const descripcionCorta =
              ticket.Descripcion.length > 50
                ? ticket.Descripcion.substring(0, 50) + "..."
                : ticket.Descripcion;

            const fila = [
              `<span class='ticket-code'>${ticket.Codigo_Ticket}</span>`,
              `<div class='user-info'><span class='user-name'>${ticket.NombreCompleto}</span></div>`,
              `<span class='user-area'>${ticket.AreaNombre}</span>`,
              `<span class='description-text' title='${ticket.Descripcion}'>${descripcionCorta}</span>`,
              `<select class='estado-select ${estadoClass}' 
                      data-id='${ticket.Id_Incidentes}' 
                      onchange='cambiarEstadoDirecto(this)'
                      data-original='${ticket.EstadoNombre}'>
                 <option value='Pendiente'${ticket.EstadoNombre === "Pendiente" ? " selected" : ""
              }>Pendiente</option>
                 <option value='En proceso'${ticket.EstadoNombre === "En proceso" ? " selected" : ""
              }>En proceso</option>
                 <option value='Resuelto'${ticket.EstadoNombre === "Resuelto" ? " selected" : ""
              }>Resuelto</option>
               </select>`,
              `<span class='date-cell'>${fechaFormateada}</span>`,
            ];

            const newRow = table.row.add(fila);
            $(newRow.node()).attr("data-estado-id", ticket.EstadoId);
            $(newRow.node()).attr("data-area", ticket.AreaNombre);
          });
        }

        table.draw();
        setTimeout(inicializarBadges, 200);

        const estadoTexto = estadoId
          ? $("#filtroEstado option:selected").text()
          : "Todos";
        const cantidad = response.tickets ? response.tickets.length : 0;
        mostrarNotificacion(
          `Filtro "${estadoTexto}": ${cantidad} tickets encontrados`,
          "info"
        );

        console.log(`✅ Filtro aplicado: ${cantidad} tickets cargados`);
      } else {
        mostrarNotificacion(
          "Error al aplicar filtro: " +
          (response.message || "Error desconocido"),
          "error"
        );
      }
    },
    error: function (xhr, status, error) {
      hideLoading();
      console.error("Error AJAX:", error);
      mostrarNotificacion("Error de conexión al aplicar filtro", "error");
    },
  });
}

// ================== FUNCIÓN AUXILIAR FORMATEAR FECHA ==================
function formatearFecha(fechaString) {
  try {
    const fecha = new Date(fechaString);
    const dia = String(fecha.getDate()).padStart(2, "0");
    const mes = String(fecha.getMonth() + 1).padStart(2, "0");
    const año = fecha.getFullYear();
    const horas = String(fecha.getHours()).padStart(2, "0");
    const minutos = String(fecha.getMinutes()).padStart(2, "0");

    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
  } catch (error) {
    return fechaString;
  }
}

// ================== OTROS FILTROS (SIN CAMBIOS) ==================
function aplicarFiltroArea(area) {
  console.log("🏢 Aplicando filtro de área:", area);

  if (area && area !== "") {
    table
      .column(2)
      .search(`^${area.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")}$`, true, false)
      .draw();
    console.log("✅ Filtro de área aplicado:", area);
  } else {
    table.column(2).search("").draw();
    console.log("🔄 Filtro de área limpiado");
  }
}

function aplicarFiltroFechaPersonalizada() {
  const desde = $("#filtroFechaDesde").val();
  const hasta = $("#filtroFechaHasta").val();
  filtrarPorFechas(desde, hasta);
}

function aplicarFiltroFechaRapida(tipo) {
  const hoy = new Date();
  let desde = "";
  let hasta = "";

  const formato = (fecha) => fecha.toISOString().split("T")[0];

  switch (tipo) {
    case "todos":
      desde = "";
      hasta = "";
      break;
    case "hoy":
      desde = hasta = formato(hoy);
      break;
    case "ayer":
      const ayer = new Date(hoy);
      ayer.setDate(hoy.getDate() - 1);
      desde = hasta = formato(ayer);
      break;
    case "esta-semana":
      const inicioSemana = new Date(hoy);
      inicioSemana.setDate(hoy.getDate() - hoy.getDay());
      desde = formato(inicioSemana);
      hasta = formato(hoy);
      break;
    case "semana-pasada":
      const finSemanaAnterior = new Date(hoy);
      finSemanaAnterior.setDate(hoy.getDate() - hoy.getDay() - 1);
      const inicioSemanaAnterior = new Date(finSemanaAnterior);
      inicioSemanaAnterior.setDate(finSemanaAnterior.getDate() - 6);
      desde = formato(inicioSemanaAnterior);
      hasta = formato(finSemanaAnterior);
      break;
    case "este-mes":
      desde = formato(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
      hasta = formato(hoy);
      break;
  }

  $("#filtroFechaDesde").val(desde);
  $("#filtroFechaHasta").val(hasta);
  filtrarPorFechas(desde, hasta);
}

function filtrarPorFechas(desde, hasta) {
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(
    (fn) => fn.nombre !== "fecha"
  );

  if (desde || hasta) {
    const fechaDesde = desde ? new Date(desde) : null;
    const fechaHasta = hasta ? new Date(hasta + "T23:59:59") : null;

    const filtro = (settings, data, idx) => {
      try {
        const fechaTexto = data[5];
        const [fechaParte] = fechaTexto.split(" ");
        const [dia, mes, anio] = fechaParte.split("/");
        const fechaFila = new Date(anio, mes - 1, dia);

        const dentroDelRango =
          (!fechaDesde || fechaFila >= fechaDesde) &&
          (!fechaHasta || fechaFila <= fechaHasta);
        return dentroDelRango;
      } catch (error) {
        console.warn("Error al procesar fecha:", error);
        return true;
      }
    };

    filtro.nombre = "fecha";
    $.fn.dataTable.ext.search.push(filtro);
    console.log("📅 Filtro de fecha aplicado:", { desde, hasta });
  }

  table.draw();
}

// ================== UTILIDADES ==================
function limpiarTodosFiltros() {
  console.log("🧹 Limpiando filtros y recargando datos...");

  $("#filtroEstado, #filtroBusqueda, #filtroFechaDesde, #filtroFechaHasta").val(
    ""
  );

  if (selectizeArea) {
    selectizeArea.setValue("");
  } else {
    $("#filtroArea").val("");
  }

  $(".date-quick-btn").removeClass("active");
  $('.date-quick-btn[data-filter="todos"]').addClass("active");

  // Aplicar filtro vacío para recargar todos
  aplicarFiltroEstado("");

  $.fn.dataTable.ext.search = [];
  table.search("");

  const btnLimpiar = $("#limpiarFiltros");
  btnLimpiar.addClass("btn-success").text("✓ Limpiado");
  setTimeout(() => {
    btnLimpiar
      .removeClass("btn-success")
      .html('<i class="material-icons">clear</i> Limpiar');
  }, 1500);
}

function alternarFiltrosAvanzados() {
  const contenido = $("#filtersContent");
  const boton = $("#filtersToggle");

  contenido.toggleClass("collapsed expanded");

  const estaExpandido = contenido.hasClass("expanded");
  boton.html(
    estaExpandido
      ? '<i class="material-icons">expand_less</i> Ocultar Filtros'
      : '<i class="material-icons">expand_more</i> Más Filtros'
  );
}

function retrasarEjecucion(func, espera) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), espera);
  };
}

// ================== CAMBIO DE ESTADO ==================
function obtenerIdEstado(estado) {
  const mapeoEstados = {
    Pendiente: "1",
    "En proceso": "2",
    Resuelto: "3",
  };
  return mapeoEstados[estado] || "1";
}

function cambiarEstadoDirecto(select) {
  const id = select.dataset.id;
  const nuevoEstado = select.value.trim();
  const estadoOriginal = select.dataset.original;

  if (nuevoEstado === estadoOriginal) {
    console.log("🔄 Estado sin cambios:", nuevoEstado);
    return;
  }

  const row = select.closest("tr");

  console.log("🔄 Cambiando estado:", {
    id: id,
    de: estadoOriginal,
    a: nuevoEstado,
  });

  showLoading();
  select.style.opacity = "0.6";
  select.disabled = true;
  row.style.backgroundColor = "rgba(52,152,219,.1)";

  // Actualización AJAX
  $.ajax({
    url: "/sisti/backend/ajax/estadisticas_generales.php", // Cambia esta ruta a tu endpoint real
    method: "POST",
    data: {
      id: id,
      estado: nuevoEstado
    },
    dataType: "json",
    success: function (response) {
      hideLoading();

      if (response.success) {
        // Actualizar visualmente solo si el servidor confirma el cambio
        const nuevaClase = getBadgeClass(nuevoEstado);
        select.className = "estado-select " + nuevaClase;

        select.dataset.original = nuevoEstado;
        row.dataset.estadoId = obtenerIdEstado(nuevoEstado);

        row.style.backgroundColor = "rgba(39,174,96,.1)";
        setTimeout(() => (row.style.backgroundColor = ""), 2000);

        select.style.opacity = "1";
        select.disabled = false;

        actualizarEstadisticasGenerales();
        mostrarNotificacion(`Estado cambiado a: ${nuevoEstado}`, "success");

        console.log("✅ Estado actualizado exitosamente");
        location.reload();
      } else {
        // Revertir el cambio si hubo error en el backend
        revertirCambio(select, estadoOriginal, response.message || "Error al actualizar estado en BD");
      }
    },
    error: function (xhr, status, error) {
      hideLoading();
      revertirCambio(select, estadoOriginal, "Error de conexión al actualizar estado");
      console.error("Error AJAX:", error);
    }
  });
}

function revertirCambio(select, estadoOriginal, mensaje) {
  console.log("⚠️ Revirtiendo cambio:", mensaje);

  select.value = estadoOriginal;
  select.className = "estado-select " + getBadgeClass(estadoOriginal);
  select.style.opacity = "1";
  select.disabled = false;
  select.closest("tr").style.backgroundColor = "";

  mostrarNotificacion(mensaje, "error");
}

// ================== ESTADÍSTICAS ==================
function actualizarEstadisticasGenerales() {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "/sisti/frontend/tickets/gestickets/todos-tickets.php", true); // <- Ruta directa al PHP
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try {
        const respuesta = JSON.parse(xhr.responseText);

        ["total", "pendientes", "proceso", "resueltos"].forEach((key) => {
          const valor = respuesta[key];
          if (valor !== undefined) {
            animarNumero(`${key}-general`, valor);
          }
        });

        console.log("📊 Estadísticas actualizadas:", respuesta);
      } catch (error) {
        console.warn("⚠️ Error al actualizar estadísticas:", error);
      }
    }
  };

  xhr.send("ajax=estadisticas_generales");
}

function animarNumero(elementoId, valorFinal) {
  const elemento = document.getElementById(elementoId);
  if (!elemento) return;

  const valorInicial = parseInt(elemento.textContent) || 0;
  const diferencia = valorFinal - valorInicial;
  const pasos = Math.abs(diferencia);
  const incremento = diferencia / Math.max(pasos, 1);
  const duracion = 1000;
  const intervalo = duracion / Math.max(pasos, 1);

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
  }, Math.max(16, intervalo));
}

// ================== NOTIFICACIONES ==================
function mostrarNotificacion(mensaje, tipo = "info") {
  if (notificacionActiva) return;
  notificacionActiva = true;

  console.log(`🔔 Notificación [${tipo}]:`, mensaje);

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
      pointer-events: none;
    `;
    document.body.appendChild(contenedor);
  }

  const colores = {
    success: { bg: "#d4edda", color: "#155724", border: "#c3e6cb" },
    error: { bg: "#f8d7da", color: "#721c24", border: "#f5c6cb" },
    warning: { bg: "#fff3cd", color: "#856404", border: "#ffeaa7" },
    info: { bg: "#d1ecf1", color: "#0c5460", border: "#bee5eb" },
  };

  const colorConfig = colores[tipo] || colores.info;

  const notificacion = document.createElement("div");
  notificacion.className = `notificacion notificacion-${tipo}`;
  notificacion.style.cssText = `
    background: ${colorConfig.bg};
    color: ${colorConfig.color};
    border: 1px solid ${colorConfig.border};
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(100%);
    transition: all 0.3s ease;
    opacity: 0;
    cursor: pointer;
    pointer-events: auto;
    font-weight: 500;
  `;

  notificacion.textContent = mensaje;
  contenedor.appendChild(notificacion);

  setTimeout(() => {
    notificacion.style.transform = "translateX(0)";
    notificacion.style.opacity = "1";
  }, 100);

  const ocultar = () => {
    notificacion.style.transform = "translateX(100%)";
    notificacion.style.opacity = "0";
    setTimeout(() => {
      if (notificacion.parentNode) {
        notificacion.remove();
      }
      notificacionActiva = false;
    }, 300);
  };

  setTimeout(ocultar, 4000);
  notificacion.addEventListener("click", ocultar);
}

// ================== MODALES Y UTILIDADES ==================
function cerrarModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
    console.log("🗂️ Modal cerrado:", modalId);
  }
}

window.addEventListener("click", (evento) => {
  document.querySelectorAll(".modal-overlay").forEach((modal) => {
    if (evento.target === modal) {
      modal.style.display = "none";
    }
  });
});

// ================== DEBUG ==================
window.debugFiltros = function () {
  console.log("🔍 DEBUG: Verificando estructura de filtros");

  const selectEstado = document.getElementById("filtroEstado");
  console.log("📋 Select Estado:", selectEstado);
  console.log("📋 Opciones de estado:");
  Array.from(selectEstado.options).forEach((option) => {
    console.log(`  - Valor: "${option.value}", Texto: "${option.text}"`);
  });

  console.log("📋 Filas de la tabla:");
  const filas = document.querySelectorAll("#ticketsTable tbody tr");
  filas.forEach((fila, index) => {
    const estadoId = fila.getAttribute("data-estado-id");
    const select = fila.querySelector(".estado-select");
    const valorSelect = select ? select.value : "No select";
    console.log(
      `  Fila ${index}: data-estado-id="${estadoId}", select.value="${valorSelect}"`
    );
  });

  const totalFilas = table.rows().count();
  const filasVisibles = table.rows({ search: "applied" }).count();
  console.log(`📊 Total filas: ${totalFilas}, Visibles: ${filasVisibles}`);

  return {
    totalFilas,
    filasVisibles,
    filtrosActivos: $.fn.dataTable.ext.search.length,
  };
};

// ================== INICIALIZACIÓN GLOBAL ==================
console.log("📋 Módulo todos-tickets.js cargado correctamente");