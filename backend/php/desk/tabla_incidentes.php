<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sishelpdesk/backend/bd/conexion.php';

try {
    $stmt = $conexion->query("
    SELECT 
        i.Id_Incidentes,
        t.Codigo_Ticket AS Ticket,
        a.Nombre AS Area,
        i.Descripcion,
        e.Nombre AS Estado,
        FORMAT(i.Fecha_Creacion, 'dd/MM/yyyy HH:mm') AS Fecha_Creacion
    FROM tb_incidentes i
    INNER JOIN tb_tickets t ON i.Id_Tickets = t.Id_Tickets
    INNER JOIN tb_areas a ON i.Id_Areas = a.Id_Areas
    INNER JOIN tb_estados_incidente e ON i.Id_Estados_Incidente = e.Id_Estados_Incidente
    ORDER BY i.Fecha_Creacion DESC
");

    $incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error al obtener incidentes: " . $e->getMessage() . "</p>";
    exit;
}
?>

<!-- Tabla de Incidentes -->
<div class="activity-card">
    <h4><i class="material-icons">list</i> Lista de Incidentes</h4>
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
                    <tr>
                        <td><?= $row['Id_Incidentes'] ?></td>
                        <td><?= $row['Ticket'] ?></td>
                        <td><?= $row['Area'] ?></td>
                        <td><?= $row['Descripcion'] ?></td>
                        <td style="text-align: center;">
                            <?php
                            $estado = strtolower($row['Estado']);
                            if ($estado === 'pendiente') {
                                echo '<span class="estado-tag estado-pendiente">Pendiente</span>';
                            } elseif ($estado === 'en proceso') {
                                echo '<span class="estado-tag estado-proceso">En Proceso</span>';
                            } elseif ($estado === 'resuelto') {
                                echo '<span class="estado-tag estado-resuelto">Resuelto</span>';
                            } else {
                                echo '<span class="estado-tag">' . htmlspecialchars($row['Estado']) . '</span>';
                            }
                            ?>
                        </td>
                        <td><?= $row['Fecha_Creacion'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>