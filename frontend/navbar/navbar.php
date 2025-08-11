<?php
// Verificación de sesión HelpDesk
if (isset($_SESSION['hd_rol'])) {
    $rol = $_SESSION['hd_rol'];
} else {
    $rol = null;
}

// Función para detectar página activa (traducida al español)
function esPaginaActiva($fragmento_ruta)
{
    $ruta_actual = $_SERVER['REQUEST_URI'];
    return strpos($ruta_actual, $fragmento_ruta) !== false;
}

// Función para detectar si un dropdown debe estar abierto (traducida al español)
function debeDropdownEstarAbierto($fragmentos_ruta)
{
    $ruta_actual = $_SERVER['REQUEST_URI'];
    foreach ($fragmentos_ruta as $fragmento) {
        if (strpos($ruta_actual, $fragmento) !== false) {
            return true;
        }
    }
    return false;
}

// Función para obtener el primer nombre del usuario
function obtenerPrimerNombre($nombre_completo)
{
    return explode(' ', $nombre_completo)[0];
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- CSS Responsive Mejorado -->
<link rel="stylesheet" href="/sisti/backend/css/navbar/navbar.css">
<link rel="icon" type="image/png" href="/sisti/backend/img/logoPisco.png" />

<div class="wrapper">
    <div class="body-overlay"></div>

    <!-- Sidebar HelpDesk -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>
                <img src="/sisti/backend/img/logoPisco.png" class="img-fluid" alt="Logo Pisco" />
                <span class="sidebar-text">HELPDESK</span>
            </h3>
        </div>

        <ul class="list-unstyled components">
            <!-- Dashboard -->
            <li <?php echo esPaginaActiva('/sisvis/escritorio.php') ? 'class="active"' : ''; ?>>
                <a href="/sisti/frontend/sisvis/escritorio.php" class="dashboard">
                    <i class="material-icons">dashboard</i>
                    <span>Inicio</span>
                </a>
            </li>

            <!-- Gestión de Tickets -->
            <?php
            $rutas_tickets = ['/tickets/gestickets/'];
            $tickets_abierto = debeDropdownEstarAbierto($rutas_tickets);
            ?>
            <li class="dropdown <?php echo esPaginaActiva('/tickets/gestickets/') ? 'active' : ''; ?>">
                <a href="#ticketsSubmenu" class="dropdown-toggle" aria-expanded="<?php echo $tickets_abierto ? 'true' : 'false'; ?>">
                    <i class="material-icons">confirmation_number</i>
                    <span>Gestión de Tickets</span>
                </a>
                <ul class="collapse list-unstyled menu <?php echo $tickets_abierto ? 'show' : ''; ?>" id="ticketsSubmenu">
                    <li <?php echo esPaginaActiva('/tickets/gestickets/crear-ticket.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/tickets/gestickets/crear-ticket.php">Crear Ticket</a>
                    </li>
                    <li <?php echo esPaginaActiva('/tickets/gestickets/mis-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/tickets/gestickets/mis-tickets.php">Mis Tickets</a>
                    </li>
                    <li <?php echo esPaginaActiva('/tickets/gestickets/todos-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/tickets/gestickets/todos-tickets.php">Todos los Tickets</a>
                    </li>
                    <li <?php echo esPaginaActiva('/tickets/gestickets/seguimiento-tickets.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/tickets/gestickets/seguimiento-tickets.php">Seguimiento</a>
                    </li>
                </ul>
            </li>

            <!-- Reportes -->
            <?php
            $rutas_reportes = ['/reportes/'];
            $reportes_abierto = debeDropdownEstarAbierto($rutas_reportes);
            ?>
            <li class="dropdown <?php echo esPaginaActiva('/reportes/') ? 'active' : ''; ?>">
                <a href="#reportesSubmenu" class="dropdown-toggle" aria-expanded="<?php echo $reportes_abierto ? 'true' : 'false'; ?>">
                    <i class="material-icons">assessment</i>
                    <span>Reportes</span>
                </a>
                <ul class="collapse list-unstyled menu <?php echo $reportes_abierto ? 'show' : ''; ?>" id="reportesSubmenu">
                    <li <?php echo esPaginaActiva('/reportes/general.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/reportes/reporte_dias/reporte_ticket.php">Reporte General</a>
                    </li>
                    <li <?php echo esPaginaActiva('/reportes/atencion.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/reportes/reporte_atencion/fichas.php">Reporte de Atención</a>
                    </li>
                    <li <?php echo esPaginaActiva('/reportes/estadisticas.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/frontend/reportes/estadisticas/estadisticas.php">Estadísticas</a>
                    </li>
                    <?php if ($rol === 'administrador'): ?>
                        <li <?php echo esPaginaActiva('/reportes/fichas.php') ? 'class="active"' : ''; ?>>
                            <a href="/sisti/frontend/reportes/lista_fichas/listado_fichas.php">Lista de Fichas</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>

            <?php if ($rol === 'administrador'): ?>
                <!-- Administración (solo administradores) -->
                <?php
                $rutas_admin = ['/admin/'];
                $admin_abierto = debeDropdownEstarAbierto($rutas_admin);
                ?>
                <li class="dropdown <?php echo esPaginaActiva('/admin/') ? 'active' : ''; ?>">
                    <a href="#adminSubmenu" class="dropdown-toggle" aria-expanded="<?php echo $admin_abierto ? 'true' : 'false'; ?>">
                        <i class="material-icons">admin_panel_settings</i>
                        <span>Administración</span>
                    </a>
                    <ul class="collapse list-unstyled menu <?php echo $admin_abierto ? 'show' : ''; ?>" id="adminSubmenu">
                        <li <?php echo esPaginaActiva('/admin/usuarios.php') ? 'class="active"' : ''; ?>>
                            <a href="/sisti/frontend/admin/usuarios.php">Gestión de Usuarios</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Configuración -->
            <?php
            $rutas_config = ['/configuracion/', '/backend/php/configuracion/'];
            $config_abierto = debeDropdownEstarAbierto($rutas_config);
            ?>
            <li class="dropdown <?php echo esPaginaActiva('/configuracion/') || esPaginaActiva('/backend/php/configuracion/') ? 'active' : ''; ?>">
                <a href="#configSubmenu" class="dropdown-toggle" aria-expanded="<?php echo $config_abierto ? 'true' : 'false'; ?>">
                    <i class="material-icons">settings</i>
                    <span>Configuración</span>
                </a>
                <ul class="collapse list-unstyled menu <?php echo $config_abierto ? 'show' : ''; ?>" id="configSubmenu">
                    <li <?php echo esPaginaActiva('/configuracion/perfil.php') || esPaginaActiva('/backend/php/configuracion/perfil.php') ? 'class="active"' : ''; ?>>
                        <a href="/sisti/backend/php/configuracion/perfil.php">Mi Perfil</a>
                    </li>
                </ul>
            </li>
        </ul>

        <!-- Cerrar Sesión para móvil (dentro del sidebar) -->
        <div class="mobile-logout d-lg-none">
            <hr class="sidebar-divider">
            <a href="/sisti/frontend/logout.php" class="logout-mobile">
                <i class="material-icons">power_settings_new</i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <div class="top-navbar">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <!-- Botón para colapsar sidebar -->
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="material-icons">menu</i>
                        <span class="btn-text d-none d-md-inline">Menú</span>
                    </button>

                    <!-- BRAND RESPONSIVO -->
                    <a class="navbar-brand" href="/frontend/sisvis/escritorio.php">
                        <i class="material-icons brand-icon">support_agent</i>
                        <!-- Texto completo para desktop -->
                        <span class="brand-text-full d-none d-lg-inline">HelpDesk - Panel Principal</span>
                        <!-- Texto medio para tablet -->
                        <span class="brand-text-medium d-none d-md-inline d-lg-none">HelpDesk</span>
                        <!-- Texto corto para móvil -->
                        <span class="brand-text-short d-inline d-md-none">HD</span>
                    </a>

                    <!-- Usuario info y dropdown (siempre visible en desktop) -->
                    <div class="navbar-nav ms-auto d-none d-lg-flex">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-dropdown" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="material-icons user-icon">person</i>
                                <span class="user-info">
                                    <span class="user-name"><?php echo $_SESSION['hd_nombre']; ?></span>
                                    <small class="user-role"><?php echo ucfirst($_SESSION['hd_rol']); ?></small>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <div class="user-details">
                                        <strong><?php echo $_SESSION['hd_nombre']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $_SESSION['hd_usuario']; ?></small>
                                        <br>
                                        <span class="badge bg-primary"><?php echo ucfirst($_SESSION['hd_rol']); ?></span>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/sisti/backend/php/configuracion/perfil.php">
                                        <i class="material-icons">account_circle</i>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/sisti/frontend/logout.php">
                                        <i class="material-icons">power_settings_new</i>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Botón de usuario simple para móvil/tablet -->
                    <div class="d-lg-none">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary mobile-user-btn" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="material-icons">person</i>
                                <span class="d-none d-sm-inline"><?php echo obtenerPrimerNombre($_SESSION['hd_nombre']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <div class="user-details">
                                        <strong><?php echo $_SESSION['hd_nombre']; ?></strong>
                                        <br>
                                        <span class="badge bg-primary"><?php echo ucfirst($_SESSION['hd_rol']); ?></span>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/sisti/backend/php/configuracion/perfil.php">
                                        <i class="material-icons">account_circle</i>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/sisti/frontend/logout.php">
                                        <i class="material-icons">power_settings_new</i>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Aquí va el contenido de cada página -->

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- JAVASCRIPT MEJORADO -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const barraLateral = document.getElementById('sidebar');
                const contenido = document.getElementById('content');
                const colapsarBarraLateral = document.getElementById('sidebarCollapse');
                const superposicionCuerpo = document.querySelector('.body-overlay');

                // Alternar barra lateral
                colapsarBarraLateral.addEventListener('click', function() {
                    barraLateral.classList.toggle('active');
                    contenido.classList.toggle('active');

                    // Activar overlay solo en móvil
                    if (window.innerWidth <= 768) {
                        superposicionCuerpo.classList.toggle('active');
                        document.body.style.overflow = barraLateral.classList.contains('active') ? 'hidden' : 'auto';
                    }

                    // Cerrar dropdowns cuando se colapsa en desktop
                    if (barraLateral.classList.contains('active') && window.innerWidth > 768) {
                        const desplegablesAbiertos = document.querySelectorAll('#sidebar .collapse.show');
                        desplegablesAbiertos.forEach(desplegable => {
                            desplegable.classList.remove('show');
                        });
                        const alternadoresAbiertos = document.querySelectorAll('#sidebar .dropdown-toggle[aria-expanded="true"]');
                        alternadoresAbiertos.forEach(alternador => {
                            alternador.setAttribute('aria-expanded', 'false');
                        });
                    }
                });

                // Cerrar barra lateral al hacer clic en overlay (móvil)
                superposicionCuerpo.addEventListener('click', function() {
                    barraLateral.classList.remove('active');
                    contenido.classList.remove('active');
                    superposicionCuerpo.classList.remove('active');
                    document.body.style.overflow = 'auto';
                });

                // Auto-cerrar barra lateral en móvil al hacer clic en enlaces
                const enlacesNavegacion = document.querySelectorAll('#sidebar .components a:not(.dropdown-toggle)');
                enlacesNavegacion.forEach(enlace => {
                    enlace.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            barraLateral.classList.remove('active');
                            contenido.classList.remove('active');
                            superposicionCuerpo.classList.remove('active');
                            document.body.style.overflow = 'auto';
                        }
                    });
                });

                // Manejo de dropdowns mejorado
                const alternadoresDesplegables = document.querySelectorAll('#sidebar .dropdown-toggle');
                alternadoresDesplegables.forEach(alternador => {
                    alternador.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Si barra lateral colapsada en desktop, no permitir dropdown
                        if (barraLateral.classList.contains('active') && window.innerWidth > 768) {
                            return false;
                        }

                        const idObjetivo = this.getAttribute('href').substring(1);
                        const colapsarObjetivo = document.getElementById(idObjetivo);

                        if (colapsarObjetivo) {
                            const estaAbierto = colapsarObjetivo.classList.contains('show');

                            // Cerrar otros dropdowns
                            alternadoresDesplegables.forEach(otroAlternador => {
                                if (otroAlternador !== this) {
                                    const otroId = otroAlternador.getAttribute('href').substring(1);
                                    const otroColapsar = document.getElementById(otroId);
                                    if (otroColapsar) {
                                        otroColapsar.classList.remove('show');
                                        otroAlternador.setAttribute('aria-expanded', 'false');
                                    }
                                }
                            });

                            // Alternar actual
                            if (estaAbierto) {
                                colapsarObjetivo.classList.remove('show');
                                this.setAttribute('aria-expanded', 'false');
                            } else {
                                colapsarObjetivo.classList.add('show');
                                this.setAttribute('aria-expanded', 'true');
                            }
                        }
                    });
                });

                // Ajustar barra lateral en redimensión
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        superposicionCuerpo.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    } else {
                        // En móvil, asegurar que barra lateral esté oculta por defecto
                        if (!barraLateral.classList.contains('active')) {
                            barraLateral.classList.remove('active');
                            contenido.classList.remove('active');
                        }
                    }
                });

                // Función para manejar estados activos
                function manejarEstadosActivos() {
                    const rutaActual = window.location.pathname;
                    const enlacesNavegacion = document.querySelectorAll('#sidebar .components a');

                    enlacesNavegacion.forEach(enlace => {
                        const href = enlace.getAttribute('href');
                        if (href && rutaActual.includes(href)) {
                            enlace.parentElement.classList.add('active');
                        }
                    });
                }

                // Inicializar estados activos
                manejarEstadosActivos();
            });
        </script>