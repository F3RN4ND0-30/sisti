<?php
// Iniciar sesión
session_name('HELPDESK_SISTEMA');
session_start();
if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../login.php');
    exit();
}
require_once '../../bd/conexion.php';

try {
    // Obtener datos del usuario logueado
    $stmt = $conexion->prepare("
        SELECT 
            Id_Usuarios, 
            Dni, 
            Nombre, 
            Apellido_Paterno, 
            Apellido_Materno, 
            Id_Areas, 
            Id_Roles, 
            Activo, 
            CONVERT(DATE, Fecha_Creacion) AS Fecha_Creacion, 
            Usuario, 
            Clave
        FROM tb_Usuarios 
        WHERE Id_Usuarios = :id
    ");
    $stmt->bindParam(':id', $_SESSION['hd_id'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Error: Usuario no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al cargar los datos del usuario: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mi Perfil | HelpDesk</title>

    <!-- 🔥 IMPORTANTE: Navbar CSS primero -->
    <link rel="stylesheet" href="../../css/navbar/navbar.css">

    <!-- CSS del módulo DESPUÉS del navbar -->
    <link rel="stylesheet" href="../../css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../css/configuracion/perfil.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />
</head>

<body>
    <!-- 🔥 INCLUIR NAVBAR (que ya tiene el wrapper) -->
    <?php include '../../../frontend/navbar/navbar.php'; ?>

    <!-- 🔥 CONTENIDO DENTRO DEL LAYOUT DEL NAVBAR -->
    <div class="main-content">
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>Mi perfil</h2>
                    <p class="mb-0">Información personal del usuario</p>
                    <small>Municipalidad Provincial de Pisco</small>
                </div>
                <div class="col-md-4 text-right">
                    <div class="user-role">
                        <h5>Usuario</h5>
                        <p><?= htmlspecialchars($usuario['Usuario']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="perfil-container">
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <?= strtoupper(substr($usuario['Nombre'], 0, 1)) ?>
                </div>
                <h2><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno']) ?></h2>
                <p><?= htmlspecialchars($usuario['Usuario']) ?></p>
            </div>

            <div class="perfil-datos">
                <div class="perfil-row">
                    <div class="perfil-item">
                        <label>Nombre de Usuario:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Nombre']) ?>" class="form-control" disabled>
                    </div>
                    <div class="perfil-item">
                        <label>Apellido Paterno:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Apellido_Paterno']) ?>" class="form-control" disabled>
                    </div>
                </div>

                <div class="perfil-row">
                    <div class="perfil-item">
                        <label>Apellido Materno:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Apellido_Materno']) ?>" class="form-control" disabled>
                    </div>
                    <div class="perfil-item">
                        <label>Usuario:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Usuario']) ?>" class="form-control" disabled>
                    </div>
                </div>

                <div class="perfil-row">
                    <div class="perfil-item">
                        <label>DNI:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Dni']) ?>" class="form-control" disabled>
                    </div>
                    <div class="perfil-item">
                        <label>Fecha de Creación:</label>
                        <input type="text" value="<?= htmlspecialchars($usuario['Fecha_Creacion']) ?>" class="form-control" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>