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
$stmtNum = $conexion->query("SELECT ISNULL(MAX(Numero), 0) + 1 AS nuevo_num FROM ficha_control");
$numeroNuevo = (int)$stmtNum->fetchColumn();
$numeroFormateado = str_pad($numeroNuevo, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ficha Mantenimiento</title>
    <!-- css Basicos -->
    <link rel="stylesheet" href="../../../backend/css/reportes/reporte_atencion/ficha_mantenimiento.css">
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
</head>

<body>

    <?php include '../../navbar/navbar.php'; ?>

    <div class="form-container">

        <h2>Ficha de Mantenimiento</h2>

        <form method="POST" action="/sisti/backend/php/excel/generar_excel_mantenimiento.php">
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
                    <td><input type="text" name="unidad_organica" required></td>
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
                    <td><input type="text" name="dni_trabajador"></td>
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
                <button type="submit">Generar Ficha</button>
            </div>
        </form>
    </div>

    <script>
        const subtiposPorTipo = {
            hardware: [
                "CPU", "Monitor", "Teclado", "Mouse", "Estabilizador", "Impresora", "Supresor de Pico", "Otros"
            ],
            sistemas: [
                "SIAF", "SIGA", "Sistema Registro Civil", "RUBEM", "RUB PVL 20", "SISPLA", "Sistema Vía Web", "Otros"
            ],
            software: [
                "Sistema Operativo", "Word", "Excel", "Power Point", "Internet", "Antivirus", "Otros"
            ],
            redes: [
                "Internet", "Modem", "Router", "Switch", "Cableado", "Otros"
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

</body>

</html>