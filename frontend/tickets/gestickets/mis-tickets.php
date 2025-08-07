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
    <title>Mis Tickets | HelpDesk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS principal -->
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css" />
    <link rel="stylesheet" href="../../../backend/css/tickets/mis-tickets.css" />

    <!-- Material Icons (Google) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />

    <!-- Favicon opcional -->
    <link rel="icon" type="image/png" href="/sisti/assets/favicon.png">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
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
    <!-- Scripts al final -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
</body>

</html>