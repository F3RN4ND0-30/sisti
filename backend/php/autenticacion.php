<?php
session_name('HELPDESK_SISTEMA');
session_start();

require_once '../bd/conexion.php';

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciarSesion'])) {
    $usuarioInput = trim($_POST['usuario'] ?? '');
    $claveInput = trim($_POST['clave'] ?? '');

    if ($usuarioInput === '' || $claveInput === '') {
        $mensajeError = "Debe ingresar usuario y contraseña.";
    } else {
        try {
            $stmt = $conexion->prepare("
    SELECT u.*, r.Nombre AS NombreRol
    FROM tb_Usuarios u
    JOIN tb_Roles r ON u.Id_Roles = r.Id_Roles
    WHERE u.Usuario = :usuario AND u.Activo = 1
");
            $stmt->execute([':usuario' => $usuarioInput]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($claveInput, $user['Clave'])) {
                $_SESSION['hd_activo'] = true;
                $_SESSION['hd_id'] = $user['Id_Usuaraios'];
                $_SESSION['hd_usuario'] = $user['Usuario'];
                $_SESSION['hd_nombre'] = $user['Nombre'] . ' ' . $user['Apellido_Paterno'];
                $_SESSION['hd_rol'] = strtolower($user['NombreRol']);

                header('Location:/sisti/frontend/sisvis/escritorio.php');
                exit();
            } else {
                $mensajeError = "Credenciales incorrectas o usuario inactivo.";
            }
        } catch (PDOException $e) {
            $mensajeError = "Error en la base de datos.";
        }
    }
}

// Si llegamos aquí es porque hubo un error
$_SESSION['hd_error'] = $mensajeError;
header('Location:/sisti/frontend/login.php');
exit();
