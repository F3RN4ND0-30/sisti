function actualizarEstado(selectElement, idIncidente) {
  const nuevoEstado = selectElement.value;

  fetch("/helpdesk_mpp2.0/backend/php/desk/actualizar_estado.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id_incidente: idIncidente,
      nuevo_estado: nuevoEstado,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.exito) {
        // 🔁 Actualiza estadísticas
        actualizarEstadisticas();

        // 🎨 Actualiza clase del <select>
        selectElement.classList.remove("pendiente", "proceso", "resuelto");
        selectElement.classList.add(nuevoEstado);
      } else {
        alert("Error: " + data.mensaje);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function actualizarEstadisticas() {
  fetch("/HelpDesk_MPP2.0/backend/php/desk/obtener_estadisticas.php")
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("total-hoy").textContent = data.total_hoy ?? 0;
      document.getElementById("pendientes").textContent = data.pendientes ?? 0;
      document.getElementById("proceso").textContent = data.proceso ?? 0;
      document.getElementById("resueltos").textContent = data.resueltos ?? 0;
    })
    .catch((error) => {
      console.error("Error al actualizar estadísticas:", error);
    });
}

document.addEventListener("DOMContentLoaded", function () {
  actualizarEstadisticas(); // Llama al cargar la página
});
