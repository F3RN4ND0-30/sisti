<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../../../login.php');
    exit();
}

require_once '../../../backend/bd/conexion.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Todos los Tickets | HelpDesk </title>

    <!-- CSS -->
    <link rel="stylesheet" href="/sisti/backend/css/vistas/gestickets/todos-tickets.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="/sisti/backend/img/logoPisco.png" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Selectize CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/selectize@0.15.2/dist/css/selectize.bootstrap5.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-12">
                    <h2><i class="material-icons" style="vertical-align: middle;">view_list</i> Todos los Tickets</h2>
                    <p class="mb-0">Gestión completa de tickets e incidencias del sistema</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="stats-grid">
            <?php
            try {
                // 🔥 CORRECCIÓN: Usar 'En proceso' con minúscula como está en la BD
                $stmt = $conexion->prepare("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN ei.Nombre = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN ei.Nombre = 'En proceso' THEN 1 ELSE 0 END) as proceso,
                        SUM(CASE WHEN ei.Nombre = 'Resuelto' THEN 1 ELSE 0 END) as resueltos
                    FROM tb_Incidentes i
                    INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                ");
                $stmt->execute();
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $stats = ['total' => 0, 'pendientes' => 0, 'proceso' => 0, 'resueltos' => 0];
            }
            ?>

            <div class="stat-card">
                <div class="stat-number" id="total-general"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>

            <div class="stat-card">
                <div class="stat-number" id="pendientes-general"><?php echo $stats['pendientes']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>

            <div class="stat-card">
                <div class="stat-number" id="proceso-general"><?php echo $stats['proceso']; ?></div>
                <div class="stat-label">En Proceso</div>
            </div>

            <div class="stat-card">
                <div class="stat-number" id="resueltos-general"><?php echo $stats['resueltos']; ?></div>
                <div class="stat-label">Resueltos</div>
            </div>
        </div>

        <!-- Filtros Mejorados y Simplificados -->
        <div class="filters-container">
            <!-- Header de filtros -->
            <div class="filters-header">
                <h4>
                    <i class="material-icons">filter_list</i>
                    Filtros de Búsqueda
                </h4>
                <button id="filtersToggle" class="filters-toggle">
                    <i class="material-icons">expand_more</i>
                    Más Filtros
                </button>
            </div>

            <!-- Filtros básicos (siempre visibles) -->
            <div class="filters-row">
                <div class="filter-group">
                    <label for="filtroEstado">
                        <i class="material-icons">flag</i>
                        Estado
                    </label>
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <?php
                        try {
                            $stmt = $conexion->prepare("SELECT Id_Estados_Incidente, Nombre FROM tb_Estados_Incidente ORDER BY Id_Estados_Incidente");
                            $stmt->execute();
                            $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($estados as $estado) {
                                echo "<option value='" . $estado['Id_Estados_Incidente'] . "'>" . htmlspecialchars($estado['Nombre']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option value=''>Error al cargar estados</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filtroArea">
                        <i class="material-icons">business</i>
                        Área
                    </label>
                    <select id="filtroArea" class="form-control selectize-control">
                        <option value="">Todas las áreas</option>
                        <?php
                        try {
                            $stmt = $conexion->prepare("SELECT Id_Areas, Nombre FROM tb_Areas WHERE Estado = 1 ORDER BY Nombre");
                            $stmt->execute();
                            $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($areas as $area) {
                                echo "<option value='" . htmlspecialchars($area['Nombre']) . "'>" . htmlspecialchars($area['Nombre']) . "</option>";
                            }
                        } catch (PDOException $e) {
                            echo "<option value=''>Error al cargar áreas</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filtroBusqueda">
                        <i class="material-icons">search</i>
                        Búsqueda General
                    </label>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Buscar en todos los campos...">
                </div>

                <div class="filter-group">
                    <button id="limpiarFiltros" class="btn btn-secondary hover-lift">
                        <i class="material-icons">clear</i>
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- Filtros avanzados (colapsables) -->
            <div id="filtersContent" class="filters-content collapsed">
                <!-- Filtros de fecha simplificados -->
                <div class="filter-group">
                    <label>
                        <i class="material-icons">date_range</i>
                        Filtros de Fecha
                    </label>
                    <div class="date-filter-group">
                        <button type="button" class="date-quick-btn active" data-filter="todos">Todos</button>
                        <button type="button" class="date-quick-btn" data-filter="hoy">Hoy</button>
                        <button type="button" class="date-quick-btn" data-filter="ayer">Ayer</button>
                        <button type="button" class="date-quick-btn" data-filter="esta-semana">Esta Semana</button>
                        <button type="button" class="date-quick-btn" data-filter="semana-pasada">Semana Pasada</button>
                        <button type="button" class="date-quick-btn" data-filter="este-mes">Este Mes</button>
                    </div>
                </div>

                <!-- Rango de fechas personalizado -->
                <div class="filters-row" style="margin-top: 20px;">
                    <div class="filter-group">
                        <label for="filtroFechaDesde">
                            <i class="material-icons">event</i>
                            Fecha Desde
                        </label>
                        <input type="date" id="filtroFechaDesde" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label for="filtroFechaHasta">
                            <i class="material-icons">event</i>
                            Fecha Hasta
                        </label>
                        <input type="date" id="filtroFechaHasta" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Filtros activos -->
            <div id="activeFilters" class="active-filters" style="display: none;">
                <h5>Filtros Aplicados:</h5>
                <div id="filterTags" class="filter-tags"></div>
            </div>

            <!-- Estadísticas de filtros -->
            <div id="filter-stats" style="margin-top: 15px; text-align: center;"></div>
        </div>

        <!-- Tabla de tickets mejorada -->
        <div class="table-container">
            <div class="tickets-header">
                <h3>
                    <i class="material-icons">confirmation_number</i>
                    Lista de Tickets
                </h3>
                <a href="/sisti/frontend/tickets/gestickets/crear-ticket.php" class="btn btn-primary hover-lift">
                    <i class="material-icons">add</i>
                    Nuevo Ticket
                </a>
            </div>

            <!-- Loading overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>

            <div class="table-responsive">
                <table id="ticketsTable" class="table tickets-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Usuario</th>
                            <th>Área</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $conexion->prepare("
                                SELECT 
                                    t.Codigo_Ticket,
                                    COALESCE(
                                        ue.Nombre + ' ' + ue.Apellido_Paterno + ' ' + ISNULL(ue.Apellido_Materno, ''), 
                                        u.Nombre + ' ' + u.Apellido_Paterno + ' ' + ISNULL(u.Apellido_Materno, ''), 
                                        'Usuario no encontrado'
                                    ) as NombreCompleto,
                                    a.Nombre as AreaNombre,
                                    i.Descripcion,
                                    ei.Nombre as EstadoNombre,
                                    ei.Id_Estados_Incidente as EstadoId,
                                    i.Fecha_Creacion,
                                    i.Id_Incidentes
                                FROM tb_Incidentes i
                                INNER JOIN tb_Tickets t ON i.Id_Tickets = t.Id_Tickets
                                LEFT JOIN tb_UsuariosExternos ue ON i.Id_UsuariosExternos = ue.Id_UsuariosExternos
                                LEFT JOIN tb_Usuarios u ON i.Id_Usuarios = u.Id_Usuarios
                                INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
                                INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                                ORDER BY i.Fecha_Creacion DESC
                            ");
                            $stmt->execute();
                            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $rowIndex = 1;
                            foreach ($tickets as $ticket) {
                                $estadoNombre = trim($ticket['EstadoNombre']);
                                $estadoClass = '';

                                switch ($estadoNombre) {
                                    case 'Pendiente':
                                        $estadoClass = 'badge-pendiente';
                                        break;
                                    case 'En proceso':
                                        $estadoClass = 'badge-proceso';
                                        break;
                                    case 'Resuelto':
                                        $estadoClass = 'badge-resuelto';
                                        break;
                                    default:
                                        $estadoClass = 'badge-pendiente';
                                }

                                echo "<tr style='--row-index: $rowIndex' data-estado-id='" . $ticket['EstadoId'] . "' data-area='" . htmlspecialchars($ticket['AreaNombre']) . "'>";

                                // Código de ticket
                                echo "<td>
                                        <span class='ticket-code'>" . htmlspecialchars($ticket['Codigo_Ticket']) . "</span>
                                      </td>";

                                // Usuario
                                echo "<td>
                                        <div class='user-info'>
                                            <span class='user-name'>" . htmlspecialchars($ticket['NombreCompleto']) . "</span>
                                        </div>
                                      </td>";

                                // Área
                                echo "<td>
                                        <span class='user-area'>" . htmlspecialchars($ticket['AreaNombre']) . "</span>
                                      </td>";

                                $descripcion = htmlspecialchars($ticket['Descripcion']);
                                $descripcionCorta = strlen($descripcion) > 50 ? substr($descripcion, 0, 50) . '...' : $descripcion;

                                echo "<td class='description-cell'>
        <span class='desc-text' onclick='toggleDesc(this)' 
              data-full='$descripcion' 
              data-short='$descripcionCorta'>$descripcionCorta</span>
      </td>";

                                // Estado con select
                                echo "<td>
                                        <select class='estado-select $estadoClass' 
                                                data-id='" . $ticket['Id_Incidentes'] . "' 
                                                onchange='cambiarEstadoDirecto(this)'
                                                data-original='" . htmlspecialchars($estadoNombre) . "'>
                                            <option value='Pendiente'" . ($estadoNombre == 'Pendiente' ? ' selected' : '') . ">Pendiente</option>
                                            <option value='En proceso'" . ($estadoNombre == 'En proceso' ? ' selected' : '') . ">En proceso</option>
                                            <option value='Resuelto'" . ($estadoNombre == 'Resuelto' ? ' selected' : '') . ">Resuelto</option>
                                        </select>
                                      </td>";

                                // Fecha
                                $fechaFormateada = date('d/m/Y H:i', strtotime($ticket['Fecha_Creacion']));
                                echo "<td>
                                        <span class='date-cell'>" . $fechaFormateada . "</span>
                                      </td>";

                                echo "</tr>";
                                $rowIndex++;
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' class='text-center' style='padding: 40px;'>
                                    <i class='material-icons' style='font-size: 48px; color: var(--text-muted); margin-bottom: 10px;'>error_outline</i>
                                    <br>
                                    <strong>Error al cargar tickets</strong>
                                    <br>
                                    <small class='text-muted'>" . $e->getMessage() . "</small>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- Selectize JS -->
    <script src="https://cdn.jsdelivr.net/npm/selectize@0.15.2/dist/js/selectize.min.js"></script>

    <!-- Sistema de filtros avanzados -->
    <script src="/sisti/backend/js/tickets/todos-tickets.js"></script>

</body>

</html>

<?php
// AJAX para actualizar estadísticas generales inline
if (isset($_POST['ajax']) && $_POST['ajax'] === 'estadisticas_generales') {
    try {
        // 🔥 CORRECCIÓN: Usar 'En proceso' con minúscula como está en la BD
        $stmt = $conexion->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ei.Nombre = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN ei.Nombre = 'En proceso' THEN 1 ELSE 0 END) as proceso,
                SUM(CASE WHEN ei.Nombre = 'Resuelto' THEN 1 ELSE 0 END) as resueltos
            FROM tb_Incidentes i
            INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($stats);
        exit();
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}
?>