<?php
session_name('HELPDESK_SISTEMA');
session_start();

// Destruir solo variables HelpDesk
unset($_SESSION['hd_id']);
unset($_SESSION['hd_nombres']);
unset($_SESSION['hd_correo']);
unset($_SESSION['hd_rol']);
unset($_SESSION['hd_activo']);

header('Location: login.php');
exit();
