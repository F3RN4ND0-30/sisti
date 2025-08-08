<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../../login.php');
    exit();
}

require_once '../../../backend/bd/conexion.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Crear Ticket | HelpDesk </title>

    <!-- CSS -->
    <link rel="stylesheet" href="../../../backend/css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../../backend/css/tickets/modal-ticket.css">
    <link rel="stylesheet" href="../../../backend/css/tickets/crear-ticket.css">

    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />

    <!-- jQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- JS  -->
    <script src="../../../backend/js/tickets/registro-ticket.js" defer></script>
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>

    <div class="main-content">
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-12">
                    <h2><i class="material-icons" style="vertical-align: middle;">add_circle</i> Crear Nuevo Ticket</h2>
                    <p class="mb-0">Registrar incidencia desde el panel administrativo</p>
                </div>
            </div>
        </div>

        <div class="form-container">
            <form id="formTicket">
                <div class="form-row">
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <input type="text" id="dni" name="dni" class="form-control" maxlength="8" required autocomplete="off">
                        <div id="dni-loader" style="display: none; position: absolute; right: 15px; top: 50%; transform: translateY(-50%);">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MDAgNDAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxjaXJjbGUgY3g9IjIwMCIgY3k9IjIwMCIgcj0iMTgwIiBzdHJva2U9IiMyNTYzZWIiIHN0cm9rZS13aWR0aD0iNDAiIGZpbGw9Im5vbmUiIHN0cm9rZS1kYXNoYXJyYXk9IjMwMCw1MDAiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHR5cGU9InJvdGF0ZSIgZnJvbT0iMCAyMDAgMjAwIiB0bz0iMzYwIDIwMCAyMDAiLz48L2NpcmNsZT48L3N2Zz4=" alt="Cargando" width="22">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombres</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" readonly required>

                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="apPaterno">Apellido Paterno</label>
                        <input type="text" id="apPaterno" name="apPaterno" class="form-control" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="apMaterno">Apellido Materno</label>
                        <input type="text" id="apMaterno" name="apMaterno" class="form-control" readonly required>
                    </div>
                </div>
                <div class="form-row area-descripcion">
                    <div class="form-group">
                        <label for="area">Área</label>
                        <select id="area" name="area" required>
                            <option value="">Seleccione o escriba para buscar</option>
                            <?php
                            try {
                                $stmt = $conexion->prepare("SELECT Id_Areas, Nombre FROM tb_Areas WHERE Estado = 1");
                                $stmt->execute();
                                $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($areas as $area) {
                                    echo "<option value='" . htmlspecialchars($area['Id_Areas']) . "'>" . htmlspecialchars($area['Nombre']) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error al cargar áreas</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción del problema</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Describa el problema..."></textarea>
                    </div>
                </div>
                <div class="form-row boton-submit">
                    <div class="form-group" style="width: auto;">
                        <button type="submit" class="btn-primary">Enviar Ticket</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Ticket Creado -->
    <div id="modalTicket" class="modal" style="display:none;">
        <div class="modal-contenido">
            <span id="cerrarModal" class="cerrar">&times;</span>
            <h3>✅ Ticket creado con éxito</h3>
            <p id="mensajeModal"></p>
            <p style="margin-top: 10px; font-style: italic; color: #555;">
                Guarda el ticket para hacerle seguimiento a tu solicitud.
            </p>
            <button id="copiarTicket" class="boton boton-copiar" style="margin-bottom: 10px;">Copiar Ticket</button>
            <button id="aceptar" class="boton boton-enviar">Aceptar</button>
        </div>
    </div>

    <!-- Inicialización Select2 con resaltado -->
    <script>
        $(document).ready(function() {
            function highlightMatch(text, term) {
                const regex = new RegExp('(' + term + ')', 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function customTemplate(state, container) {
                const term = $('.select2-search__field').val();
                if (!term) return state.text;
                return highlightMatch(state.text, term);
            }

            $("#area").select2({
                placeholder: "Seleccione o escriba para buscar",
                allowClear: true,
                width: '100%',
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: customTemplate
            });
        });
    </script>
</body>

</html>