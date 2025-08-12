<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$inicioMes = (new DateTime('first day of this month'))->format('Y-m-d');
$finMes = (new DateTime('last day of this month'))->format('Y-m-d');

try {
    // Totales por estado
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE DATE(Fecha_Creacion) BETWEEN ? AND ?");
    $stmt->execute([$inicioMes, $finMes]);
    $total = $stmt->fetchColumn();

    $pendientes = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 1 AND DATE(Fecha_Creacion) BETWEEN ? AND ?");
    $pendientes->execute([$inicioMes, $finMes]);
    $pendientesCount = $pendientes->fetchColumn();

    $proceso = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 2 AND DATE(Fecha_Creacion) BETWEEN ? AND ?");
    $proceso->execute([$inicioMes, $finMes]);
    $procesoCount = $proceso->fetchColumn();

    $resueltos = $conexion->prepare("SELECT COUNT(*) FROM tb_Incidentes WHERE Id_Estados_Incidente = 3 AND DATE(Fecha_Creacion) BETWEEN ? AND ?");
    $resueltos->execute([$inicioMes, $finMes]);
    $resueltosCount = $resueltos->fetchColumn();

    // Tickets por mes (últimos 6 meses)
    $meses = $conexion->query("
    SELECT 
        DATE_FORMAT(Fecha_Creacion, '%m/%Y') AS nombre, 
        COUNT(*) AS cantidad
    FROM tb_Incidentes
    WHERE Fecha_Creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(Fecha_Creacion, '%Y-%m')
    ORDER BY DATE_FORMAT(Fecha_Creacion, '%Y-%m')
")->fetchAll(PDO::FETCH_ASSOC);

    // Tickets por área
    $stmt = $conexion->prepare("
    SELECT a.Abreviatura AS abreviatura, COUNT(*) AS cantidad
    FROM tb_Incidentes i
    INNER JOIN tb_Areas a ON a.Id_Areas = i.Id_Areas
    WHERE DATE(i.Fecha_Creacion) BETWEEN ? AND ?
    GROUP BY a.Abreviatura
    ORDER BY cantidad DESC
");
    $stmt->execute([$inicioMes, $finMes]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tickets por técnico
    $stmt = $conexion->prepare("
    SELECT 
        CONCAT(u.Nombre, ' ', u.Apellido_Paterno) AS nombre_completo, 
        COUNT(*) AS cantidad
    FROM tb_Incidentes i
    INNER JOIN tb_Usuarios u ON u.Id_Usuarios = i.Id_Usuarios
    INNER JOIN tb_Roles r ON r.Id_Roles = u.Id_Roles
    WHERE r.Nombre = 'tecnico'
      AND DATE(i.Fecha_Creacion) BETWEEN ? AND ?
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
        'usuarios' => $usuarios
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
