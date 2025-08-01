<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

header('Content-Type: application/json');

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_raw = $data['nuevo_estado'] ?? '';

// Normalizar el nombre recibido
$nuevo_estado_nombre = strtolower(trim(preg_replace('/\s+/', ' ', $nuevo_estado_raw)));

// Mapeo flexible
$estadoMap = [
    'pendiente'   => 'pendiente',
    'en proceso'  => 'en proceso',
    'en_proceso'  => 'en proceso',
    'proceso'     => 'en proceso',
    'resuelto'    => 'resuelto'
];

// Asignar el nombre final si existe en el mapa
$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

// Debug
error_log("DEBUG - Estado recibido: $nuevo_estado_raw");
error_log("DEBUG - Estado normalizado: $nuevo_estado_nombre");

if (!$id_incidente || !$nuevo_estado_nombre) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos o estado no reconocido.',
        'debug' => [
            'id_incidente' => $id_incidente,
            'estado_raw' => $nuevo_estado_raw,
            'estado_normalizado' => $nuevo_estado_nombre
        ]
    ]);
    exit;
}

try {
    // Buscar ID de estado en BD
    $stmt = $conexion->prepare("SELECT Id_Estados_Incidente FROM tb_estados_incidente WHERE LOWER(Nombre) = :nombre");
    $stmt->execute([':nombre' => $nuevo_estado_nombre]);
    $estado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estado) {
        echo json_encode([
            'exito' => false,
            'mensaje' => "Estado no válido en la BD: $nuevo_estado_nombre"
        ]);
        exit;
    }

    $id_estado = $estado['Id_Estados_Incidente'];

    // Actualizar estado
    $updateStmt = $conexion->prepare("UPDATE tb_incidentes SET Id_Estados_Incidente = :estado WHERE Id_Incidentes = :id");
    $updateStmt->execute([
        ':estado' => $id_estado,
        ':id' => $id_incidente
    ]);

    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'exito' => true,
            'mensaje' => 'Estado actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'No se actualizó ningún registro (¿Estado ya estaba igual?)'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
