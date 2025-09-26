<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/sisti/backend/bd/conexion.php';

try {
    $stmt = $conexion->query("
        SELECT 
            i.Id_Incidentes,
            t.Codigo_Ticket AS Ticket,
            a.Nombre AS Area,
            u.Nombre AS Tecnico,
            i.Descripcion,
            e.Nombre AS Estado,
            i.Ultima_Modificacion,
            DATE_FORMAT(i.Fecha_Creacion, '%d/%m/%Y %H:%i') AS Fecha_Creacion,
            DATE_FORMAT(i.Fecha_Resuelto, '%d/%m/%Y %H:%i') AS Fecha_Resuelto
        FROM tb_incidentes i
        INNER JOIN tb_tickets t ON i.Id_Tickets = t.Id_Tickets
        INNER JOIN tb_areas a ON i.Id_Areas = a.Id_Areas
        LEFT JOIN tb_usuarios u ON i.Id_Usuarios = u.Id_Usuarios
        INNER JOIN tb_estados_incidente e ON i.Id_Estados_Incidente = e.Id_Estados_Incidente
        ORDER BY i.Id_Incidentes DESC
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
        <table id="tabla-incidente" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ticket</th>
                    <th>Área</th>
                    <th>Técnico</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Última Modificación</th>
                    <th>Fecha de Creación</th>
                    <th>Fecha de Resolución</th>
                    <?php if (strtolower($_SESSION['hd_rol']) === 'administrador'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
        </table>
    </div>
</div>