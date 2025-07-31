<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sishelpdesk/backend/bd/conexion.php';

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_nombre = strtolower(trim($data['nuevo_estado'] ?? ''));

// Mapeo simplificado del frontend a la BD
$estadoMap = [
    'pendiente' => 'pendiente',
    'proceso' => 'en proceso',
    'resuelto' => 'resuelto'
];

$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

header('Content-Type: application/json');

if (!$id_incidente || !$nuevo_estado_nombre) {
    echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos.']);
    exit;
}

try {
    // Buscar el ID del estado
    $stmt = $conexion->prepare("SELECT Id_Estados_Incidente FROM tb_estados_incidente WHERE LOWER(Nombre) = :nombre");
    $stmt->execute([':nombre' => $nuevo_estado_nombre]);
    $estado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estado) {
        $id_estado = $estado['Id_Estados_Incidente'];

        $updateStmt = $conexion->prepare("UPDATE tb_incidentes SET Id_Estados_Incidente = :estado WHERE Id_Incidentes = :id");
        $updateStmt->execute([
            ':estado' => $id_estado,
            ':id' => $id_incidente
        ]);

        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Estado no válido.']);
    }
} catch (PDOException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
