<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

header('Content-Type: application/json');

try {
    $stmt = $conexion->query("
        SELECT 
            i.Id_Incidentes,
            t.Codigo_Ticket AS Ticket,
            a.Nombre AS Area,
            i.Descripcion,
            e.Nombre AS Estado,
            DATE_FORMAT(i.Fecha_Creacion, '%d/%m/%Y %H:%i') AS Fecha_Creacion,
            DATE_FORMAT(i.Fecha_Resuelto, '%d/%m/%Y %H:%i') AS Fecha_Resuelto
        FROM tb_incidentes i
        INNER JOIN tb_tickets t ON i.Id_Tickets = t.Id_Tickets
        INNER JOIN tb_areas a ON i.Id_Areas = a.Id_Areas
        INNER JOIN tb_estados_incidente e ON i.Id_Estados_Incidente = e.Id_Estados_Incidente
        ORDER BY i.Id_Incidentes DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
