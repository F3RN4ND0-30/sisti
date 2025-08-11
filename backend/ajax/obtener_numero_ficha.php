<?php
require_once __DIR__ . '/../bd/conexion.php';

$stmt = $conexion->query("SELECT ISNULL(MAX(Numero), 0) + 1 AS nuevo_num FROM ficha_control");
$numeroNuevo = (int)$stmt->fetchColumn();
$numeroFormateado = 'N° ' . str_pad($numeroNuevo, 6, '0', STR_PAD_LEFT);

echo $numeroFormateado;
