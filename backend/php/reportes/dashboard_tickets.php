<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// Obtener primer y último día del mes actual
$inicioMes = (new DateTime('first day of this month'))->format('Y-m-d');
$finMes = (new DateTime('last day of this month'))->format('Y-m-d');

try {
    // Totales por estado (solo mes actual)
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE CONVERT(date, Fecha_Creacion) BETWEEN ? AND ?");
    $stmt->execute([$inicioMes, $finMes]);
    $total = $stmt->fetchColumn();

    $pendientes = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 1 AND CONVERT(date, Fecha_Creacion) BETWEEN ? AND ?");
    $pendientes->execute([$inicioMes, $finMes]);
    $pendientesCount = $pendientes->fetchColumn();

    $proceso = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 2 AND CONVERT(date, Fecha_Creacion) BETWEEN ? AND ?");
    $proceso->execute([$inicioMes, $finMes]);
    $procesoCount = $proceso->fetchColumn();

    $resueltos = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 3 AND CONVERT(date, Fecha_Creacion) BETWEEN ? AND ?");
    $resueltos->execute([$inicioMes, $finMes]);
    $resueltosCount = $resueltos->fetchColumn();

    // Tickets por mes (últimos 6 meses)
    $meses = $conexion->query("
        SELECT 
            FORMAT(Fecha_Creacion, 'MM/yyyy') AS nombre, 
            COUNT(*) AS cantidad
        FROM tb_Incidentes
        WHERE Fecha_Creacion >= DATEADD(month, -6, GETDATE())
        GROUP BY FORMAT(Fecha_Creacion, 'MM/yyyy'), YEAR(Fecha_Creacion), MONTH(Fecha_Creacion)
        ORDER BY YEAR(Fecha_Creacion), MONTH(Fecha_Creacion)
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Tickets por semana del mes actual
    $fechaActual = new DateTime();
    $inicioMesCalc = $fechaActual->modify('first day of this month')->format('Y-m-d');
    $finMesCalc = (new DateTime())->modify('last day of this month')->format('Y-m-d');

    $semanas = [
        'Semana 1' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
        'Semana 2' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
        'Semana 3' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
        'Semana 4' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
        'Semana 5' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
    ];

    $inicio = new DateTime($inicioMesCalc);
    for ($i = 0; $i < 5; $i++) {
        $inicioSemana = clone $inicio;
        $inicioSemana->modify("+{$i} weeks");
        $finSemana = clone $inicioSemana;
        $finSemana->modify('+6 days');

        // Límite del mes
        if ($finSemana->format('Y-m-d') > $finMesCalc) {
            $finSemana = new DateTime($finMesCalc);
        }

        $nombreSemana = 'Semana ' . ($i + 1);
        $semanas[$nombreSemana]['inicio'] = $inicioSemana->format('Y-m-d');
        $semanas[$nombreSemana]['fin'] = $finSemana->format('Y-m-d');

        $stmt = $conexion->prepare("
            SELECT COUNT(*) FROM tb_Incidentes
            WHERE CONVERT(date, Fecha_Creacion) BETWEEN ? AND ?
        ");
        $stmt->execute([
            $semanas[$nombreSemana]['inicio'],
            $semanas[$nombreSemana]['fin']
        ]);

        $semanas[$nombreSemana]['cantidad'] = (int)$stmt->fetchColumn();
    }

    // Formatear arrays para JS
    $labelsSemana = array_keys($semanas);
    $valoresSemana = array_column($semanas, 'cantidad');

    // Tickets por área (abreviatura y conteo solo del mes actual)
    $stmt = $conexion->prepare("
        SELECT a.Abreviatura AS abreviatura, COUNT(*) AS cantidad
        FROM tb_Incidentes i
        INNER JOIN tb_Areas a ON a.Id_Areas = i.Id_Areas
        WHERE CONVERT(date, i.Fecha_Creacion) BETWEEN ? AND ?
        GROUP BY a.Abreviatura
        ORDER BY cantidad DESC
    ");
    $stmt->execute([$inicioMes, $finMes]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Respuesta JSON
    header('Content-Type: application/json');
    echo json_encode([
        'total' => (int)$total,
        'pendientes' => (int)$pendientesCount,
        'proceso' => (int)$procesoCount,
        'resueltos' => (int)$resueltosCount,
        'meses' => $meses,
        'semanas' => $labelsSemana,
        'totales' => $valoresSemana,
        'areas' => $areas // ✅ Sin acento
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Error al cargar datos: ' . $e->getMessage(),
        'total' => 0,
        'pendientes' => 0,
        'proceso' => 0,
        'resueltos' => 0,
        'meses' => [],
        'semanas' => [],
        'totales' => [],
        'areas' => []
    ]);
}
