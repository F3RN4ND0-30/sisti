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
        ti.Id_Incidentes,
        ti.Descripcion,
        ti.Fecha_Creacion,
        ti.Fecha_Resuelto,
        ti.Id_Estados_Incidente,
        te.Nombre AS estado,
        tue.Nombre AS nombre,
        tue.Apellido_Paterno,
        tue.Apellido_Materno,
        tue.DNI,
        ta.Nombre AS area,
        tt.Codigo_Ticket
    FROM tb_incidentes ti
    INNER JOIN tb_tickets tt ON ti.Id_Tickets = tt.Id_Tickets
    LEFT JOIN tb_usuariosExternos tue ON ti.Id_UsuariosExternos = tue.Id_UsuariosExternos
    LEFT JOIN tb_areas ta ON ti.Id_Areas = ta.Id_Areas
    LEFT JOIN tb_estados_incidente te ON ti.Id_Estados_Incidente = te.Id_Estados_Incidente
    WHERE tt.Id_Tickets = :idTicket
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':idTicket' => $ticketId]);
    $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$incidentes) {
        echo json_encode(['success' => false, 'error' => 'Información del ticket no encontrada']);
        exit;
    }

    // Preparar estructura esperada por el frontend
    $ticketData = [
        'numero_ticket' => $incidentes[0]['Codigo_Ticket'] ?? null,
        // Aquí podrías añadir más datos generales del ticket si los tienes
    ];

    $incidentesData = [];

    foreach ($incidentes as $info) {
        $incidentesData[] = [
            'id_incidente' => $info['Id_Incidentes'],
            'descripcion' => $info['Descripcion'],
            'fecha_creacion' => $info['Fecha_Creacion'],
            'fecha_resuelto' => $info['Fecha_Resuelto'],
            'estado' => $info['estado'],
            'dni' => $info['DNI'],
            'nombre' => $info['nombre'],
            'apellido' => trim($info['Apellido_Paterno'] . ' ' . $info['Apellido_Materno']),
            'area' => $info['area'],
            'id_estado_incidente' => (int)$info['Id_Estados_Incidente']
        ];
    }

    echo json_encode([
        'success' => true,
        'ticket' => $ticketData,
        'incidentes' => $incidentesData,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . $e->getMessage()]);
}
