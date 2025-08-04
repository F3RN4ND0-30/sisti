<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (isset($_SESSION['hd_activo']) && $_SESSION['hd_activo'] === true) {
    header('Location: sisvis/escritorio.php');
    exit();
}

require_once '../backend/bd/conexion.php';

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciarSesion'])) {
    $usuarioInput = trim($_POST['correo'] ?? '');
    $claveInput = trim($_POST['clave'] ?? '');

    error_log("DEBUG LOGIN - Usuario ingresado: '$usuarioInput'");
    error_log("DEBUG LOGIN - Clave ingresada: '" . ($claveInput ? '****' : '') . "'");

    if ($usuarioInput === '' || $claveInput === '') {
        $mensajeError = "Debe ingresar usuario y contraseña.";
        error_log("DEBUG LOGIN - Faltan campos.");
    } else {
        try {
            $stmt = $conexion->prepare("SELECT * FROM tb_Usuarios WHERE Usuario = :usuario AND Activo = 1");
            $stmt->execute([':usuario' => $usuarioInput]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("DEBUG LOGIN - Resultado de la consulta: " . print_r($user, true));

            if ($user) {
                $hashBD = $user['Clave'];

                if (password_verify($claveInput, $hashBD)) {
                    // Login exitoso
                    $_SESSION['hd_activo'] = true;
                    $_SESSION['hd_id'] = $user['Id_Usuarios'];
                    $_SESSION['hd_usuario'] = $user['Usuario'];
                    $_SESSION['hd_nombre'] = $user['Nombre'] . ' ' . $user['Apellido_Paterno'];
                    $_SESSION['hd_rol'] = $user['Id_Roles'];

                    header('Location: sisvis/escritorio.php');
                    exit();
                } else {
                    $mensajeError = "Credenciales incorrectas.";
                    error_log("DEBUG LOGIN - Contraseña incorrecta.");
                }
            } else {
                $mensajeError = "Usuario no encontrado o cuenta inactiva.";
                error_log("DEBUG LOGIN - Usuario no encontrado.");
            }
        } catch (PDOException $e) {
            error_log("ERROR LOGIN: " . $e->getMessage());
            $mensajeError = "Error en la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login | HelpDesk</title>
    <link rel="stylesheet" href="../backend/css/login/estilologin.css">
    <link rel="icon" type="image/png" href="../backend/img/logoPisco.png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert@2.1.2"></script>
</head>

<body>
    <div class="contenedor-login">
        <div class="columna-imagen"></div>

        <div class="columna-formulario">
            <div class="caja-formulario">
                <div class="contenido-interno">
                    <div class="cabecera-formulario">
                        <div class="titulo">
                            <img src="../backend/img/logo-helpdesk-pisco.png" class="logo-imagen">
                            <p>Municipalidad Provincial de Pisco</p>
                        </div>

                        <?php if (!empty($mensajeError)): ?>
                            <script>
                                swal("Error de autenticación", "<?= htmlspecialchars($mensajeError) ?>", "error");
                            </script>
                        <?php endif; ?>

                        <form method="post" autocomplete="off">
                            <div class="grupo-formulario tiene-retroalimentacion">
                                <i class="material-icons icono-control-formulario">person</i>
                                <input type="text" name="correo"
                                    value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                    class="control-formulario"
                                    placeholder="Usuario técnico"
                                    required>
                            </div>

                            <div class="grupo-formulario">
                                <i class="material-icons icono-control-formulario">lock</i>
                                <input type="password" name="clave"
                                    class="control-formulario"
                                    placeholder="Contraseña"
                                    required>
                            </div>

                            <div class="acciones">
                                <button class="boton boton-enviar" name="iniciarSesion" type="submit">
                                    ACCEDER AL SISTEMA
                                </button>
                            </div>
                        </form>

                        <div class="informacion-acceso">
                            <p><strong>Acceso solo para personal técnico autorizado</strong></p>
                            <p>Si no tienes cuenta, contacta al administrador del sistema</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>