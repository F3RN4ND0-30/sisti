async function cargarDashboard() {
  const response = await fetch(
    "/sisti/backend/php/reportes/dashboard_tickets.php"
  );
  const data = await response.json();

  document.getElementById("totalTickets").textContent = data.total;
  document.getElementById("ticketsPendientes").textContent = data.pendientes;
  document.getElementById("ticketsProceso").textContent = data.proceso;
  document.getElementById("ticketsResueltos").textContent = data.resueltos;

  // Gráfico por estado
  new Chart(document.getElementById("graficoEstados"), {
    type: "doughnut",
    data: {
      labels: ["Pendientes", "En proceso", "Resueltos"],
      datasets: [
        {
          data: [data.pendientes, data.proceso, data.resueltos],
          backgroundColor: ["#e74c3c", "#f39c12", "#27ae60"],
        },
      ],
    },
  });

  // 📊 Gráfico por usuario asignado
  renderGraficoPorUsuario(data.usuarios);

  // Gráfico por mes
  new Chart(document.getElementById("graficoPorMes"), {
    type: "line",
    data: {
      labels: data.meses.map((m) => m.nombre),
      datasets: [
        {
          label: "Tickets por mes",
          data: data.meses.map((m) => m.cantidad),
          fill: true,
          borderColor: "#1d4ed8",
          backgroundColor: "#93c5fd",
        },
      ],
    },
  });

  // Gráfico por área usando abreviaturas
  renderGraficoPorArea(data.areas);
}

// 🔁 Reemplazada: ahora muestra tickets por usuario
function renderGraficoPorUsuario(usuarios) {
  const ctx = document.getElementById("graficoPorSemana").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: usuarios.map((u) => u.nombre_usuario),
      datasets: [
        {
          label: "Tickets por usuario",
          data: usuarios.map((u) => u.cantidad),
          backgroundColor: "#3b82f6",
        },
      ],
    },
    options: {
      responsive: true,
      indexAxis: "y", // puedes cambiar a 'x' si prefieres vertical
      scales: {
        x: {
          beginAtZero: true,
        },
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              const usuario = usuarios[context.dataIndex];
              return `${usuario.nombre_usuario}: ${usuario.cantidad} tickets`;
            },
          },
        },
      },
    },
  });
}

// Gráfico por área (sin cambios)
function renderGraficoPorArea(areas) {
  const ctx = document.getElementById("graficoPorArea").getContext("2d");

  new Chart(ctx, {
    type: "pie",
    data: {
      labels: areas.map((a) => a.abreviatura),
      datasets: [
        {
          label: "Tickets por área",
          data: areas.map((a) => a.cantidad),
          backgroundColor: [
            "#f87171",
            "#60a5fa",
            "#34d399",
            "#fbbf24",
            "#a78bfa",
            "#f43f5e",
            "#3b82f6",
            "#10b981",
            "#f59e0b",
            "#8b5cf6",
          ],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              const area = areas[context.dataIndex];
              return `${area.abreviatura}: ${area.cantidad} tickets`;
            },
          },
        },
        legend: {
          position: "right",
        },
      },
    },
  });
}

window.addEventListener("DOMContentLoaded", cargarDashboard);
