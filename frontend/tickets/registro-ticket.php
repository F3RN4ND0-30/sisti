<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../backend/bd/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Incidencia | HelpDesk</title>
    <link rel="stylesheet" href="../../backend/css/tickets/registro-ticket.css">
    <link rel="stylesheet" href="../../backend/css/tickets/modal-ticket.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="../../backend/js/tickets/registro-ticket.js"></script>

    <!-- jQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Estilos personalizados -->
    <style>
        /* Ajustar altura y bordes del select */
        .select2-container--default .select2-selection--single {
            height: 50px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            padding-left: 40px;
            /* espacio para ícono */
        }

        /* Ícono dentro del select */
        .select2-container--default .select2-selection--single::before {
            content: "\e7ee";
            /* Material icon: domain */
            font-family: 'Material Icons';
            font-size: 22px;
            color: #999;
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Texto dentro */
        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #555;
            line-height: 50px;
            padding-left: 5px !important;
        }

        /* Flecha */
        .select2-container--default .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }

        /* Foco */
        .select2-container--default .select2-selection--single:focus {
            border-color: #2563eb;
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.3);
            outline: none;
        }

        /* Resaltado */
        .highlight {
            font-weight: bold;
            color: #2563eb;
            background: #eef2ff;
            padding: 2px 4px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="contenedor-login">
        <div class="columna-formulario">
            <div class="caja-formulario">
                <div class="contenido-interno">
                    <div class="cabecera-formulario">
                        <div class="titulo">
                            <div class="logo-imagen">
                                <i class="material-icons" style="font-size: 40px; color: white;">confirmation_number</i>
                            </div>
                            <h2>Registrar Incidencia</h2>
                            <p>Ingrese los datos del problema técnico</p>
                        </div>
                    </div>

                    <form id="formTicket">
                        <div class="grupo-formulario tiene-retroalimentacion">
                            <input type="text" id="dni" name="dni" class="control-formulario" placeholder="DNI" maxlength="8" required autocomplete="off">
                            <span class="material-icons icono-control-formulario">badge</span>
                            <div id="dni-loader" style="display: none; position: absolute; right: 15px; top: 50%; transform: translateY(-50%);">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MDAgNDAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxjaXJjbGUgY3g9IjIwMCIgY3k9IjIwMCIgcj0iMTgwIiBzdHJva2U9IiMyNTYzZWIiIHN0cm9rZS13aWR0aD0iNDAiIGZpbGw9Im5vbmUiIHN0cm9rZS1kYXNoYXJyYXk9IjMwMCw1MDAiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgZHVyPSIxcyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHR5cGU9InJvdGF0ZSIgZnJvbT0iMCAyMDAgMjAwIiB0bz0iMzYwIDIwMCAyMDAiLz48L2NpcmNsZT48L3N2Zz4=" alt="Cargando" width="22">
                            </div>
                        </div>

                        <div class="grupo-formulario tiene-retroalimentacion">
                            <input type="text" id="nombre" name="nombre" class="control-formulario" placeholder="Nombres" readonly required>
                            <span class="material-icons icono-control-formulario">person</span>
                        </div>

                        <div class="grupo-formulario tiene-retroalimentacion">
                            <input type="text" id="apPaterno" name="apPaterno" class="control-formulario" placeholder="Apellido Paterno" readonly required>
                            <span class="material-icons icono-control-formulario">account_circle</span>
                        </div>

                        <div class="grupo-formulario tiene-retroalimentacion">
                            <input type="text" id="apMaterno" name="apMaterno" class="control-formulario" placeholder="Apellido Materno" readonly required>
                            <span class="material-icons icono-control-formulario">account_circle</span>
                        </div>

                        <!-- Select con buscador directo -->
                        <div class="grupo-formulario tiene-retroalimentacion">
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

                        <div class="grupo-formulario tiene-retroalimentacion">
                            <textarea id="descripcion" name="descripcion" rows="3" class="control-formulario" placeholder="Describa el problema..." required></textarea>
                            <span class="material-icons icono-control-formulario">report_problem</span>
                        </div>

                        <div class="acciones">
                            <button type="submit" class="boton boton-enviar">Enviar Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="columna-imagen">
            <h1 style="font-size: 34px;">HelpDesk MPP</h1>
            <p style="font-size: 16px; margin-top: 10px;">Soporte Técnico Municipal</p>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalTicket" class="modal" style="display:none;">
        <div class="modal-contenido">
            <span id="cerrarModal" class="cerrar">&times;</span>
            <h3>✅ Ticket creado con éxito</h3>
            <p id="mensajeModal"></p>
            <p style="margin-top: 10px; font-style: italic; color: #555;">
                Guarda el ticket para hacerle seguimiento a tu solicitud.
            </p>
            <button id="irInicio" class="boton boton-enviar">Ir al Inicio</button>
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