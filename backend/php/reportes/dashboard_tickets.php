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

    // ✅ Tickets por usuario asignado
    $stmt = $conexion->prepare("
        SELECT 
            u.Nombre + ' ' + u.Apellido_Paterno + ' ' + u.Apellido_Materno AS nombre_usuario,
            COUNT(*) AS cantidad
        FROM tb_Incidentes i
        INNER JOIN tb_Usuarios u ON i.Id_Usuarios = u.Id_Usuarios
        WHERE CONVERT(date, i.Fecha_Creacion) BETWEEN ? AND ?
        GROUP BY u.Nombre, u.Apellido_Paterno, u.Apellido_Materno
        ORDER BY cantidad DESC
    ");
    $stmt->execute([$inicioMes, $finMes]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Respuesta JSON
    header('Content-Type: application/json');
    echo json_encode([
        'total' => (int)$total,
        'pendientes' => (int)$pendientesCount,
        'proceso' => (int)$procesoCount,
        'resueltos' => (int)$resueltosCount,
        'meses' => $meses,
        'areas' => $areas,
        'usuarios' => $usuarios, // ✅ nuevo campo para gráfico por usuario
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
        'areas' => [],
        'usuarios' => []
    ]);
}
