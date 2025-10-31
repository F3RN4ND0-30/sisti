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
    <title>Gestión de Extranjeros - HelpDesk</title>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <!-- Iconos y CSS propio -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/sisti/backend/css/admin/extranjeros.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="material-icons me-2">public</i> Gestión de Extranjeros</h4>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearExtranjeros">
                            <i class="material-icons me-1">person_add</i> Nuevo Registro
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaExtranjeros" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cédula</th>
                                        <th>Nombres</th>
                                        <th>Apellido Paterno</th>
                                        <th>Apellido Materno</th>
                                        <th>Estado</th>
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

    <!-- MODAL NUEVO EXTRANJERO -->
    <div class="modal fade" id="modalCrearExtranjeros" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="material-icons me-2">person_add</i> Nuevo Extranjero</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formExtranjeros">
                        <div class="mb-3">
                            <label>Cédula *</label>
                            <input type="text" class="form-control" name="cedula" maxlength="15" required>
                        </div>
                        <div class="mb-3">
                            <label>Nombres *</label>
                            <input type="text" class="form-control" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label>Apellido Paterno *</label>
                            <input type="text" class="form-control" name="ap_paterno" required>
                        </div>
                        <div class="mb-3">
                            <label>Apellido Materno *</label>
                            <input type="text" class="form-control" name="ap_materno" required>
                        </div>
                        <div class="mb-3">
                            <label>Estado *</label>
                            <select class="form-select" name="estado" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="material-icons me-1">save</i> Guardar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/sisti/backend/js/admin/extranjeros.js"></script>
</body>

</html>