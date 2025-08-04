<?php
// NOTA: session_start() ya se ejecutó en login.php
require_once '../backend/bd/conexion.php';

if (isset($_POST['iniciarSesion'])) {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (empty($correo) || empty($clave)) {
        $mensajeError = "Por favor complete todos los campos";
    } else {
        try {
            // Buscar usuario activo con el campo Usuario (o correo si usas otro campo)
            $stmt = $conexion->prepare("SELECT Id_Usuarios, Usuario, Clave, Nombre, Apellido_Paterno, Apellido_Materno, Id_Roles FROM tb_Usuarios WHERE Usuario = :usuario AND Activo = 1");
            $stmt->execute([':usuario' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($clave, $usuario['Clave'])) {
                // Login exitoso: guardar datos en sesión
                $_SESSION['hd_id'] = $usuario['Id_Usuarios'];
                $_SESSION['hd_usuario'] = $usuario['Usuario'];
                $_SESSION['hd_nombres'] = $usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno'];
                $_SESSION['hd_rol'] = $usuario['Id_Roles']; // si quieres puedes mapearlo a texto
                $_SESSION['hd_activo'] = true;

                header('Location: sisvis/escritorio.php');
                exit();
            } else {
                $mensajeError = "Credenciales incorrectas";
            }
        } catch (PDOException $e) {
            $mensajeError = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
