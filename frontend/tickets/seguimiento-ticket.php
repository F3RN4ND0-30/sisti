<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Ticket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="../../backend/css/tickets/seguimiento-ticket.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
</head>

<body>
    <div class="seguimiento-container">
        <h2>Seguimiento de Ticket</h2>

        <form id="formBuscarTicket">
            <input type="text" name="ticket" id="ticketInput" placeholder="Ingrese N° de Ticket (ej. TCK-...)" required />
            <button type="button" id="btnPegar">Pegar</button>
            <button type="submit">Buscar</button>
        </form>

        <div id="resultado" style="margin-top: 2rem;"></div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button id="btnRegresar">Regresar a inicio</button>
        </div>
    </div>

    <script src="../../backend/js/tickets/seguimiento-ticket.js"></script>
</body>

</html>