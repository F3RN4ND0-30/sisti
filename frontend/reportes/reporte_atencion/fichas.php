<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
  header('location: ../login.php');
  exit();
}
?>

<!-- fichas.php -->
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Fichas Técnicas</title>
  <!-- css Basicos -->
  <link rel="stylesheet" href="../../../backend/css/reportes/reporte_atencion/fichas.css">
  <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css">

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
</head>

<body>

  <?php include '../../navbar/navbar.php'; ?>

  <h2>Generar Fichas Técnicas</h2>

  <div class="ficha-container">
    <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_mantenimiento.php'">Ficha de Mantenimiento</button>
    <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_instalacion.php'">Ficha de Instalación</button>
    <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_baja.php'">Ficha de Baja</button>
  </div>
</body>

</html>