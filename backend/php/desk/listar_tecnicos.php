<?php
session_name('HELPDESK_SISTEMA');
session_start();

header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

try {
    // Obtenemos todos los usuarios que tienen el rol "técnico" (Id_Roles = 2)
    $stmt = $conexion->prepare("
        SELECT 
            u.Id_Usuarios AS id, 
            u.Nombre AS nombre
        FROM tb_usuarios u
        INNER JOIN tb_roles r ON u.Id_Roles = r.Id_Roles
        WHERE u.Id_Roles = 2 AND u.Activo = 1
    ");
    $stmt->execute();

    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tecnicos);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al obtener técnicos: ' . $e->getMessage()
    ]);
}
