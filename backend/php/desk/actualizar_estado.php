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

// Validaciones iniciales
if (!$id_incidente || !$nuevo_estado_nombre || !$id_usuario_actual) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos o sesión no válida.'
    ]);
    exit;
}

try {
    // Verificar a quién está asignado el incidente
    $stmt = $conexion->prepare("
        SELECT Id_Usuarios 
        FROM tb_incidentes 
        WHERE Id_Incidentes = :id
    ");
    $stmt->execute([':id' => $id_incidente]);
    $incidente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incidente) {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Incidente no encontrado.'
        ]);
        exit;
    }

    $id_usuario_asignado = $incidente['Id_Usuarios'];

    // Obtener ID del nuevo estado
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
            'mensaje' => "Estado no válido: $nuevo_estado_nombre"
        ]);
        exit;
    }

    $id_estado = $estado['Id_Estados_Incidente'];

    // Si aún no tiene usuario asignado, lo asignamos al usuario actual
    if ($id_usuario_asignado === null) {
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
    } else {
        // Solo actualiza el estado, sin tocar Id_Usuarios
        $updateStmt = $conexion->prepare("
            UPDATE tb_incidentes 
            SET Id_Estados_Incidente = :estado 
            WHERE Id_Incidentes = :id
        ");
        $updateStmt->execute([
            ':estado' => $id_estado,
            ':id' => $id_incidente
        ]);
    }

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Estado actualizado correctamente.'
    ]);
} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error en base de datos: ' . $e->getMessage()
    ]);
}
