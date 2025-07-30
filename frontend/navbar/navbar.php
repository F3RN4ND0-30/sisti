<?php
// Verificación de sesión HelpDesk
if (isset($_SESSION['hd_rol'])) {
    $rol = $_SESSION['hd_rol'];
} else {
    $rol = null;
}

// Función para detectar página activa
function is_active_hd($path_fragment)
{
    $current_path = $_SERVER['REQUEST_URI'];
    return strpos($current_path, $path_fragment) !== false;
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div class="wrapper">
    <div class="body-overlay"></div>

    <!-- Sidebar HelpDesk -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>
                <img src="../../backend/img/logoPisco.png" class="img-fluid" />
                <span class="sidebar-text">HELPDESK</span>
            </h3>
        </div>

        <ul class="list-unstyled components">
            <!-- Dashboard -->
            <li <?php echo is_active_hd('/sisvis/escritorio.php') ? 'class="active"' : ''; ?>>
                <a href="../sisvis/escritorio.php" class="dashboard">
                    <i class="material-icons">dashboard</i>
                    <span>Inicio</span>
                </a>
            </li>

            <!-- Gestión de Tickets -->
            <li class="dropdown <?php echo is_active_hd('/tickets/gestickets/') ? 'active' : ''; ?>">
                <a href="#ticketsSubmenu" class="dropdown-toggle" aria-expanded="false">
                    <i class="material-icons">confirmation_number</i>
                    <span>Gestión de Tickets</span>
                </a>
                <ul class="collapse list-unstyled menu" id="ticketsSubmenu">
                    <li <?php echo is_active_hd('/tickets/gestickets/crear-ticket.php') ? 'class="active"' : ''; ?>>
                        <a href="../tickets/gestickets/crear-ticket.php">Crear Ticket</a>
                    </li>
                    <li <?php echo is_active_hd('/tickets/gestickets/mis-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="../tickets/gestickets/mis-tickets.php">Mis Tickets</a>
                    </li>
                    <li <?php echo is_active_hd('/tickets/gestickets/todos-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="../tickets/gestickets/todos-tickets.php">Todos los Tickets</a>
                    </li>
                    <li <?php echo is_active_hd('/tickets/gestickets/seguimiento-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="../tickets/gestickets/seguimiento-tickets.php">Seguimiento</a>
                    </li>
                </ul>
            </li>

            <!-- Reportes -->
            <li class="dropdown <?php echo is_active_hd('/reportes/') ? 'active' : ''; ?>">
                <a href="#reportesSubmenu" class="dropdown-toggle" aria-expanded="false">
                    <i class="material-icons">assessment</i>
                    <span>Reportes</span>
                </a>
                <ul class="collapse list-unstyled menu" id="reportesSubmenu">
                    <li <?php echo is_active_hd('/reportes/general.php') ? 'class="active"' : ''; ?>>
                        <a href="../reportes/general.php">Reporte General</a>
                    </li>
                    <li <?php echo is_active_hd('/reportes/atencion.php') ? 'class="active"' : ''; ?>>
                        <a href="../reportes/atencion.php">Reporte de Atención</a>
                    </li>
                    <li <?php echo is_active_hd('/reportes/estadisticas.php') ? 'class="active"' : ''; ?>>
                        <a href="../reportes/estadisticas.php">Estadísticas</a>
                    </li>
                </ul>
            </li>

            <?php if ($rol === 'administrador'): ?>
                <!-- Administración (solo administradores) -->
                <li class="dropdown <?php echo is_active_hd('/admin/') ? 'active' : ''; ?>">
                    <a href="#adminSubmenu" class="dropdown-toggle" aria-expanded="false">
                        <i class="material-icons">admin_panel_settings</i>
                        <span>Administración</span>
                    </a>
                    <ul class="collapse list-unstyled menu" id="adminSubmenu">
                        <li <?php echo is_active_hd('/admin/usuarios.php') ? 'class="active"' : ''; ?>>
                            <a href="../admin/usuarios.php">Gestión de Usuarios</a>
                        </li>
                        <li <?php echo is_active_hd('/admin/equipos.php') ? 'class="active"' : ''; ?>>
                            <a href="../admin/equipos.php">Registro de Equipos</a>
                        </li>
                        <li <?php echo is_active_hd('/admin/categorias.php') ? 'class="active"' : ''; ?>>
                            <a href="../admin/categorias.php">Categorías</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Configuración -->
            <li class="dropdown <?php echo is_active_hd('/configuracion/') ? 'active' : ''; ?>">
                <a href="#configSubmenu" class="dropdown-toggle" aria-expanded="false">
                    <i class="material-icons">settings</i>
                    <span>Configuración</span>
                </a>
                <ul class="collapse list-unstyled menu" id="configSubmenu">
                    <li <?php echo is_active_hd('/configuracion/perfil.php') ? 'class="active"' : ''; ?>>
                        <a href="../configuracion/perfil.php">Mi Perfil</a>
                    </li>
                    <?php if ($rol === 'administrador'): ?>
                        <li <?php echo is_active_hd('/configuracion/sistema.php') ? 'class="active"' : ''; ?>>
                            <a href="../configuracion/sistema.php">Configuración del Sistema</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>

            <!-- Cerrar Sesión (móvil) -->
            <div class="small-screen navbar-display">
                <li class="dropdown d-lg-none d-md-block d-xl-none d-sm-block">
                    <a href="#logoutSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="material-icons">power_settings_new</i>Cerrar Sesión</a>
                    <ul class="collapse list-unstyled menu" id="logoutSubmenu">
                        <li>
                            <a href="../logout.php" style="color: #e74c3c;">Cerrar Sesión</a>
                        </li>
                    </ul>
                </li>
            </div>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <div class="top-navbar">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <!-- Botón para colapsar sidebar -->
                    <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-block">
                        <i class="material-icons">menu</i>
                    </button>

                    <a class="navbar-brand" href="../sisvis/escritorio.php">
                        <i class="material-icons">support_agent</i>
                        HelpDesk - Panel Principal
                    </a>

                    <button class="navbar-toggler d-lg-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                        <i class="material-icons">more_vert</i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-icons">person</i>
                                    <span class="user-info">
                                        <span class="user-name"><?php echo $_SESSION['hd_nombres']; ?></span>
                                        <small class="user-role"><?php echo ucfirst($_SESSION['hd_rol']); ?></small>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-header">
                                        <div class="user-details">
                                            <strong><?php echo $_SESSION['hd_nombres']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $_SESSION['hd_correo']; ?></small>
                                            <br>
                                            <span class="badge bg-primary"><?php echo ucfirst($_SESSION['hd_rol']); ?></span>
                                        </div>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../configuracion/perfil.php">
                                            <i class="material-icons">account_circle</i>
                                            Mi Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../configuracion/perfil.php">
                                            <i class="material-icons">settings</i>
                                            Configuración
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="../../logout.php">
                                            <i class="material-icons">power_settings_new</i>
                                            Cerrar Sesión
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Aquí va el contenido de cada página -->

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script para sidebar toggle -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const content = document.getElementById('content');
                const sidebarCollapse = document.getElementById('sidebarCollapse');
                const bodyOverlay = document.querySelector('.body-overlay');

                // Toggle sidebar
                sidebarCollapse.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');

                    // Solo activar overlay en móvil
                    if (window.innerWidth <= 768) {
                        bodyOverlay.classList.toggle('active');
                    }

                    // Cerrar todos los dropdowns cuando se colapsa
                    if (sidebar.classList.contains('active')) {
                        const openDropdowns = document.querySelectorAll('#sidebar .collapse.show');
                        openDropdowns.forEach(dropdown => {
                            dropdown.classList.remove('show');
                        });
                        const openToggles = document.querySelectorAll('#sidebar .dropdown-toggle[aria-expanded="true"]');
                        openToggles.forEach(toggle => {
                            toggle.setAttribute('aria-expanded', 'false');
                        });
                    }
                });

                // Close sidebar on overlay click (mobile)
                bodyOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    content.classList.remove('active');
                    bodyOverlay.classList.remove('active');
                });

                // Auto-close sidebar on mobile when clicking nav links
                const navLinks = document.querySelectorAll('#sidebar .components a:not(.dropdown-toggle)');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('active');
                            content.classList.remove('active');
                            bodyOverlay.classList.remove('active');
                        }
                    });
                });

                // Handle dropdown toggles manually for collapsed sidebar
                const dropdownToggles = document.querySelectorAll('#sidebar .dropdown-toggle');
                dropdownToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Si el sidebar está collapsed en desktop, no permitir dropdown
                        if (sidebar.classList.contains('active') && window.innerWidth > 768) {
                            return false;
                        }

                        // Comportamiento normal para sidebar expandido
                        const targetId = this.getAttribute('href').substring(1);
                        const targetCollapse = document.getElementById(targetId);

                        if (targetCollapse) {
                            // Toggle collapse
                            const isOpen = targetCollapse.classList.contains('show');

                            // Cerrar otros dropdowns primero
                            dropdownToggles.forEach(otherToggle => {
                                if (otherToggle !== this) {
                                    const otherId = otherToggle.getAttribute('href').substring(1);
                                    const otherCollapse = document.getElementById(otherId);
                                    if (otherCollapse) {
                                        otherCollapse.classList.remove('show');
                                        otherToggle.setAttribute('aria-expanded', 'false');
                                    }
                                }
                            });

                            // Toggle el actual
                            if (isOpen) {
                                targetCollapse.classList.remove('show');
                                this.setAttribute('aria-expanded', 'false');
                            } else {
                                targetCollapse.classList.add('show');
                                this.setAttribute('aria-expanded', 'true');
                            }
                        }
                    });
                });
            });
        </script>