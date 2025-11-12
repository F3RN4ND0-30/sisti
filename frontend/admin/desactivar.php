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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Tickets - HelpDesk</title>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <!-- Iconos y CSS propio -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/sisti/backend/css/admin/desactivar.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="material-icons me-2">public</i> Gestión de Ticket</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaDesactivar" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>TICKET</th>
                                        <th>ASUNTO</th>
                                        <th>FECHA CREACIÓN</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody><!-- Cargado por JS --></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/sisti/backend/js/admin/desactivar.js"></script>
</body>

</html>