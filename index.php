<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk - Municipalidad Provincial de Pisco</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="backend/css/index.css">
    <link rel="icon" type="image/png" href="backend/img/logoPisco.png" />
</head>

<body>
    <!-- Logo Municipalidad -->
    <div class="muni-logo">
        <img src="backend/img/munipisco.png" alt="Municipalidad de Pisco">
    </div>

    <!-- Header con Login -->
    <div class="header">
        <a href="frontend/login.php" class="login-btn">
            <i class="material-icons">admin_panel_settings</i>
            Acceso
        </a>
    </div>

    <!-- Container Principal -->
    <div class="main-container">
        <!-- Área de Contenido -->
        <div class="content-area">
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="logo-container">
                    <img src="backend/img/logoPisco.png" alt="Escudo de Pisco">
                </div>

                <h1 class="main-title">HelpDesk</h1>
                <p class="main-description">
                    Sistema de tickets para reportar problemas técnicos. Rápido, fácil y con seguimiento en tiempo real.
                </p>
            </div>

            <!-- Botones de Acción -->
            <div class="action-section">
                <a href="frontend/tickets/registro-ticket.php" class="action-btn btn-primary-action">
                    <i class="material-icons">add_circle</i>
                    Registrar Incidencia
                </a>

                <a href="seguimiento.php" class="action-btn btn-outline-action">
                    <i class="material-icons">search</i>
                    Consultar Ticket
                </a>
            </div>

            <!-- Cómo Funciona -->
            <div class="how-it-works">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Reporta</h3>
                    <p class="step-description">
                        Ingresa tu DNI y describe el problema técnico.
                    </p>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Consulta</h3>
                    <p class="step-description">
                        Revisa el estado usando tu número de ticket.
                    </p>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Resuelve</h3>
                    <p class="step-description">
                        El técnico atenderá tu caso y resolverá el problema.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        Unidad de Sistemas - Municipalidad Provincial de Pisco
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>