<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/php/reportes/GeneradorExcel.php';

$fecha = $_GET['fecha'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$semana = $_GET['semana'] ?? null;

if (!$fecha || !$tipo) {
    die("Parámetros incompletos.");
}

try {
    $params = [];
    $sql = "";
    $titulo = "";
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

    switch ($tipo) {
        case 'dia':
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) die("Fecha inválida para tipo día.");

            $titulo = "REPORTE DE ATENCIÓN DE TICKETS DEL DÍA " . $fechaObj->format('d/m/Y');

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
                WHERE DATE(i.Fecha_Creacion) = :fecha
            ";
            $params = [':fecha' => $fecha];
            break;

        case 'mes':
            [$anio, $mes] = explode('-', $fecha);
            $titulo = "REPORTE DE ATENCIÓN DE TICKETS DEL MES DE " . strtoupper($meses[$mes]);

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
                WHERE DATE_FORMAT(i.Fecha_Creacion, '%Y-%m') = :mes
            ";
            $params = [':mes' => $fecha];
            break;

        case 'semana':
            if (!$semana) die("Semana no especificada.");
            if (!$fecha) die("Mes no especificado.");

            $inicioMes = DateTime::createFromFormat('Y-m', $fecha);
            if (!$inicioMes) die("Formato de mes inválido.");

            $inicioSemana = clone $inicioMes;
            $inicioSemana->modify('first day of this month');
            $inicioSemana->modify('+' . ($semana - 1) . ' weeks');

            $finSemana = clone $inicioSemana;
            $finSemana->modify('+6 days');

            $fechaInicio = $inicioSemana->format('Y-m-d');
            $fechaFin = $finSemana->format('Y-m-d');

            $titulo = "REPORTE DE ATENCIÓN DE TICKETS DEL MES DE " . strtoupper($meses[$inicioMes->format('m')]) . " - SEMANA $semana";

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
                WHERE DATE(i.Fecha_Creacion) BETWEEN :inicio AND :fin
            ";
            $params = [
                ':inicio' => $fechaInicio,
                ':fin' => $fechaFin
            ];
            break;

        case 'anio':
            $anio = $fecha;
            if (!preg_match('/^\d{4}$/', $anio)) {
                die("Año inválido.");
            }

            $titulo = "REPORTE DE ATENCIÓN DE TICKETS DEL AÑO $anio";

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
            die("Filtro inválido.");
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$incidentes) {
        die("No hay datos para exportar.");
    }

    // Transformar datos
    $detalle = [];
    foreach ($incidentes as $i) {
        $detalle[] = [
            'numero_ticket' => $i['numero_ticket'],
            'dni' => $i['Dni'],
            'nombre' => $i['nombre_usuario'],
            'apellido' => $i['Apellido_Paterno'] . ' ' . $i['Apellido_Materno'],
            'area' => $i['nombre_area'],
            'descripcion' => $i['Descripcion'],
            'estado_texto' => $i['estado_texto'],
            'fecha_creacion' => $i['Fecha_Creacion'],
            'fecha_resuelto' => $i['Fecha_Resuelto'] ? $i['Fecha_Resuelto'] : '-'  // Si no hay fecha resuelto, guion
        ];
    }

    // Crear Excel
    $excel = new GeneradorExcel();
    $hoja = $excel->agregarHoja('Tickets');

    // 🔹 Escribir título centrado (B2 hasta K2)
    $spreadsheet = $excel->getSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->mergeCells('B2:K2');
    $sheet->setCellValue('B2', $titulo);
    $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // 🔹 Escribir encabezado (con Fecha resuelto)
    $encabezados = [
        'N° Ticket',
        'DNI',
        'Nombre',
        'Apellidos',
        'Área',
        'Descripción',
        'Estado',
        'Fecha de creación',
        'Fecha resuelto'
    ];
    $excel->escribirFilaEncabezado($hoja, $encabezados);

    // 🔹 Escribir datos
    foreach ($detalle as $fila) {
        $datosOrdenados = [
            $fila['numero_ticket'],
            $fila['dni'],
            $fila['nombre'],
            $fila['apellido'],
            $fila['area'],
            $fila['descripcion'],
            $fila['estado_texto'],
            $fila['fecha_creacion'],
            $fila['fecha_resuelto']
        ];
        $excel->escribirFilaDatos($hoja, $datosOrdenados);
    }

    // 🔹 Generar archivo
    $excel->generar("reporte_tickets_" . date('Ymd_His'));
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
