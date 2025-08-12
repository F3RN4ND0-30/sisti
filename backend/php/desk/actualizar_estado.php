<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

$id_usuario_actual = $_SESSION['hd_id'] ?? null;

$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_raw = $data['nuevo_estado'] ?? '';

$nuevo_estado_nombre = strtolower(trim(preg_replace('/\s+/', ' ', $nuevo_estado_raw)));

$estadoMap = [
    'pendiente'   => 'pendiente',
    'en proceso'  => 'en proceso',
    'en_proceso'  => 'en proceso',
    'proceso'     => 'en proceso',
    'resuelto'    => 'resuelto'
];

$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

if (!isset($id_incidente) || $id_incidente === '' || !$nuevo_estado_nombre || !$id_usuario_actual) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos o sesión no válida.'
    ]);
    exit;
}

try {
    // Obtener estado y usuario actual del incidente
    $stmt = $conexion->prepare("
        SELECT Id_Usuarios, Id_Estados_Incidente, Fecha_Resuelto 
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

    $id_estado_actual = $incidente['Id_Estados_Incidente'];
    $id_usuario_asignado = $incidente['Id_Usuarios'];
    $fecha_resuelto = $incidente['Fecha_Resuelto'];

    // No permitir cambiar si ya está resuelto
    $stmtEstadoActual = $conexion->prepare("
        SELECT LOWER(Nombre) as Nombre FROM tb_estados_incidente WHERE Id_Estados_Incidente = :id
    ");
    $stmtEstadoActual->execute([':id' => $id_estado_actual]);
    $estado_actual = $stmtEstadoActual->fetch(PDO::FETCH_ASSOC)['Nombre'] ?? '';

    if ($estado_actual === 'resuelto') {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'El incidente ya está resuelto y no se puede modificar.'
        ]);
        exit;
    }

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

    // Preparar campos para actualización
    $campos_update = "Id_Estados_Incidente = :estado";
    $params_update = [
        ':estado' => $id_estado,
        ':id' => $id_incidente
    ];

    // Si el nuevo estado es "resuelto", registrar la fecha/hora actual usando NOW()
    if ($nuevo_estado_nombre === 'resuelto') {
        $campos_update .= ", Fecha_Resuelto = NOW()"; // Cambiado GETDATE() por NOW()
    }

    // Si no tiene usuario asignado, asignar el actual
    if ($id_usuario_asignado === null) {
        $campos_update .= ", Id_Usuarios = :usuario";
        $params_update[':usuario'] = $id_usuario_actual;
    }

    // Ejecutar actualización
    $updateStmt = $conexion->prepare("
        UPDATE tb_incidentes 
        SET $campos_update 
        WHERE Id_Incidentes = :id
    ");
    $updateStmt->execute($params_update);

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
