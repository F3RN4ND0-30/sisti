<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

$usuarioId = $_SESSION['hd_id'] ?? null;

if (!$usuarioId) {
    $incidentes = [];
    return;
}

try {
    $stmt = $conexion->prepare("
    SELECT 
        i.Id_Incidentes,
        t.Codigo_Ticket AS Ticket,
        a.Nombre AS Area,
        i.Descripcion,
        e.Nombre AS Estado,
        DATE_FORMAT(i.Fecha_Creacion, '%d/%m/%Y %H:%i') AS Fecha_Creacion
    FROM tb_incidentes i
    INNER JOIN tb_tickets t ON i.Id_Tickets = t.Id_Tickets
    INNER JOIN tb_areas a ON i.Id_Areas = a.Id_Areas
    INNER JOIN tb_estados_incidente e ON i.Id_Estados_Incidente = e.Id_Estados_Incidente
    WHERE i.Id_Usuarios = :id_usuario
    ORDER BY i.Id_Incidentes DESC
");
    $stmt->execute([':id_usuario' => $usuarioId]);
    $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $incidentes = [];
}
