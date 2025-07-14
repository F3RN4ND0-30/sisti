<?php
// NOTA: session_start() ya se ejecutó en login.php
require_once '../backend/bd/conexion.php';

if (isset($_POST['iniciarSesion'])) {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (empty($correo) || empty($clave)) {
        $mensajeError = "Por favor complete todos los campos";
    } else {
        // Login temporal
        if ($correo == 'admin@mpp.com' && $clave == 'admin123') {
            // VARIABLES ÚNICAS CON PREFIJO hd_
            $_SESSION['hd_id'] = 1;
            $_SESSION['hd_nombres'] = 'Administrador';
            $_SESSION['hd_correo'] = $correo;
            $_SESSION['hd_rol'] = 'administrador';
            $_SESSION['hd_activo'] = true;

            header('Location: sisvis/escritorio.php');
            exit();
        } else {
            $mensajeError = "Credenciales incorrectas";
        }
    }
}
