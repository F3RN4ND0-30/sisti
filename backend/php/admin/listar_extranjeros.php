<?php
require_once '../../bd/conexion.php';

header('Content-Type: application/json');

try {
    $stmt = $conexion->prepare("SELECT id, cedula, nombres, ap_paterno, ap_materno, estado FROM tb_extranjeros ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll();

    echo json_encode(['data' => $data]);
} catch (Exception $e) {
    echo json_encode([
        'data' => [],
        'error' => 'Error al obtener los registros: ' . $e->getMessage()
    ]);
}
