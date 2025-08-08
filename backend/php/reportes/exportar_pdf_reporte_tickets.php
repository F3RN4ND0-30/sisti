<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Parámetros
$fecha = $_GET['fecha'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$semana = $_GET['semana'] ?? null;

// Meses en español
$meses = [
    '01' => 'Enero',
    '02' => 'Febrero',
    '03' => 'Marzo',
    '04' => 'Abril',
    '05' => 'Mayo',
    '06' => 'Junio',
    '07' => 'Julio',
    '08' => 'Agosto',
    '09' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre'
];

try {
    // Consulta SQL según tipo
    switch ($tipo) {
        case 'dia':
            $titulo = "Reporte de atención de tickets del día " . date('d/m/Y', strtotime($fecha));
            $sql = "
                SELECT 
                    t.Codigo_Ticket AS numero_ticket,
                    u.Dni,
                    u.Nombre AS nombre_usuario,
                    u.Apellido_Paterno,
                    u.Apellido_Materno,
                    a.Nombre AS nombre_area,
                    i.Descripcion,
                    ei.Nombre AS estado_texto,
                    i.Fecha_Creacion,
                    i.Fecha_Resuelto
                FROM tb_Incidentes i
                INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
                INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
                INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
                INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                WHERE CONVERT(date, i.Fecha_Creacion) = :fecha
            ";
            $params = [':fecha' => $fecha];
            break;

        case 'semana':
            if (!$semana || !$fecha) die("Parámetros inválidos.");
            $mesNombre = $meses[date('m', strtotime($fecha))] ?? $fecha;
            $titulo = "Reporte de atención de tickets del mes de $mesNombre, semana $semana";

            $inicioMes = new DateTime($fecha);
            $inicioSemana = clone $inicioMes;
            $inicioSemana->modify('first day of this month')->modify('+' . ($semana - 1) . ' weeks');
            $finSemana = clone $inicioSemana;
            $finSemana->modify('+6 days');

            $fechaInicio = $inicioSemana->format('Y-m-d');
            $fechaFin = $finSemana->format('Y-m-d');

            $sql = "
                SELECT 
                    t.Codigo_Ticket AS numero_ticket,
                    u.Dni,
                    u.Nombre AS nombre_usuario,
                    u.Apellido_Paterno,
                    u.Apellido_Materno,
                    a.Nombre AS nombre_area,
                    i.Descripcion,
                    ei.Nombre AS estado_texto,
                    i.Fecha_Creacion,
                    i.Fecha_Resuelto
                FROM tb_Incidentes i
                INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
                INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
                INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
                INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                WHERE CONVERT(date, i.Fecha_Creacion) BETWEEN :inicio AND :fin
            ";
            $params = [':inicio' => $fechaInicio, ':fin' => $fechaFin];
            break;

        case 'mes':
            $mesNombre = $meses[date('m', strtotime($fecha))] ?? $fecha;
            $titulo = "Reporte de atención de tickets del mes de $mesNombre";
            $sql = "
                SELECT 
                    t.Codigo_Ticket AS numero_ticket,
                    u.Dni,
                    u.Nombre AS nombre_usuario,
                    u.Apellido_Paterno,
                    u.Apellido_Materno,
                    a.Nombre AS nombre_area,
                    i.Descripcion,
                    ei.Nombre AS estado_texto,
                    i.Fecha_Creacion,
                    i.Fecha_Resuelto
                FROM tb_Incidentes i
                INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
                INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
                INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
                INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                WHERE FORMAT(i.Fecha_Creacion, 'yyyy-MM') = :mes
            ";
            $params = [':mes' => $fecha];
            break;

        case 'anio':
            $anio = $fecha;
            if (!preg_match('/^\d{4}$/', $anio)) {
                die("Año inválido.");
            }

            $titulo = "Reporte de atención de tickets del año $anio";

            $sql = "
                SELECT 
                    t.Codigo_Ticket AS numero_ticket,
                    u.Dni,
                    u.Nombre AS nombre_usuario,
                    u.Apellido_Paterno,
                    u.Apellido_Materno,
                    a.Nombre AS nombre_area,
                    i.Descripcion,
                    ei.Nombre AS estado_texto,
                    i.Fecha_Creacion,
                    i.Fecha_Resuelto
                FROM tb_Incidentes i
                INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
                INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
                INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
                INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
                WHERE YEAR(i.Fecha_Creacion) = :anio
            ";
            $params = [':anio' => $anio];
            break;

        default:
            die("Tipo inválido.");
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$registros) die("No hay datos para mostrar.");

    ob_start();
?>
    <html>

    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
            }

            h2 {
                text-align: center;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #333;
                padding: 5px;
                word-wrap: break-word;
            }

            th {
                background-color: #2563EB;
                color: white;
                text-align: center;
            }

            td.center {
                text-align: center;
            }

            .ticket {
                width: 12%;
            }

            .dni {
                width: 7%;
            }

            .nombre {
                width: 15%;
            }

            .area {
                width: 17%;
            }

            .descripcion {
                width: 20%;
            }

            .estado {
                width: 8%;
            }

            .fecha {
                width: 10%;
            }
        </style>
    </head>

    <body>
        <h2><?php echo $titulo; ?></h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">N°</th>
                    <th class="ticket">Ticket</th>
                    <th class="dni">DNI</th>
                    <th class="nombre">Nombre</th>
                    <th class="area">Área</th>
                    <th class="descripcion">Descripción</th>
                    <th class="estado">Estado</th>
                    <th class="fecha">Fecha Creación</th>
                    <th class="fecha">Fecha Resuelto</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $contador = 1;
                foreach ($registros as $r):
                    $fechaCreacion = (new DateTime($r['Fecha_Creacion']))->format('d/m/Y H:i:s');
                    $fechaResuelto = $r['Fecha_Resuelto'] ? (new DateTime($r['Fecha_Resuelto']))->format('d/m/Y H:i:s') : '-';
                ?>
                    <tr>
                        <td class="center"><?php echo $contador++; ?></td>
                        <td class="center"><?php echo $r['numero_ticket']; ?></td>
                        <td class="center"><?php echo $r['Dni']; ?></td>
                        <td><?php echo $r['nombre_usuario'] . ' ' . $r['Apellido_Paterno'] . ' ' . $r['Apellido_Materno']; ?></td>
                        <td><?php echo $r['nombre_area']; ?></td>
                        <td><?php echo $r['Descripcion']; ?></td>
                        <td class="center"><?php echo $r['estado_texto']; ?></td>
                        <td class="center"><?php echo $fechaCreacion; ?></td>
                        <td class="center"><?php echo $fechaResuelto; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>

    </html>
<?php
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("reporte_tickets_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
