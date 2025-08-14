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
  <div class="main-content">
    <h2>Generar Fichas Técnicas</h2>

    <div class="row">
      <div class="col-lg-12">
        <div class="activity-card">
          <h4><i class="material-icons">support_agent</i> Sistema de reportes del SISTI</h4>
          <p>El Sistema de reportes del SISTI de la Municipalidad Provincial de Pisco permite la optimización en la creación de las fichas de reporte. Proporciona un formulario donde se rellenan los datos de manera eficiente y organizada.</p>
        </div>
      </div>
    </div>

    <div class="ficha-layout">
      <div class="ficha-container">
        <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_mantenimiento.php'">Ficha de Mantenimiento</button>
        <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_instalacion.php'">Ficha de Instalación</button>
        <button class="boton-ficha" onclick="window.location.href='../../reportes/reporte_atencion/ficha_baja.php'">Ficha de Baja</button>
      </div>
      <div class="ficha-imagen">
        <img src="../../../backend/img/reporte.png" alt="Ficha Técnica" />
      </div>
    </div>
  </div>
</body>

</html>