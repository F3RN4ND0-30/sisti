<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// Obtener primer y último día del mes actual
$inicioMes = (new DateTime('first day of this month'))->format('Y-m-d');
$finMes = (new DateTime('last day of this month'))->format('Y-m-d');

// Totales por estado (solo mes actual)
$stmt = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE CONVERT(date, Fecha_Creacion) BETWEEN :inicio AND :fin");
$stmt->execute([':inicio' => $inicioMes, ':fin' => $finMes]);
$total = $stmt->fetchColumn();

$pendientes = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 1 AND CONVERT(date, Fecha_Creacion) BETWEEN :inicio AND :fin");
$pendientes->execute([':inicio' => $inicioMes, ':fin' => $finMes]);

$proceso = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 2 AND CONVERT(date, Fecha_Creacion) BETWEEN :inicio AND :fin");
$proceso->execute([':inicio' => $inicioMes, ':fin' => $finMes]);

$resueltos = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 3 AND CONVERT(date, Fecha_Creacion) BETWEEN :inicio AND :fin");
$resueltos->execute([':inicio' => $inicioMes, ':fin' => $finMes]);

// Tickets por mes (sin filtro de fecha)
$meses = $conexion->query("
    SELECT FORMAT(Fecha_Creacion, 'MM/yyyy') AS nombre, COUNT(*) AS cantidad
    FROM tb_Incidentes
    GROUP BY FORMAT(Fecha_Creacion, 'MM/yyyy')
    ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

// Tickets por semana del mes actual
$fechaActual = new DateTime();
$inicioMes = $fechaActual->modify('first day of this month')->format('Y-m-d');
$finMes = (new DateTime())->modify('last day of this month')->format('Y-m-d');

$semanas = [
    'Semana 1' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
    'Semana 2' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
    'Semana 3' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
    'Semana 4' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
    'Semana 5' => ['inicio' => null, 'fin' => null, 'cantidad' => 0],
];

$inicio = new DateTime($inicioMes);
for ($i = 0; $i < 4; $i++) {
    $inicioSemana = clone $inicio;
    $inicioSemana->modify("+{$i} weeks");
    $finSemana = clone $inicioSemana;
    $finSemana->modify('+6 days');

    // Límite del mes
    if ($finSemana->format('Y-m-d') > $finMes) {
        $finSemana = new DateTime($finMes);
    }

    $nombreSemana = 'Semana ' . ($i + 1);
    $semanas[$nombreSemana]['inicio'] = $inicioSemana->format('Y-m-d');
    $semanas[$nombreSemana]['fin'] = $finSemana->format('Y-m-d');

    $stmt = $conexion->prepare("
        SELECT COUNT(*) FROM tb_Incidentes
        WHERE CONVERT(date, Fecha_Creacion) BETWEEN :inicio AND :fin
    ");
    $stmt->execute([
        ':inicio' => $semanas[$nombreSemana]['inicio'],
        ':fin' => $semanas[$nombreSemana]['fin']
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
    WHERE CONVERT(date, i.Fecha_Creacion) BETWEEN :inicio AND :fin
    GROUP BY a.Abreviatura
    ORDER BY cantidad DESC
");
$stmt->execute([':inicio' => $inicioMes, ':fin' => $finMes]);
$areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respuesta JSON
echo json_encode([
    'total' => (int)$total,
    'pendientes' => (int)$pendientes->fetchColumn(),
    'proceso' => (int)$proceso->fetchColumn(),
    'resueltos' => (int)$resueltos->fetchColumn(),
    'meses' => $meses,
    'semanas' => $labelsSemana,
    'totales' => $valoresSemana,
    'areas' => $areas
]);
