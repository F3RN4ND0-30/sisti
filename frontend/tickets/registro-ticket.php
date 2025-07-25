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
    <script src="../../backend/js/tickets/registro-ticket.js" defer></script>
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

                        <div class="grupo-formulario tiene-retroalimentacion">
                            <select id="area" name="area" class="control-formulario" required>
                                <option value="">Seleccione Área</option>
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
                            <span class="material-icons icono-control-formulario">domain</span>
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

    <!-- Modal de Ticket Creado -->
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
</body>

</html>