<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// Obtener ID de usuario desde la sesión
$id_usuario_actual = $_SESSION['hd_id'] ?? null;

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_raw = $data['nuevo_estado'] ?? '';

// Normalizar estado
$nuevo_estado_nombre = strtolower(trim(preg_replace('/\s+/', ' ', $nuevo_estado_raw)));

// Mapeo flexible de estados
$estadoMap = [
    'pendiente'   => 'pendiente',
    'en proceso'  => 'en proceso',
    'en_proceso'  => 'en proceso',
    'proceso'     => 'en proceso',
    'resuelto'    => 'resuelto'
];

$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

// Validaciones
if (!$id_incidente || !$nuevo_estado_nombre || !$id_usuario_actual) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos o sesión no válida.',
        'debug' => [
            'id_incidente' => $id_incidente,
            'estado_raw' => $nuevo_estado_raw,
            'estado_normalizado' => $nuevo_estado_nombre,
            'id_usuario_sesion' => $id_usuario_actual
        ]
    ]);
    exit;
}

try {
    // Obtener ID del estado
    $stmt = $conexion->prepare("
        SELECT Id_Estados_Incidente 
        FROM tb_estados_incidente 
        WHERE LOWER(Nombre) = :nombre
    ");
    $stmt->execute([':nombre' => $nuevo_estado_nombre]);
    $estado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estado) {
        echo json_encode([
            'exito' => false,
            'mensaje' => "Estado no válido en la base de datos: $nuevo_estado_nombre"
        ]);
        exit;
    }

    $id_estado = $estado['Id_Estados_Incidente'];

    // Actualizar estado y asignar usuario actual
    $updateStmt = $conexion->prepare("
        UPDATE tb_incidentes 
        SET Id_Estados_Incidente = :estado, 
            Id_Usuarios = :usuario 
        WHERE Id_Incidentes = :id
    ");
    $updateStmt->execute([
        ':estado' => $id_estado,
        ':usuario' => $id_usuario_actual,
        ':id' => $id_incidente
    ]);

    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'exito' => true,
            'mensaje' => 'Estado actualizado y usuario asignado correctamente.'
        ]);
    } else {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'No se modificó el registro (puede que ya tuviera el mismo estado o usuario).'
        ]);
    }
} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
