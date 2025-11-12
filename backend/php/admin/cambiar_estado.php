<?php
require_once '../../bd/conexion.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;

if (!$id || $estado === null) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $conexion->prepare("UPDATE tb_incidentes SET EstadoIncidente = :estado WHERE Id_Incidentes = :id");
    $stmt->execute([':estado' => $estado, ':id' => $id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
