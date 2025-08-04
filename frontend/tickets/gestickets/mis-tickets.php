<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_id'])) {
    header('Location: /sisti/frontend/login.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/php/desk/obtener_mis_incidentes.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis Tickets Asignados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS principal -->
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css" />
    <link rel="stylesheet" href="../../../backend/css/tickets/mis-tickets.css" />

    <!-- Material Icons (Google) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Favicon opcional -->
    <link rel="icon" type="image/png" href="/sisti/assets/favicon.png">
</head>

<body>

    <?php include '../../navbar/navbar.php'; ?>

    <main class="main-content">
        <div class="activity-card">
            <h4><i class="material-icons">assignment_ind</i> Mis Incidentes Asignados</h4>
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ticket</th>
                            <th>Área</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha de Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidentes as $row): ?>
                            <?php $estado = strtolower(trim($row['Estado'])); ?>
                            <tr>
                                <td><?= $row['Id_Incidentes'] ?></td>
                                <td><?= $row['Ticket'] ?></td>
                                <td><?= $row['Area'] ?></td>
                                <td><?= $row['Descripcion'] ?></td>
                                <td style="text-align: center;">
                                    <select class="estado-select <?= $estado ?>" onchange="actualizarEstado(this, <?= $row['Id_Incidentes'] ?>)">
                                        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="proceso" <?= $estado === 'en proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="resuelto" <?= $estado === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                    </select>
                                </td>
                                <td><?= $row['Fecha_Creacion'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- JS para manejar cambio de estado -->
    <script src="/sisti/backend/js/desk/actualizarEstado.js"></script>
</body>

</html>