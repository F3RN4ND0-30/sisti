<?php
require_once __DIR__ . '/../bd/conexion.php';

header('Content-Type: text/plain'); // Puedes cambiar a application/json si prefieres

try {
    // COALESCE es compatible con MariaDB/MySQL (ISNULL no lo es para funciones)
    $stmt = $conexion->query("SELECT COALESCE(MAX(Numero), 0) + 1 AS nuevo_num FROM tb_ficha_control");

    $numeroNuevo = (int) $stmt->fetchColumn();

    $numeroFormateado = 'N° ' . str_pad($numeroNuevo, 6, '0', STR_PAD_LEFT);

    echo $numeroFormateado;
} catch (PDOException $e) {
    // En producción puedes omitir detalles específicos del error
    echo "Error al obtener número de ficha: " . $e->getMessage();
}
