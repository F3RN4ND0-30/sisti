<?php
header('Content-Type: application/json');
require_once '../../bd/conexion.php';

$tipo = $_GET['tipo'] ?? null;
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!$tipo) {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó el tipo de filtro']);
    exit;
}

try {
    if ($tipo === 'dia') {
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
                FORMAT(i.Fecha_Creacion, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Creacion,
                FORMAT(i.Fecha_Resuelto, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Resuelto
            FROM tb_Incidentes i
            INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
            INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
            INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
            INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
            WHERE CONVERT(date, i.Fecha_Creacion) = :fecha
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':fecha' => $fecha]);
        $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                'fecha_resuelto' => $i['Fecha_Resuelto']
            ];
        }

        echo json_encode([
            'success' => true,
            'total_tickets' => count($detalle),
            'detalle' => $detalle
        ]);
    } elseif ($tipo === 'semana') {
        $mes = $_GET['fecha'] ?? date('Y-m');  // formato "YYYY-MM"
        $semana = (int) ($_GET['semana'] ?? 1);  // Semana 1, 2, 3, 4, ...

        // Crear objeto DateTime para el primer día del mes
        $inicioMes = new DateTime($mes . '-01');

        // Ajustar al lunes de la primera semana del mes (puede ser anterior al día 1 si el 1 no es lunes)
        $diaSemanaInicioMes = (int)$inicioMes->format('N'); // 1 (Lunes) - 7 (Domingo)
        if ($diaSemanaInicioMes !== 1) {
            $inicioMes->modify('last monday');
        }

        // Calcular fecha inicio de la semana seleccionada
        $fechaInicio = clone $inicioMes;
        $fechaInicio->modify('+' . (($semana - 1) * 7) . ' days');

        // Calcular fecha fin (domingo) de esa semana
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify('+6 days');

        // Limitar fechas al mes actual para evitar incluir días fuera del mes
        $primerDiaMes = new DateTime($mes . '-01');
        $ultimoDiaMes = new DateTime($mes . '-01');
        $ultimoDiaMes->modify('last day of this month');

        if ($fechaInicio < $primerDiaMes) {
            $fechaInicio = clone $primerDiaMes;
        }
        if ($fechaFin > $ultimoDiaMes) {
            $fechaFin = clone $ultimoDiaMes;
        }

        $sql = "
            SELECT 
                CONVERT(date, i.Fecha_Creacion) as dia,
                t.Codigo_Ticket AS numero_ticket,
                u.Dni,
                u.Nombre AS nombre_usuario,
                u.Apellido_Paterno,
                u.Apellido_Materno,
                a.Nombre AS nombre_area,
                i.Descripcion,
                ei.Nombre AS estado_texto,
                FORMAT(i.Fecha_Creacion, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Creacion,
                FORMAT(i.Fecha_Resuelto, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Resuelto
            FROM tb_Incidentes i
            INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
            INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
            INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
            INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
            WHERE CONVERT(date, i.Fecha_Creacion) BETWEEN :inicio AND :fin
            ORDER BY i.Fecha_Creacion
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':inicio' => $fechaInicio->format('Y-m-d'),
            ':fin' => $fechaFin->format('Y-m-d')
        ]);
        $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Crear arreglo con todos los días del rango para garantizar días sin tickets también
        $detalle = [];
        $intervalo = new DateInterval('P1D');
        $periodo = new DatePeriod($fechaInicio, $intervalo, $fechaFin->modify('+1 day')); // +1 para incluir último día

        foreach ($periodo as $dia) {
            $detalle[$dia->format('Y-m-d')] = [
                'dia_nombre' => $dia->format('l'),
                'tickets' => []
            ];
        }

        // Llenar tickets en el arreglo
        foreach ($incidentes as $i) {
            $dia = $i['dia'];
            if (isset($detalle[$dia])) {
                $detalle[$dia]['tickets'][] = [
                    'numero_ticket' => $i['numero_ticket'],
                    'dni' => $i['Dni'],
                    'nombre' => $i['nombre_usuario'],
                    'apellido' => $i['Apellido_Paterno'] . ' ' . $i['Apellido_Materno'],
                    'area' => $i['nombre_area'],
                    'descripcion' => $i['Descripcion'],
                    'estado_texto' => $i['estado_texto'],
                    'fecha_creacion' => $i['Fecha_Creacion'],
                    'fecha_resuelto' => $i['Fecha_Resuelto']
                ];
            }
        }

        $resultado = [];
        foreach ($detalle as $fechaDia => $info) {
            $resultado[] = [
                'fecha' => $fechaDia,
                'dia' => $info['dia_nombre'],
                'tickets' => $info['tickets'],
                'total' => count($info['tickets'])
            ];
        }

        $totalTickets = array_sum(array_column($resultado, 'total'));

        // Log para debug (opcional, elimina después)
        // file_put_contents('debug_log.txt', print_r(['inicio' => $fechaInicio->format('Y-m-d'), 'fin' => $fechaFin->format('Y-m-d'), 'resultado' => $resultado], true));

        echo json_encode([
            'success' => true,
            'total_tickets' => $totalTickets,
            'detalle' => $resultado
        ]);
    } elseif ($tipo === 'mes') {
        $anioMes = date('Y-m', strtotime($fecha));

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
                FORMAT(i.Fecha_Creacion, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Creacion,
                FORMAT(i.Fecha_Resuelto, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Resuelto
            FROM tb_Incidentes i
            INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
            INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
            INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
            INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
            WHERE FORMAT(i.Fecha_Creacion, 'yyyy-MM') = :anioMes
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':anioMes' => $anioMes]);
        $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                'fecha_resuelto' => $i['Fecha_Resuelto']
            ];
        }

        echo json_encode([
            'success' => true,
            'total_tickets' => count($detalle),
            'detalle' => $detalle
        ]);
    } elseif ($tipo === 'anio') {
        $anio = $fecha; // "fecha" será el año como string (ej. "2024")

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
            FORMAT(i.Fecha_Creacion, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Creacion,
            FORMAT(i.Fecha_Resuelto, 'yyyy-MM-dd HH:mm:ss') AS Fecha_Resuelto
        FROM tb_Incidentes i
        INNER JOIN tb_Tickets t ON t.Id_Tickets = i.Id_Tickets
        INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
        INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
        INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
        WHERE YEAR(i.Fecha_Creacion) = :anio
    ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':anio' => $anio]);
        $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                'fecha_resuelto' => $i['Fecha_Resuelto']
            ];
        }

        echo json_encode([
            'success' => true,
            'total_tickets' => count($detalle),
            'detalle' => $detalle
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Filtro no válido']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . $e->getMessage()]);
}
