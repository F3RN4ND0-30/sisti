<?php
header('Content-Type: application/json');
require_once '../../bd/conexion.php';

$dni = $_POST['dni'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$area = $_POST['area'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$apPaterno = $_POST['apPaterno'] ?? '';
$apMaterno = $_POST['apMaterno'] ?? '';

// Validación básica
if (!$dni || !$nombre || !$apPaterno || !$apMaterno || !$area || !$descripcion) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

// Generar código único de ticket
$fechaActual = date('Ymd');
$random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
$nuevoCodigo = "TCK-$fechaActual-$random";

// Insertar en la base de datos
$sql = "INSERT INTO tb_Tickets(codigo_ticket, dni, nombre, apellido_paterno, apellido_materno, area, descripcion, fecha_registro)
        VALUES (:codigo, :dni, :nombre, :apPaterno, :apMaterno, :area, :descripcion, GETDATE())";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':codigo' => $nuevoCodigo,
        ':dni' => $dni,
        ':nombre' => $nombre,
        ':apPaterno' => $apPaterno,
        ':apMaterno' => $apMaterno,
        ':area' => $area,
        ':descripcion' => $descripcion
    ]);

    echo json_encode(['success' => true, 'ticket' => $nuevoCodigo]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar en BD: ' . $e->getMessage()]);
}
exit;
