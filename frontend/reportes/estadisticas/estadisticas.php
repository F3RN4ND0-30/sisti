<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../login.php');
    exit();
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Estadísticas | HelpDesk</title>

    <!-- 🔥 IMPORTANTE: Navbar CSS primero -->
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css" />

    <!-- CSS del módulo DESPUÉS del navbar -->
    <link rel="stylesheet" href="../../../backend/css/reportes/estadisticas/estadisticas.css" />

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JS -->
    <script src="../../../backend/js/reportes/dashboard.js"></script>
</head>

<body>
    <!-- 🔥 INCLUIR NAVBAR (que ya tiene el wrapper) -->
    <?php include '../../navbar/navbar.php'; ?>

    <!-- 🔥 CONTENIDO DENTRO DEL LAYOUT DEL NAVBAR -->
    <div class="main-content">
        <div class="dashboard">
            <h2 class="titulo-dashboard">Panel de Tickets de este Mes</h2>

            <div class="kpis">
                <div class="kpi-card">Total: <strong id="totalTickets">0</strong></div>
                <div class="kpi-card pendiente">Pendientes: <strong id="ticketsPendientes">0</strong></div>
                <div class="kpi-card proceso">En proceso: <strong id="ticketsProceso">0</strong></div>
                <div class="kpi-card resuelto">Resueltos: <strong id="ticketsResueltos">0</strong></div>
            </div>

            <div class="graficos">
                <div class="grafico-container">
                    <h3>Tickets por Estado</h3>
                    <canvas id="graficoEstados"></canvas>
                </div>
                <div class="grafico-container">
                    <h3>Tickets por Área</h3>
                    <canvas id="graficoPorArea"></canvas>
                </div>
                <div class="grafico-container">
                    <h3>Tickets por Semana</h3>
                    <canvas id="graficoPorSemana"></canvas>
                </div>
                <div class="grafico-container">
                    <h3>Tickets por Mes</h3>
                    <canvas id="graficoPorMes"></canvas>
                </div>
            </div>
        </div>
    </div>
</body>

</html>