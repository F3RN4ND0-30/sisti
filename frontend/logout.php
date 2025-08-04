<?php
session_name('HELPDESK_SISTEMA');
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
