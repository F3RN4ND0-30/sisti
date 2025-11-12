<?php
require_once '../../bd/conexion.php';

header('Content-Type: application/json');

try {
    $stmt = $conexion->prepare("
    SELECT 
        i.Id_Incidentes,
        t.Codigo_Ticket AS Id_Tickets,
        i.Descripcion,
        i.Fecha_Creacion,
        i.EstadoIncidente
    FROM tb_incidentes i
    INNER JOIN tb_tickets t ON i.Id_Tickets = t.Id_Tickets
    ORDER BY i.Id_Incidentes DESC
");
    $stmt->execute();
    $data = $stmt->fetchAll();

    echo json_encode(['data' => $data]);
} catch (Exception $e) {
    echo json_encode([
        'data' => [],
        'error' => 'Error al obtener los registros: ' . $e->getMessage()
    ]);
}
