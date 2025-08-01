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
    <title>HelpDesk | MPP</title>

    <!-- CSS Básico -->
    <link rel="stylesheet" href="../../backend/css/vistas/escritorio.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="../../backend/js/desk/actualizarEstado.js" defer></script>
</head>

<body>

    <?php include '../navbar/navbar.php'; ?>

    <div class="main-content">

        <!-- Header Dashboard -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>¡Bienvenido, <?php echo $_SESSION['hd_nombres']; ?>!</h2>
                    <p class="mb-0">Sistema de HelpDesk - Gestión de Incidencias y Soporte Técnico</p>
                    <?php
                    date_default_timezone_set('America/Lima');
                    ?>
                    <small>Municipalidad Provincial de Pisco | <?php echo date('d/m/Y H:i'); ?></small>
                </div>
                <div class="col-md-4 text-right">
                    <div class="user-role">
                        <h5><?php echo ucfirst($_SESSION['hd_rol']); ?></h5>
                        <p><?php echo $_SESSION['hd_correo']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="row">
            <div class="col-lg-12">
                <div class="activity-card">
                    <h4><i class="material-icons">support_agent</i> Sistema de HelpDesk</h4>
                    <p>El Sistema de HelpDesk de la Municipalidad Provincial de Pisco permite la gestión integral de incidencias y soporte técnico para optimizar la atención ciudadana y mejorar los servicios municipales. Proporciona herramientas para el registro, seguimiento y resolución de tickets de soporte de manera eficiente y organizada.</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card blue">
                    <i class="material-icons" style="font-size: 40px; color: #3498db;">today</i>
                    <div class="stat-number" id="total-hoy">0</div>
                    <h5>Total Incidentes Hoy</h5>
                    <p class="text-muted">Tickets registrados hoy</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card red">
                    <i class="material-icons" style="font-size: 40px; color: #e74c3c;">warning</i>
                    <div class="stat-number" id="pendientes">0</div>
                    <h5>Incidentes Pendientes</h5>
                    <p class="text-muted">Requieren atención</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card orange">
                    <i class="material-icons" style="font-size: 40px; color: #f39c12;">settings</i>
                    <div class="stat-number" id="proceso">0</div>
                    <h5>Incidentes en Proceso</h5>
                    <p class="text-muted">Siendo atendidos</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card green">
                    <i class="material-icons" style="font-size: 40px; color: #27ae60;">check_circle</i>
                    <div class="stat-number" id="resueltos">0</div>
                    <h5>Incidentes Resueltos</h5>
                    <p class="text-muted">Completados exitosamente</p>
                </div>
            </div>
        </div>

        <!-- Módulos del Sistema -->
        <?php include '../../backend/php/desk/tabla_incidentes.php'; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true,
                order: [
                    [1, 'desc']
                ]
            });
        });
    </script>

</body>

</html>