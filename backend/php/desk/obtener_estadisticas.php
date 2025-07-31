<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/helpdesk_mpp2.0/backend/bd/conexion.php';

header('Content-Type: application/json');

try {
    // Total incidentes HOY
    $stmtHoy = $conexion->prepare("SELECT COUNT(*) AS total FROM tb_incidentes WHERE CAST(fecha_creacion AS DATE) = CAST(GETDATE() AS DATE)");
    $stmtHoy->execute();
    $totalHoy = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Incidentes por estado
    $stmtPend = $conexion->prepare("SELECT COUNT(*) AS total FROM tb_incidentes WHERE Id_Estados_Incidente = 1");
    $stmtPend->execute();
    $pendientes = $stmtPend->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtProc = $conexion->prepare("SELECT COUNT(*) AS total FROM tb_incidentes WHERE Id_Estados_Incidente = 2");
    $stmtProc->execute();
    $proceso = $stmtProc->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtRes = $conexion->prepare("SELECT COUNT(*) AS total FROM tb_incidentes WHERE Id_Estados_Incidente = 3");
    $stmtRes->execute();
    $resueltos = $stmtRes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'total_hoy' => $totalHoy,
        'pendientes' => $pendientes,
        'proceso' => $proceso,
        'resueltos' => $resueltos
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
