<?php
session_name('HELPDESK_SISTEMA');
session_start();

header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_incidente = $data['id_incidente'] ?? null;
$id_tecnico = $data['id_tecnico'] ?? null;

if (!$id_incidente || !$id_tecnico) {
    echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
    exit;
}

try {
    // ✅ Verificar el estado actual del incidente
    $stmt = $conexion->prepare("
        SELECT ei.Nombre AS estado_actual
        FROM tb_incidentes i
        INNER JOIN tb_estados_incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
        WHERE i.Id_Incidentes = :id_incidente
    ");
    $stmt->execute([':id_incidente' => $id_incidente]);
    $incidente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incidente) {
        echo json_encode(['exito' => false, 'mensaje' => 'Incidente no encontrado.']);
        exit;
    }

    $estado_actual = strtolower(trim($incidente['estado_actual']));
    if ($estado_actual === 'resuelto') {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'No se puede asignar un técnico. El incidente ya fue resuelto.'
        ]);
        exit;
    }

    // ✅ Asignar técnico si no está resuelto
    $stmt = $conexion->prepare("
        UPDATE tb_incidentes 
        SET Id_Usuarios = :id_tecnico 
        WHERE Id_Incidentes = :id_incidente
    ");
    $stmt->execute([
        ':id_tecnico' => $id_tecnico,
        ':id_incidente' => $id_incidente
    ]);

    echo json_encode(['exito' => true, 'mensaje' => 'Técnico asignado correctamente.']);
} catch (PDOException $e) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error en base de datos: ' . $e->getMessage()
    ]);
}
