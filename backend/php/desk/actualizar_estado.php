<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_nombre = strtolower(trim($data['nuevo_estado'] ?? ''));

// CORRECCIÓN: Mapeo correcto del frontend a la BD
$estadoMap = [
    'pendiente' => 'pendiente',
    'en proceso' => 'en proceso',  // ← CORREGIDO: coincide con lo que envía JS
    'resuelto' => 'resuelto'
];

$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

header('Content-Type: application/json');

// Debug para ayudar a identificar problemas
error_log("DEBUG - Estado recibido: " . ($data['nuevo_estado'] ?? 'null'));
error_log("DEBUG - Estado procesado: " . ($nuevo_estado_nombre ?? 'null'));

if (!$id_incidente || !$nuevo_estado_nombre) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos.',
        'debug' => [
            'id_incidente' => $id_incidente,
            'estado_original' => $data['nuevo_estado'] ?? null,
            'estado_procesado' => $nuevo_estado_nombre
        ]
    ]);
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

        // Verificar que se actualizó
        if ($updateStmt->rowCount() > 0) {
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Estado actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'No se pudo actualizar el registro'
            ]);
        }
    } else {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Estado no válido: ' . $nuevo_estado_nombre
        ]);
    }
} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
