<?php
session_name('HELPDESK_SISTEMA');
session_start();

// Verificar autenticación
if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit();
}

require_once '../../bd/conexion.php';

try {
    $estadoId = isset($_POST['estado_id']) ? trim($_POST['estado_id']) : '';

    // Construir consulta base SIN punto y coma
    $sql = "
        SELECT 
            t.Codigo_Ticket,
            COALESCE(
                CONCAT(ue.Nombre, ' ', ue.Apellido_Paterno, ' ', IFNULL(ue.Apellido_Materno, '')),
                CONCAT(u.Nombre, ' ', u.Apellido_Paterno, ' ', IFNULL(u.Apellido_Materno, '')),
                'Usuario no encontrado'
            ) AS NombreCompleto,
            a.Nombre AS AreaNombre,
            i.Descripcion,
            ei.Nombre AS EstadoNombre,
            ei.Id_Estados_Incidente AS EstadoId,
            i.Fecha_Creacion,
            i.Id_Incidentes
        FROM tb_Incidentes i
        INNER JOIN tb_Tickets t ON i.Id_Tickets = t.Id_Tickets
        LEFT JOIN tb_UsuariosExternos ue ON i.Id_UsuariosExternos = ue.Id_UsuariosExternos
        LEFT JOIN tb_Usuarios u ON i.Id_Usuarios = u.Id_Usuarios
        INNER JOIN tb_Areas a ON i.Id_Areas = a.Id_Areas
        INNER JOIN tb_Estados_Incidente ei ON i.Id_Estados_Incidente = ei.Id_Estados_Incidente
    ";

    // Agregar filtro si se especifica estado
    if (!empty($estadoId)) {
        $sql .= " WHERE ei.Id_Estados_Incidente = :estado_id";
    }

    // Agregar ordenamiento
    $sql .= " ORDER BY i.Fecha_Creacion DESC";

    $stmt = $conexion->prepare($sql);

    if (!empty($estadoId)) {
        $stmt->bindParam(':estado_id', $estadoId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'tickets' => $tickets,
        'total' => count($tickets),
        'estado_filtrado' => $estadoId
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
