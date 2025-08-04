<?php
// Configuración del Sistema
session_name('HELPDESK_SISTEMA');
session_start();

// Datos de ejemplo (si no existen en sesión)
$_SESSION['usuario'] = $_SESSION['usuario'] ?? [
    'nombre' => '',
    'apellido' => '',
    'sexo' => '',
    'dni' => '',
    'nro' => '',
    'edad' => 18,
    'email' => 'admin@mpp.com'
];

$usuario = $_SESSION['usuario'];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | HelpDesk</title>
    <link rel="stylesheet" href="../../backend/css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../backend/css/navbar/navbar.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
    <style>
        /* Contenedor principal */
        .user-role p {
        color: #fff;  /* Blanco para que resalte el correo (Antes se via muy poco) */
        font-weight: bold;
        font-size: 16px;
        opacity: 1; /* Quita la transparencia (Mas resaltante y se nota mejor*/
        }

        .perfil-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .perfil-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .perfil-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            font-size: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(37,99,235,0.3);
        }
        .perfil-header h2 {
            margin: 10px 0 5px;
            font-size: 26px;
            color: #2c3e50;
        }
        .perfil-header p {
            color: #6c757d;
            font-size: 16px;
        }
        /* Campos */
        .perfil-item {
            margin-bottom: 20px;
        }
        .perfil-item label {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            font-size: 15px;
            color: #555;
        }
        /* Botón */
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            display: block;
            margin: 25px auto 0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
        }
    </style>
</head>
<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="main-content">
        <!-- Encabezado estilo dashboard -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>Mi Perfil</h2>
                    <p class="mb-0">Información personal del usuario</p>
                    <small>Municipalidad Provincial de Pisco</small>
                </div>
                <div class="col-md-4 text-right">
                    <div class="user-role">
                        <h5>Usuario</h5>
                        <p><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de perfil -->
        <div class="perfil-container">
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <?= strtoupper(substr($usuario['nombre'],0,1)) ?>
                </div>
                <h2><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h2>
                <p><?= htmlspecialchars($usuario['email']) ?></p>
            </div>

            <div class="perfil-item">
                <label>DNI:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['dni']) ?>" class="form-control" disabled>
            </div>
            <div class="perfil-item">
                <label>Teléfono:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['nro']) ?>" class="form-control" disabled>
            </div>
            <div class="perfil-item">
                <label>Sexo:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['sexo']) ?>" class="form-control" disabled>
            </div>
            <div class="perfil-item">
                <label>Edad:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['edad']) ?>" class="form-control" disabled>
            </div>

            <button class="btn btn-primary">Editar Perfil</button>
        </div>
    </div>
</body>
</html>




