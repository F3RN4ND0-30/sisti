<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../../login.php');
    exit();
}

require_once '../../../backend/bd/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Ticket</title>

    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="../../../backend/css/tickets/seguimiento-ticket.css">
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>
    <div class="seguimiento-container">
        <h2>Seguimiento de Ticket</h2>

        <form id="formBuscarTicket">
            <input type="text" name="ticket" id="ticketInput" placeholder="Ingrese N° de Ticket (ej. TCK-...)" required />
            <button type="submit">Buscar</button>
        </form>

        <div id="resultado" style="margin-top: 2rem;"></div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button id="btnRegresarFrontend">Regresar a inicio</button>
        </div>
    </div>

    <script src="../../../backend/js/tickets/seguimiento-ticket.js?=123"></script>
    
</body>

</html>