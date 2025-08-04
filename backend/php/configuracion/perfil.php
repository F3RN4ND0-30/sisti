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
            Fecha_Creacion, 
            Usuario, 
            Clave
        FROM tb_Usuarios 
        WHERE Id_Usuarios = :id
    ");
    $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
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
    <title>Mi Perfil | HelpDesk</title>
    <link rel="stylesheet" href="../css/vistas/escritorio.css">
    <link rel="stylesheet" href="../css/navbar/navbar.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/logoPisco.png" />
    <style>
        .user-role p {
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            opacity: 1;
        }
        .perfil-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .perfil-header { text-align: center; margin-bottom: 30px; }
        .perfil-avatar {
            width: 120px; height: 120px; border-radius: 50%;
            background: #2563eb; color: white; font-size: 50px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; box-shadow: 0 5px 15px rgba(37,99,235,0.3);
        }
        .perfil-header h2 { margin: 10px 0 5px; font-size: 26px; color: #2c3e50; }
        .perfil-header p { color: #6c757d; font-size: 16px; }
        .perfil-item { margin-bottom: 20px; }
        .perfil-item label { font-weight: 600; color: #333; display: block; margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 10px 15px; border: 2px solid #e5e7eb;
            border-radius: 10px; background: #f9fafb; font-size: 15px; color: #555;
        }
        .btn {
            padding: 12px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;
            border: none; transition: 0.3s; display: block; margin: 25px auto 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9); color: white;
        }
        .btn-primary:hover { background: linear-gradient(135deg, #2980b9, #2471a3); }
    </style>
</head>
<body>
    <?php include '../navbar/navbar.php'; ?>

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
                    <?= strtoupper(substr($usuario['Nombre'],0,1)) ?>
                </div>
                <h2><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno']) ?></h2>
                <p><?= htmlspecialchars($usuario['Usuario']) ?></p>
            </div>

            <div class="perfil-item">
                <label>DNI:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['Dni']) ?>" class="form-control" disabled>
            </div>
            <div class="perfil-item">
                <label>Fecha de Creación:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['Fecha_Creacion']) ?>" class="form-control" disabled>
            </div>

            <button class="btn btn-primary">Editar Perfil</button>
        </div>
    </div>
</body>
</html>
