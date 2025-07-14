<?php
session_name('HELPDESK_SISTEMA');
session_start();

// Solo redirigir si hay sesión activa
if (isset($_SESSION['hd_activo']) && $_SESSION['hd_activo'] === true) {
    header('Location: sisvis/escritorio.php');
    exit();
}

include_once '../backend/php/autenticacion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk | MPP</title>

    <script src="../backend/js/sweetalert.js"></script>
    <link rel="stylesheet" type="text/css" href="../backend/css/material.css">
    <link rel="stylesheet" href="../backend/css/login/estilologin.css">
    <link rel="icon" type="image/png" href="../backend/img/logoPisco.png" />
</head>

<body>
    <div class="contenedor-login">

        <div class="columna-imagen">
            <!-- Solo animación de fondo -->
        </div>

        <div class="columna-formulario">
            <div class="caja-formulario">
                <div class="contenido-interno">
                    <div class="cabecera-formulario">
                        <div class="titulo">
                            <img src="../backend/img/logo-helpdesk-pisco.png" class="logo-imagen">
                            <h2></h2>
                            <p>Municipalidad Provincial de Pisco</p>
                        </div>

                        <?php
                        if (isset($mensajeError)) {
                            echo '
                                <script type="text/javascript">
                                swal("Error de autenticación", "' . $mensajeError . '", "error");
                                </script>';
                        }
                        ?>

                        <form autocomplete="off" method="post" role="form">

                            <div class="grupo-formulario tiene-retroalimentacion">
                                <i class="material-icons icono-control-formulario">person</i>
                                <input type="text" id="correo" name="correo"
                                    value="<?php if (isset($_POST['correo'])) echo $_POST['correo'] ?>"
                                    class="control-formulario"
                                    placeholder="Correo electrónico del técnico"
                                    required>
                                <span id="error-correo" style="color: #e74c3c;"></span>
                            </div>

                            <div class="grupo-formulario">
                                <i class="material-icons icono-control-formulario">lock</i>
                                <input type="password" id="clave" name="clave"
                                    class="control-formulario"
                                    placeholder="Contraseña"
                                    required>
                                <span id="error-clave" style="color: #e74c3c;"></span>
                            </div>

                            <div class="acciones">
                                <button class="boton boton-enviar" name='iniciarSesion' type="submit">
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

    <script src="../backend/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="../backend/js/validacion-login.js"></script>

</body>

</html>