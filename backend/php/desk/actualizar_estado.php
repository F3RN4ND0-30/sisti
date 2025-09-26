<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

// ✅ Capturamos el ID del usuario y su nombre completo con apellido principal
$id_usuario_actual = $_SESSION['hd_id'] ?? null;
$nombre_usuario = $_SESSION['hd_nombre'] ?? null; 

// 📦 Datos recibidos desde el frontend
$data = json_decode(file_get_contents('php://input'), true);
$id_incidente = $data['id_incidente'] ?? null;
$nuevo_estado_raw = $data['nuevo_estado'] ?? '';

// ✨ Normalizamos el estado
$nuevo_estado_nombre = strtolower(trim(preg_replace('/\s+/', ' ', $nuevo_estado_raw)));

$estadoMap = [
    'pendiente'   => 'pendiente',
    'en proceso'  => 'en proceso',
    'en_proceso'  => 'en proceso',
    'proceso'     => 'en proceso',
    'resuelto'    => 'resuelto'
];
$nuevo_estado_nombre = $estadoMap[$nuevo_estado_nombre] ?? null;

// 🚨 Validación de datos
if (!$id_incidente || !$nuevo_estado_nombre || !$id_usuario_actual || !$nombre_usuario) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos o sesión no válida.'
    ]);
    exit;
}

try {
    // 1️⃣ Verificar si el incidente existe
    $stmt = $conexion->prepare("
        SELECT Id_Estados_Incidente 
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

    // 2️⃣ Obtener ID del nuevo estado
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

    // 3️⃣ Preparar actualización con el nombre completo del usuario
    $campos_update = "Id_Estados_Incidente = :estado, Ultima_Modificacion = :usuario";
    $params_update = [
        ':estado' => $id_estado,
        ':usuario' => $nombre_usuario, // ✅ Guardamos nombre + apellido
        ':id' => $id_incidente
    ];

    // 4️⃣ Si el estado es "resuelto", guardamos la fecha
    if ($nuevo_estado_nombre === 'resuelto') {
        $campos_update .= ", Fecha_Resuelto = NOW()";
    }

    // 5️⃣ Ejecutar actualización
    $updateStmt = $conexion->prepare("
        UPDATE tb_incidentes 
        SET $campos_update 
        WHERE Id_Incidentes = :id
    ");
    $updateStmt->execute($params_update);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Estado actualizado y última modificación registrada correctamente.'
    ]);
} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error en base de datos: ' . $e->getMessage()
    ]);
}
