<?php
header('Content-Type: application/json');
require_once '../../bd/conexion.php';

$dni = $_POST['dni'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apPaterno = $_POST['apPaterno'] ?? '';
$apMaterno = $_POST['apMaterno'] ?? '';
$area = $_POST['area'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

// Validación básica
if (!$dni || !$nombre || !$apPaterno || !$apMaterno || !$area || !$descripcion) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

try {
    $conexion->beginTransaction();

    // Verificar si el usuario externo ya existe
    $stmt = $conexion->prepare("SELECT Id_UsuariosExternos FROM tb_UsuariosExternos WHERE Dni = :dni");
    $stmt->execute([':dni' => $dni]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Insertar nuevo usuario externo
        $stmtUsuario = $conexion->prepare("
            INSERT INTO tb_UsuariosExternos (Dni, Nombre, Apellido_Paterno, Apellido_Materno)
            VALUES (:dni, :nombre, :apPaterno, :apMaterno)
        ");
        $stmtUsuario->execute([
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apPaterno' => $apPaterno,
            ':apMaterno' => $apMaterno
        ]);
        $usuarioId = $conexion->lastInsertId();
    } else {
        $usuarioId = $usuario['Id_UsuariosExternos'];
    }

    // Generar código único de ticket
    $fechaActual = date('Ymd');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $nuevoCodigo = "TCK-$fechaActual-$random";

    // Insertar en tb_Tickets
    $stmtTicket = $conexion->prepare("INSERT INTO tb_Tickets (Codigo_Ticket) VALUES (:codigo)");
    $stmtTicket->execute([':codigo' => $nuevoCodigo]);
    $ticketId = $conexion->lastInsertId();

    // Obtener IP del cliente
    function obtenerIPCliente()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    $ip_pc = obtenerIPCliente();

    // Insertar en tb_Incidentes con IP
    $stmtIncidente = $conexion->prepare("
        INSERT INTO tb_Incidentes (
            Id_Tickets, Id_Usuarios, Id_UsuariosExternos, Id_Areas, Descripcion, Id_Estados_Incidente, Fecha_Creacion, Ip_PC
        ) VALUES (
            :idTicket, NULL, :idUsuarioExterno, :idArea, :descripcion, :estado, GETDATE(), :ip_pc
        )
        ");
    $stmtIncidente->execute([
        ':idTicket' => $ticketId,
        ':idUsuarioExterno' => $usuarioId,
        ':idArea' => $area,
        ':descripcion' => $descripcion,
        ':estado' => 1,
        ':ip_pc' => $ip_pc
    ]);

    $conexion->commit();

    echo json_encode(['success' => true, 'ticket' => $nuevoCodigo]);
} catch (PDOException $e) {
    $conexion->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al registrar el ticket: ' . $e->getMessage()]);
}
exit;
