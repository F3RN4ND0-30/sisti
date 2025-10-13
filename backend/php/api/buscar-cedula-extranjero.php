<?php
require_once '../../bd/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula'])) {
    $cedula = trim($_POST['cedula']);

    try {
        $stmt = $conexion->prepare("SELECT nombres, ap_paterno, ap_materno FROM tb_Extranjeros WHERE cedula = :cedula AND estado = 1");
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($persona) {
            echo json_encode([
                'success' => true,
                'nombre' => $persona['nombres'],
                'apPaterno' => $persona['ap_paterno'],
                'apMaterno' => $persona['ap_materno']
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos.']);
}
