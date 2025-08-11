<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Fichas | HelpDesk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos base -->
    <link rel="stylesheet" href="../../../backend/css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../../backend/css/reportes/lista_fichas/listado_fichas.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />

    <!-- jQuery y DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Responsive extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Responsive extension JS -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Script JS -->
    <script src="../../../backend/js/reportes/fichas.js" defer></script>
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>

    <div class="main-content">
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>Listado de Fichas Generadas</h2>
                    <p class="mb-0">Aquí puedes visualizar todas las fichas emitidas desde el sistema.</p>
                    <?php date_default_timezone_set('America/Lima'); ?>
                    <small>Actualizado al: <?php echo date('d/m/Y H:i'); ?></small>
                </div>
            </div>
        </div>

        <div class="activity-card">
            <div class="titulo-fichas">
                <h4 class="titulo"><i class="material-icons">assignment</i> Fichas Técnicas</h4>
                <a href="/sisti/archivos/fichas" class="btn-descargar">Descargar Fichas</a>
            </div>
            <div class="table-responsive">
                <table class="table" id="tabla-fichas">
                    <thead>
                        <tr>
                            <th>ID Ficha</th>
                            <th>Número</th>
                            <th>Nombre del Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se carga por JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>