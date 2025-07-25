<?php
header('Content-Type: application/json');
require_once '../../bd/conexion.php';

if (!isset($_GET['ticket'])) {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó el ticket']);
    exit;
}

$codigoTicket = $_GET['ticket'];

try {
    // Obtener el ID del ticket desde tb_Tickets
    $stmt = $conexion->prepare("SELECT Id_Tickets FROM tb_Tickets WHERE Codigo_Ticket = :codigo");
    $stmt->execute([':codigo' => $codigoTicket]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        echo json_encode(['success' => false, 'error' => 'Ticket no encontrado']);
        exit;
    }

    $ticketId = $ticket['Id_Tickets'];

    // Traer información del incidente
    $sql = "
        SELECT 
            i.Id_Incidentes,
            t.Codigo_Ticket AS numero_ticket,
            u.Dni,
            u.Nombre,
            u.Apellido_Paterno,
            u.Apellido_Materno,
            a.Nombre AS area,
            i.Descripcion,
            i.Id_Estados_Incidente
        FROM tb_Incidentes i
        INNER JOIN tb_Tickets t ON i.Id_Tickets = t.Id_Tickets
        INNER JOIN tb_UsuariosExternos u ON i.Id_UsuariosExternos = u.Id_UsuariosExternos
        INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
        WHERE i.Id_Tickets = :idTicket
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':idTicket' => $ticketId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['success' => false, 'error' => 'Información del ticket no encontrada']);
        exit;
    }

    // Preparar estructura esperada por el frontend
    $ticketData = [
        'numero_ticket' => $info['numero_ticket'],
        'dni' => $info['Dni'],
        'nombre' => $info['Nombre'],
        'apellido' => $info['Apellido_Paterno'] . ' ' . $info['Apellido_Materno'],
        'area' => $info['area'],
        'descripcion' => $info['Descripcion'],
        'estado' => (int)$info['Id_Estados_Incidente']
    ];

    echo json_encode([
        'success' => true,
        'ticket' => $ticketData,
        'seguimiento' => []  // Por ahora vacío, puedes agregar historial más adelante
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . $e->getMessage()]);
}
