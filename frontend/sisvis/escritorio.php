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
    <link rel="stylesheet" href="../../backend/css/navbar/navbar.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
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

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card blue">
                    <i class="material-icons" style="font-size: 40px; color: #3498db;">today</i>
                    <div class="stat-number">0</div>
                    <h5>Total Incidentes Hoy</h5>
                    <p class="text-muted">Tickets registrados hoy</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card red">
                    <i class="material-icons" style="font-size: 40px; color: #e74c3c;">warning</i>
                    <div class="stat-number">0</div>
                    <h5>Incidentes Pendientes</h5>
                    <p class="text-muted">Requieren atención</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card orange">
                    <i class="material-icons" style="font-size: 40px; color: #f39c12;">settings</i>
                    <div class="stat-number">0</div>
                    <h5>Incidentes en Proceso</h5>
                    <p class="text-muted">Siendo atendidos</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card green">
                    <i class="material-icons" style="font-size: 40px; color: #27ae60;">check_circle</i>
                    <div class="stat-number">0</div>
                    <h5>Incidentes Resueltos</h5>
                    <p class="text-muted">Completados exitosamente</p>
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

        <!-- Módulos del Sistema -->
        <div class="module-grid">
            <div class="module-card tickets" onclick="alert('Módulo en desarrollo')">
                <div class="module-icon">
                    <i class="material-icons">confirmation_number</i>
                </div>
                <h5>Gestión de Tickets</h5>
                <p>Crear, administrar y dar seguimiento a los tickets de soporte e incidencias reportadas.</p>
            </div>

            <div class="module-card reports" onclick="alert('Módulo en desarrollo')">
                <div class="module-icon">
                    <i class="material-icons">assessment</i>
                </div>
                <h5>Reportes y Estadísticas</h5>
                <p>Generar reportes detallados y visualizar estadísticas de atención y resolución.</p>
            </div>

            <?php if ($_SESSION['hd_rol'] === 'administrador'): ?>
                <div class="module-card users" onclick="alert('Módulo en desarrollo')">
                    <div class="module-icon">
                        <i class="material-icons">people</i>
                    </div>
                    <h5>Gestión de Usuarios</h5>
                    <p>Administrar técnicos, roles y permisos del sistema de soporte.</p>
                </div>
            <?php endif; ?>

            <div class="module-card config" onclick="alert('Módulo en desarrollo')">
                <div class="module-icon">
                    <i class="material-icons">settings</i>
                </div>
                <h5>Configuración</h5>
                <p>Personalizar perfil, preferencias y configuraciones del sistema.</p>
            </div>
        </div>

    </div>

</body>

</html>