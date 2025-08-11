<?php
require_once __DIR__ . '/../bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $estado = $_POST['estado'] ?? null;

    if ($id && $estado) {
        try {
            // Obtener ID del estado
            $stmt = $conexion->prepare("SELECT Id_Estados_Incidente FROM tb_Estados_Incidente WHERE Nombre = :estado");
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                echo json_encode(['success' => false, 'error' => 'Estado inválido']);
                exit;
            }

            $estadoId = $row['Id_Estados_Incidente'];

            // Actualizar incidente
            $stmt = $conexion->prepare("UPDATE tb_Incidentes SET Id_Estados_Incidente = :estadoId WHERE Id_Incidentes = :id");
            $stmt->bindParam(':estadoId', $estadoId);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
}
