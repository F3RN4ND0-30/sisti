<?php
session_name('HELPDESK_SISTEMA');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

try {
    $sql = "SELECT
            f.Id_Ficha,
            f.Numero,
            u.Nombre,
            u.Apellido_Paterno,
            u.Apellido_Materno,
            f.Fecha
            FROM tb_ficha_control f
            JOIN tb_Usuarios u ON f.Id_Usuarios = u.Id_Usuarios
            ORDER BY f.Id_Ficha DESC;";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($fichas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener fichas']);
}
