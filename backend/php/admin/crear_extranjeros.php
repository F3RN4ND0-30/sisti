<?php
require_once '../../bd/conexion.php';

header('Content-Type: application/json');

$cedula     = $_POST['cedula']     ?? '';
$nombres    = $_POST['nombres']    ?? '';
$ap_paterno = $_POST['ap_paterno'] ?? '';
$ap_materno = $_POST['ap_materno'] ?? '';
$estado     = $_POST['estado']     ?? '';

if (empty($cedula) || empty($nombres) || empty($ap_paterno) || empty($ap_materno) || $estado === '') {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios.']);
    exit;
}

try {
    $stmt = $conexion->prepare("INSERT INTO tb_extranjeros (cedula, nombres, ap_paterno, ap_materno, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$cedula, $nombres, $ap_paterno, $ap_materno, $estado]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}
