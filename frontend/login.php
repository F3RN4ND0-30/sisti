<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (isset($_SESSION['hd_activo']) && $_SESSION['hd_activo'] === true) {
    header('Location:/sisvis/escritorio.php');
    exit();
}

$mensajeError = $_SESSION['hd_error'] ?? '';
$debugData = $_SESSION['hd_debug'] ?? null;

// Limpiar errores de sesión
unset($_SESSION['hd_error']);
unset($_SESSION['hd_debug']);

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
                                <?php if ($debugData): ?>
                                    console.log("DEBUG LOGIN:");
                                    console.log("Usuario:", <?= json_encode($debugData['usuario'] ?? '') ?>);
                                    console.log("Clave:", <?= json_encode($debugData['clave'] ?? '') ?>);
                                    console.log("Resultado:", <?= json_encode($debugData['resultado'] ?? null) ?>);
                                <?php endif; ?>
                            </script>
                        <?php endif; ?>

                        <form method="post" action="/sisti/backend/php/autenticacion.php" autocomplete="off">
                            <div class="grupo-formulario tiene-retroalimentacion">
                                <i class="material-icons icono-control-formulario">person</i>
                                <input type="text" name="usuario"
                                    value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
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