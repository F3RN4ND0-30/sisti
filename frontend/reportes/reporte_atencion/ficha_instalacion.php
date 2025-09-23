<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../login.php');
    exit();
}
$nombreTecnico = $_SESSION['hd_ficha'] ?? '';
$fechaHoy = date('d-m-Y');

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// Obtener el siguiente número de ficha
$stmtNum = $conexion->query("SELECT COALESCE(MAX(Numero), 0) + 1 AS nuevo_num FROM tb_ficha_control");
$numeroNuevo = (int)$stmtNum->fetchColumn();
$numeroFormateado = str_pad($numeroNuevo, 6, '0', STR_PAD_LEFT);
// Obtener las áreas desde la BD
$stmtAreas = $conexion->query("SELECT Id_Areas, Nombre, Encargado, DNI, Cargo FROM tb_areas ORDER BY Nombre ASC");
$areas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ficha Instalación</title>
    <!-- css Basicos -->
    <link rel="stylesheet" href="../../../backend/css/reportes/reporte_atencion/ficha_instalacion.css">
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />

    <!-- Selectize CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/selectize@0.12.6/dist/css/selectize.default.css">

    <!-- jQuery (ya lo debes tener) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Selectize JS -->
    <script src="https://cdn.jsdelivr.net/npm/selectize@0.12.6/dist/js/standalone/selectize.min.js"></script>
</head>

<body>

    <?php include '../../navbar/navbar.php'; ?>

    <div class="form-container">
        <h2>Ficha de Instalación</h2>
        <form method="POST" action="/sisti/backend/php/excel/generar_excel_instalacion.php" target="iframeInvisible"
            onsubmit="manejarEnvioFormulario(this)">
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipoFicha); ?>">

            <table>
                <tr>
                    <td>N° de Ficha</td>
                    <td>
                        <input
                            type="text"
                            name="numero_ficha"
                            required
                            readonly
                            value="<?php echo 'N° ' . $numeroFormateado; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>Unidad Orgánica</td>
                    <td>
                        <select id="unidad_organica" name="unidad_organica" required>
                            <option value="">-- Selecciona un área --</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= htmlspecialchars($area['Nombre']) ?>">
                                    <?= strtoupper(htmlspecialchars($area['Nombre'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td><input type="text" name="fecha" required readonly value="<?php echo $fechaHoy; ?>"></td>
                </tr>
                <tr>
                    <td>Encargado o responsable</td>
                    <td><input type="text" name="trabajador_municipal" required></td>
                </tr>
                <tr>
                    <td>Cargo</td>
                    <td><input type="text" name="cargo" required></td>
                </tr>
                <tr>
                    <td>DNI</td>
                    <td>
                        <input type="number" name="dni_trabajador" min="0" step="1"
                            oninput="this.value = this.value.slice(0, 8);">
                    </td>
                </tr>
                <tr>
                    <td>Doc. Requerimiento</td>
                    <td><input type="text" name="doc_requerimiento"></td>
                </tr>
                <tr>
                    <td>Nombre del Técnico</td>
                    <td><input type="text" name="nombre_tecnico" required readonly value="<?php echo htmlspecialchars($nombreTecnico); ?>"></td>
                </tr>
                <tr>
                    <td>Tipo</td>
                    <td>
                        <select id="tipo" name="tipo" required>
                            <option value="">-- Selecciona --</option>
                            <option value="hardware">Hardware</option>
                            <option value="sistemas">Sistemas</option>
                            <option value="software">Software</option>
                            <option value="redes">Redes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Subtipo</td>
                    <td>
                        <select id="subtipo" name="subtipo" required disabled>
                            <option value="">-- Primero selecciona un tipo --</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Descripción</td>
                    <td><input type="text" name="descripcion" required></td>
                </tr>
                <tr>
                    <td>Observación</td>
                    <td><textarea name="observacion" rows="4" required></textarea></td>
                </tr>
            </table>

            <div class="botones-container">
                <a href="/sisti/frontend/reportes/reporte_atencion/fichas.php" class="btn-volver">Volver</a>
                <button type="submit" id="btnGenerarFicha">Generar Ficha</button>
            </div>
        </form>
    </div>

    <iframe name="iframeInvisible" style="display: none;"></iframe>

    <script>
        const subtiposPorTipo = {
            hardware: [
                "CPU", "MONITOR", "TECLADO", "MOUSE", "ESTABILIZADOR", "IMPRESORA", "SUPRESOR DE PICO", "OTROS"
            ],
            sistemas: [
                "SIAF", "SIGA", "SISTEMA REGISTRO CIVIL", "RUBEM", "RUB PVL 20", "SISPLA", "SISTEMA VÍA WEB", "OTROS"
            ],
            software: [
                "SISTEMA OPERATIVO", "WORD", "EXCEL", "POWER POINT", "INTERNET", "ANTIVIRUS", "OTROS"
            ],
            redes: [
                "INTERNET", "MODEM", "ROUTER", "SWITCH", "CABLEADO", "OTROS"
            ]
        };

        document.getElementById('tipo').addEventListener('change', function() {
            const subtipoSelect = document.getElementById('subtipo');
            const tipo = this.value;

            subtipoSelect.innerHTML = '<option value="">-- Selecciona subtipo --</option>';
            subtipoSelect.disabled = true;

            if (subtiposPorTipo[tipo]) {
                subtiposPorTipo[tipo].forEach(function(sub) {
                    const opt = document.createElement('option');
                    opt.value = sub;
                    opt.text = sub;
                    subtipoSelect.appendChild(opt);
                });
                subtipoSelect.disabled = false;
            }
        });
    </script>

    <script>
        function manejarEnvioFormulario(form) {
            const boton = document.getElementById('btnGenerarFicha');
            boton.disabled = true;
            boton.textContent = 'Generando...';
            document.body.style.cursor = 'wait';

            // Resetear el formulario después de 1 segundo
            setTimeout(() => {
                form.reset();

                // Desactivar subtipo
                const subtipo = document.getElementById('subtipo');
                if (subtipo) {
                    subtipo.disabled = true;
                }

                // Actualizar N° de ficha vía AJAX
                fetch('/sisti/backend/ajax/obtener_numero_ficha.php')
                    .then(response => response.text())
                    .then(data => {
                        const inputFicha = form.querySelector('input[name="numero_ficha"]');
                        if (inputFicha) {
                            inputFicha.value = data;
                        }
                    });
            }, 1000);

            // Restaurar botón y cursor después de 7 segundos
            setTimeout(() => {
                boton.disabled = false;
                boton.textContent = 'Generar Ficha';
                document.body.style.cursor = 'default';
            }, 7000);
        }
    </script>

    <script>
        $(document).ready(function() {
            const $selectUnidad = $('#unidad_organica').selectize({
                create: false,
                sortField: 'text',
                maxOptions: 100,
                placeholder: 'Escribe para buscar...',
                onChange: function(value) {
                    const data = datosPorUnidad[value];

                    if (data) {
                        $('input[name="trabajador_municipal"]').val(data.encargado?.toUpperCase() || '');
                        $('input[name="dni_trabajador"]').val(data.dni || '');
                        $('input[name="cargo"]').val(data.cargo?.toUpperCase() || '');
                    } else {
                        // Limpiar si no hay datos
                        $('input[name="trabajador_municipal"]').val('');
                        $('input[name="dni_trabajador"]').val('');
                        $('input[name="cargo"]').val('');
                    }
                }
            });
        });
    </script>

    <script>
        const datosPorUnidad = <?php
                                $map = [];
                                foreach ($areas as $area) {
                                    $map[$area['Nombre']] = [
                                        'encargado' => $area['Encargado'],
                                        'dni' => $area['DNI'],
                                        'cargo' => $area['Cargo']
                                    ];
                                }
                                echo json_encode($map);
                                ?>;
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const campos = document.querySelectorAll('input[type="text"], textarea');

            campos.forEach(function(campo) {
                campo.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
        });
    </script>
</body>

</html>