<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('Location: ../login.php');
    exit();
}
require_once '../../backend/bd/conexion.php';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SISTI | MPP</title>

    <!-- CSS Básico -->
    <link rel="stylesheet" href="../../backend/css/vistas/escritorio.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />

    <!-- jQuery debe ir primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- DataTables Responsive CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Responsive JS -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="../../backend/js/desk/actualizarEstado.js" defer></script>
</head>

<body>

    <?php include '../navbar/navbar.php'; ?>

    <div class="main-content">

        <!-- Header Dashboard -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>¡Bienvenido, <?php echo $_SESSION['hd_nombre']; ?>!</h2>
                    <p class="mb-0">Sistema de SISTI - Gestión de Incidencias y Soporte Técnico</p>
                    <?php
                    date_default_timezone_set('America/Lima');
                    ?>
                    <small>Municipalidad Provincial de Pisco | <?php echo date('d/m/Y H:i'); ?></small>
                </div>
                <div class="col-md-4 text-right">
                    <div class="user-role">
                        <h5><?php echo ucfirst($_SESSION['hd_rol']); ?></h5>
                        <h5><?php echo $_SESSION['hd_usuario']; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="row">
            <div class="col-lg-12">
                <div class="activity-card">
                    <h4><i class="material-icons">support_agent</i> Sistema de SISTI</h4>
                    <p>El Sistema de SISTI de la Municipalidad Provincial de Pisco permite la gestión integral de incidencias y soporte técnico para optimizar la atención ciudadana y mejorar los servicios municipales. Proporciona herramientas para el registro, seguimiento y resolución de tickets de soporte de manera eficiente y organizada.</p>
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
        <div class="incidentes">
            <!-- Módulos del Sistema -->
            <?php include '../../backend/php/desk/tabla_incidentes.php'; ?>
        </div>

    </div>
    <script>
        // Reajusta la tabla cuando se redimensiona el contenedor
        $(window).on('resize', function() {
            if ($.fn.dataTable.isDataTable('#tabla-incidente')) {
                const tabla = $('#tabla-incidente').DataTable();
                tabla.columns.adjust();
                if (tabla.responsive && typeof tabla.responsive.recalc === 'function') {
                    tabla.responsive.recalc();
                }
            }
        });

        $('#toggle-menu, .sidebar-toggle').on('click', function() {
            setTimeout(() => {
                if ($.fn.dataTable.isDataTable('#tabla-incidente')) {
                    const tabla = $('#tabla-incidente').DataTable();
                    tabla.columns.adjust();
                    if (tabla.responsive && typeof tabla.responsive.recalc === 'function') {
                        tabla.responsive.recalc();
                    }
                }
            }, 300);
        });
    </script>

    <script>
        $(document).ready(function() {
            const tabla = $('#tabla-incidente').DataTable({
                ajax: {
                    url: '../../backend/php/desk/listar_incidentes.php',
                    dataSrc: ''
                },
                columns: [{
                        data: 'Id_Incidentes'
                    },
                    {
                        data: 'Ticket'
                    },
                    {
                        data: 'Area'
                    },
                    {
                        data: 'Descripcion'
                    },
                    {
                        data: 'Estado',
                        render: function(data, type, row) {
                            const estado = data.toLowerCase().trim();
                            return `
                        <select class="estado-select ${estado}" onchange="actualizarEstado(this, ${row.Id_Incidentes})">
                            <option value="pendiente" ${estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                            <option value="proceso" ${estado === 'en proceso' ? 'selected' : ''}>En Proceso</option>
                            <option value="resuelto" ${estado === 'resuelto' ? 'selected' : ''}>Resuelto</option>
                        </select>`;
                        }
                    },
                    {
                        data: 'Fecha_Creacion'
                    },
                    {
                        data: 'Fecha_Resuelto'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                pagingType: "simple_numbers",
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ]
            });

            // Auto recargar cada 10 segundos sin perder la página actual
            setInterval(() => {
                tabla.ajax.reload(null, false);
            }, 10000);
        });
    </script>
    <script>
        $(document).ready(function() {
            const sonido = new Audio('../../backend/sounds/Beep-alert.mp3');
            let prevStats = {
                total_hoy: 0,
                pendientes: 0,
                proceso: 0,
                resueltos: 0
            };

            function actualizarEstadisticas() {
                $.ajax({
                    url: '../../backend/php/desk/obtener_estadisticas.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.error("Error:", data.error);
                            return;
                        }

                        // Comparar con los valores anteriores
                        if (prevStats.total_hoy !== data.total_hoy ||
                            prevStats.pendientes !== data.pendientes ||
                            prevStats.proceso !== data.proceso ||
                            prevStats.resueltos !== data.resueltos) {

                            sonido.play(); // Reproducir sonido solo si hay cambios
                        }

                        // Actualizar valores en el DOM
                        $('#total-hoy').text(data.total_hoy);
                        $('#pendientes').text(data.pendientes);
                        $('#proceso').text(data.proceso);
                        $('#resueltos').text(data.resueltos);

                        // Guardar los nuevos valores
                        prevStats = data;
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            }

            // Llamar inmediatamente y luego cada 10 segundos
            actualizarEstadisticas();
            setInterval(actualizarEstadisticas, 10000); // 10000 ms = 10 segundos
        });
    </script>


</body>

</html>