<?php
date_default_timezone_set('America/Lima'); // Zona horaria de Lima, Perú

$directorio = __DIR__;
$archivos = array_diff(scandir($directorio), array('.', '..', 'index.php'));

// Filtrar solo archivos (omitir carpetas)
$archivos = array_filter($archivos, function ($archivo) use ($directorio) {
    return is_file($directorio . DIRECTORY_SEPARATOR . $archivo);
});

// Ordenar por fecha de modificación DESC
usort($archivos, function ($a, $b) use ($directorio) {
    return filemtime($directorio . DIRECTORY_SEPARATOR . $b) - filemtime($directorio . DIRECTORY_SEPARATOR . $a);
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Fichas Generadas</title>
    <link rel="stylesheet" href="../../backend/css/archivos/index_archivos.css">
</head>

<body>
    <div class="container">
        <div class="header-flex">
            <h1>Fichas Generadas</h1>
            <a href="/sisti/frontend/reportes/lista_fichas/listado_fichas.php" class="btn-regresar">Regresar</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Archivo</th>
                    <th>Última Modificación</th>
                    <th>Tamaño</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivos as $archivo): ?>
                    <tr>
                        <td><a href="<?= htmlspecialchars($archivo) ?>" download><?= htmlspecialchars($archivo) ?></a></td>
                        <td><?= date("Y-m-d H:i:s", filemtime($archivo)) ?></td>
                        <td><?= round(filesize($archivo) / 1024) ?> KB</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>