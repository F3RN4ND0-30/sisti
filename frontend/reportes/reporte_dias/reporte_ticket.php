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
    <!-- ... cabecera original ... -->

    <!-- CSS -->
    <link rel="stylesheet" href="../../../backend/css/reportes/reporte_dias/reporte_tickets.css" />
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css" />

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- JS -->
    <script src="../../../backend/js/tickets/reporte_ticket.js"></script>
</head>

<body>

    <?php include '../../navbar/navbar.php'; ?>

    <div class="main-content">
        <h2>Reporte de Tickets</h2>

        <div class="filter-section">
            <label for="filtroPeriodo">Filtrar por:</label>
            <select id="filtroPeriodo">
                <option value="dia">Día</option>
                <option value="semana">Semana</option>
                <option value="mes" selected>Mes</option>
            </select>

            <!-- Día -->
            <div id="selectorDia" class="filtro-opcional" style="display: none;">
                <label for="diaSemana">Selecciona día:</label>
                <select id="diaSemana">
                    <option value="lunes">Lunes</option>
                    <option value="martes">Martes</option>
                    <option value="miercoles">Miércoles</option>
                    <option value="jueves">Jueves</option>
                    <option value="viernes">Viernes</option>
                    <option value="sabado">Sábado</option>
                    <option value="domingo">Domingo</option>
                </select>
            </div>

            <!-- Semana -->
            <div id="selectorSemana" class="filtro-opcional" style="display: none;">
                <label for="mesSemana">Selecciona mes:</label>
                <input type="month" id="mesSemana" value="<?php echo date('Y-m'); ?>" />
                <label for="semanaDelMes">Semana del mes:</label>
                <select id="semanaDelMes">
                    <option value="1">Semana 1</option>
                    <option value="2">Semana 2</option>
                    <option value="3">Semana 3</option>
                    <option value="4">Semana 4</option>
                    <option value="5">Semana 5</option>
                </select>
            </div>

            <!-- Mes -->
            <div id="selectorMes" class="filtro-opcional">
                <label for="mesFiltro">Selecciona mes:</label>
                <input type="month" id="mesFiltro" value="<?php echo date('Y-m'); ?>" />
            </div>
        </div>

        <div class="indicador-tickets">
            <p id="ticketsAtendidos">Tickets atendidos: <strong>0</strong></p>
        </div>

        <div class="table-responsive">
            <table id="tablaTickets" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th># Ticket</th>
                        <th>Solicitante</th>
                        <th>DNI</th>
                        <th>Área</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha Atención</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Contenido dinámico -->
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>