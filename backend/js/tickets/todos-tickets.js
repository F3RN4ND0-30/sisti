/**
 * TODOS-TICKETS.JS - VERSIÓN OPTIMIZADA FINAL
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
  inicializarTabla();
  inicializarSelectize();
  inicializarFiltrosBasicos();
  inicializarFiltrosAvanzados();
  inicializarBotonesFecha();

  // Intento inicial
  inicializarBadges();

  // Refuerzo después de carga total
  window.addEventListener("load", () => setTimeout(inicializarBadges, 500));
});

// ================== BADGES ==================
function getBadgeClass(estado) {
  switch (estado) {
    case "Pendiente": return "badge-pendiente";
    case "En Proceso": return "badge-proceso";
    case "Resuelto": return "badge-resuelto";
    default: return "badge-default";
  }
}

function inicializarBadges() {
  const selects = document.querySelectorAll(".estado-select");
  if (selects.length === 0) {
    console.log("⚠️ No hay selects, reintento en 1s...");
    setTimeout(inicializarBadges, 1000);
    return;
  }
  selects.forEach((select) => {
    const clase = getBadgeClass(select.value);
    if (!select.classList.contains(clase)) {
      select.className = "estado-select " + clase;
    }
  });
  console.log(`✅ Badges aplicados a ${selects.length} elementos`);
}

// ================== DATATABLE ==================
function inicializarTabla() {
  table = $("#ticketsTable").DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
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
        .each((index, row) => $(row).addClass("fade-in").css("--row-index", index + 1));
    },
    initComplete: () => setTimeout(inicializarBadges, 400),
  });
}

// ================== SELECTIZE ==================
function inicializarSelectize() {
  if (typeof $.fn.selectize !== "undefined") {
    try {
      if ($("#filtroArea")[0].selectize) $("#filtroArea")[0].selectize.destroy();
      selectizeArea = $("#filtroArea")
        .selectize({
          placeholder: "Escribir para buscar área...",
          allowEmptyOption: true,
          searchField: ["text"],
          onChange: aplicarFiltroArea,
        })[0].selectize;
    } catch (e) {
      console.error("Error Selectize:", e);
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

// ================== FILTROS ==================
function inicializarFiltrosBasicos() {
  $("#filtroEstado")
    .off("change")
    .on("change", function () {
      aplicarFiltroEstado(this.value, $(this).find("option:selected").text());
    });

  $("#limpiarFiltros")
    .off("click")
    .on("click", limpiarTodosFiltros);
}

function inicializarFiltrosAvanzados() {
  $("#filtroBusqueda")
    .off("input")
    .on("input", retrasarEjecucion(function () {
      table.search($(this).val()).draw();
    }, 500));

  $("#filtroFechaDesde, #filtroFechaHasta").off("change").on("change", aplicarFiltroFechaPersonalizada);

  $("#filtersToggle").off("click").on("click", alternarFiltrosAvanzados);
}

function inicializarBotonesFecha() {
  $(document).off("click", ".date-quick-btn").on("click", ".date-quick-btn", function (e) {
    e.preventDefault();
    $(".date-quick-btn").removeClass("active");
    $(this).addClass("active");
    aplicarFiltroFechaRapida($(this).data("filter"));
  });
}

// ================== APLICAR FILTROS ==================
function aplicarFiltroEstado(estadoId) {
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.nombre !== "estado");
  if (estadoId) {
    const filtro = (settings, data, idx) =>
      $(settings.nTable).find("tbody tr").eq(idx).attr("data-estado-id") == estadoId;
    filtro.nombre = "estado";
    $.fn.dataTable.ext.search.push(filtro);
  }
  table.draw();
}

function aplicarFiltroArea(area) {
  table.column(2).search(area ? `^${area}$` : "", true, false).draw();
}

function aplicarFiltroFechaPersonalizada() {
  const desde = $("#filtroFechaDesde").val(), hasta = $("#filtroFechaHasta").val();
  filtrarPorFechas(desde, hasta);
}

function aplicarFiltroFechaRapida(tipo) {
  const hoy = new Date();
  let desde = "", hasta = "";

  const formato = f => f.toISOString().split("T")[0];

  switch (tipo) {
    case "todos": break;
    case "hoy": desde = hasta = formato(hoy); break;
    case "ayer": let ay = new Date(hoy); ay.setDate(hoy.getDate() - 1); desde = hasta = formato(ay); break;
    case "esta-semana": let ini = new Date(hoy); ini.setDate(hoy.getDate() - hoy.getDay()); desde = formato(ini); hasta = formato(hoy); break;
    case "semana-pasada": let fin = new Date(hoy); fin.setDate(hoy.getDate() - hoy.getDay() - 1); let ini2 = new Date(fin); ini2.setDate(fin.getDate() - 6); desde = formato(ini2); hasta = formato(fin); break;
    case "este-mes": desde = formato(new Date(hoy.getFullYear(), hoy.getMonth(), 1)); hasta = formato(hoy); break;
  }

  $("#filtroFechaDesde").val(desde);
  $("#filtroFechaHasta").val(hasta);
  filtrarPorFechas(desde, hasta);
}

function filtrarPorFechas(desde, hasta) {
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn.nombre !== "fecha");
  if (desde || hasta) {
    const fDesde = desde ? new Date(desde) : null, fHasta = hasta ? new Date(hasta + "T23:59:59") : null;
    const filtro = (s, d) => {
      try {
        const [dia, mes, anio] = d[5].split(" ")[0].split("/");
        const fecha = new Date(anio, mes - 1, dia);
        return (!fDesde || fecha >= fDesde) && (!fHasta || fecha <= fHasta);
      } catch { return true; }
    };
    filtro.nombre = "fecha";
    $.fn.dataTable.ext.search.push(filtro);
  }
  table.draw();
}

// ================== UTILIDADES ==================
function limpiarTodosFiltros() {
  $("#filtroEstado, #filtroBusqueda, #filtroFechaDesde, #filtroFechaHasta").val("");
  if (selectizeArea) selectizeArea.setValue(""); else $("#filtroArea").val("");
  $(".date-quick-btn").removeClass("active");
  $('.date-quick-btn[data-filter="todos"]').addClass("active");
  $.fn.dataTable.ext.search = [];
  table.search("").columns().search("").draw();
  $("#limpiarFiltros").addClass("btn-success");
  setTimeout(() => $("#limpiarFiltros").removeClass("btn-success"), 1000);
  mostrarNotificacion("Filtros limpiados correctamente", "success");
}

function alternarFiltrosAvanzados() {
  const c = $("#filtersContent"), b = $("#filtersToggle");
  c.toggleClass("collapsed expanded");
  b.html(c.hasClass("expanded") ? '<i class="material-icons">expand_less</i> Ocultar Filtros' : '<i class="material-icons">expand_more</i> Más Filtros');
}

function retrasarEjecucion(func, espera) {
  let t; return (...a) => { clearTimeout(t); t = setTimeout(() => func.apply(this, a), espera); };
}

// ================== CAMBIO DE ESTADO ==================
function obtenerIdEstado(estado) {
  return { "Pendiente": "1", "En Proceso": "2", "Resuelto": "3" }[estado] || "1";
}

function cambiarEstadoDirecto(select) {
  const id = select.dataset.id, nuevo = select.value, original = select.dataset.original;
  if (nuevo === original) return;

  const row = select.closest("tr");
  showLoading();
  select.style.opacity = "0.6"; select.disabled = true; row.style.backgroundColor = "rgba(52,152,219,.1)";

  // Petición AJAX
  fetch("/sishelpdesk/backend/php/desk/actualizar_estado.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id_incidente: +id, nuevo_estado: nuevo }),
  })
    .then(r => r.json())
    .then(r => {
      hideLoading();
      if (r.exito) {
        select.dataset.original = nuevo;
        select.className = "estado-select " + getBadgeClass(nuevo);
        row.dataset.estadoId = obtenerIdEstado(nuevo);
        row.style.backgroundColor = "rgba(39,174,96,.1)";
        setTimeout(() => row.style.backgroundColor = "", 2000);
        select.style.opacity = "1"; select.disabled = false;
        actualizarEstadisticasGenerales();
        mostrarNotificacion("Estado actualizado correctamente", "success");
      } else revertirCambio(select, original, r.mensaje);
    })
    .catch(() => { hideLoading(); revertirCambio(select, original, "Error de conexión"); });
}

function revertirCambio(select, original, msg) {
  select.value = original;
  select.className = "estado-select " + getBadgeClass(original);
  select.style.opacity = "1"; select.disabled = false;
  select.closest("tr").style.backgroundColor = "";
  mostrarNotificacion(msg, "error");
}

// ================== ESTADÍSTICAS ==================
function actualizarEstadisticasGenerales() {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = () => {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try {
        const r = JSON.parse(xhr.responseText);
        ["total", "pendientes", "proceso", "resueltos"].forEach(k => animarNumero(`${k}-general`, r[k]));
      } catch {}
    }
  };
  xhr.send("ajax=estadisticas_generales");
}

function animarNumero(id, fin) {
  const el = document.getElementById(id);
  if (!el) return;
  let ini = +el.textContent || 0, dif = fin - ini, paso = dif / (1000 / 16);
  const t = setInterval(() => {
    ini += paso;
    if ((paso > 0 && ini >= fin) || (paso < 0 && ini <= fin)) { ini = fin; clearInterval(t); }
    el.textContent = Math.round(ini);
  }, 16);
}

// ================== NOTIFICACIONES ==================
function mostrarNotificacion(msg, tipo = "info") {
  if (notificacionActiva) return;
  notificacionActiva = true;

  let c = document.getElementById("notificaciones");
  if (!c) { c = document.createElement("div"); c.id = "notificaciones"; c.style = "position:fixed;top:20px;right:20px;z-index:10000;max-width:400px"; document.body.appendChild(c); }

  const n = document.createElement("div");
  n.className = `notificacion notificacion-${tipo}`;
  n.style = `background:${tipo === "success" ? "#d4edda" : "#f8d7da"};color:${tipo === "success" ? "#155724" : "#721c24"};border:1px solid ${tipo === "success" ? "#c3e6cb" : "#f5c6cb"};border-radius:8px;padding:12px 16px;margin-bottom:10px;box-shadow:0 4px 12px rgba(0,0,0,.15);transform:translateX(100%);transition:all .3s ease;opacity:0;cursor:pointer;`;
  n.textContent = msg;
  c.appendChild(n);

  setTimeout(() => { n.style.transform = "translateX(0)"; n.style.opacity = "1"; }, 100);
  const ocultar = () => { n.style.transform = "translateX(100%)"; n.style.opacity = "0"; setTimeout(() => { n.remove(); notificacionActiva = false; }, 300); };
  setTimeout(ocultar, 3000);
  n.addEventListener("click", ocultar);
}

// ================== MODALES ==================
function cerrarModal(id) {
  const m = document.getElementById(id);
  if (m) m.style.display = "none";
}
window.onclick = e => document.querySelectorAll(".modal-overlay").forEach(m => { if (e.target === m) m.style.display = "none"; });
