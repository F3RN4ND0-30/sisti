<?php
// Configuración del Sistema
session_name('HELPDESK_SISTEMA');
session_start();

// Valores por defecto
$valoresDefecto = [
    'nombreSistema' => 'Configuración',
    'version' => '2.0',
    'modoMantenimiento' => true,
    'emailSoporte' => 'admin@mpp.com'
];

// Si hay configuración guardada en sesión, usarla, si no, los valores por defecto
$configSistema = $_SESSION['configSistema'] ?? $valoresDefecto;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restaurar'])) {
        // Restaurar valores por defecto
        $configSistema = $valoresDefecto;
        $mensaje = "Configuración restaurada a valores por defecto.";
    } else {
        // Guardar cambios
        $configSistema['modoMantenimiento'] = isset($_POST['modoMantenimiento']);
        $configSistema['emailSoporte'] = $_POST['emailSoporte'] ?? $configSistema['emailSoporte'];
        $mensaje = "Configuración actualizada correctamente.";
    }
    // Guardar en sesión
    $_SESSION['configSistema'] = $configSistema;
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Configuración del Sistema | HelpDesk</title>
    <link rel="stylesheet" href="../../backend/css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../backend/css/navbar/navbar.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />
    <style>
        /* Inputs personalizados */
        .user-role p {
        color: #fff;  /* Blanco para que resalte el correo (Antes se via muy poco) */
        font-weight: bold;
        font-size: 16px;
        opacity: 1; /* Quita la transparencia (Mas resaltante y se nota mejor*/
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.3);
            outline: none;
        }

        /* Switch moderno */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        .switch input { display: none; }
        .slider {
            position: absolute;
            cursor: pointer;
            background-color: #ccc;
            border-radius: 34px;
            top: 0; left: 0; right: 0; bottom: 0;
            transition: .4s;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 24px; width: 24px;
            left: 3px; bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }
        input:checked + .slider { background-color: #2563eb; }
        input:checked + .slider:before { transform: translateX(28px); }

        /* Mensaje animado */
        .alert-success {
            background: #e8f5e9;
            border: 1px solid #27ae60;
            color: #27ae60;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease-out;
        }
        .alert-success i { font-size: 20px; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px);}
            to { opacity: 1; transform: translateY(0);}
        }

        /* Botones */
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #2471a3);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #333;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
    </style>
</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="main-content">
        <!-- Encabezado -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-8">
                    <h2>Configuración del Sistema</h2>
                    <p class="mb-0">Ajustes generales del sistema HelpDesk</p>
                    <small>Municipalidad Provincial de Pisco</small>
                </div>
                <div class="col-md-4 text-right">
                    <div class="user-role">
                        <h5>Administrador</h5>
                        <p><?= htmlspecialchars($configSistema['emailSoporte']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor principal -->
        <div class="row">
            <div class="col-lg-12">
                <div class="activity-card">
                    <h4><i class="material-icons">settings</i> Configuración del Sistema</h4>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert-success">
                            <i class="material-icons">check_circle</i>
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre del Sistema:</strong></p>
                                <input type="text" name="nombreSistema" value="<?= htmlspecialchars($configSistema['nombreSistema']) ?>" disabled class="form-control">
                            </div>
                            <div class="col-md-6">
                                <p><strong>Versión:</strong></p>
                                <input type="text" name="version" value="<?= htmlspecialchars($configSistema['version']) ?>" disabled class="form-control">
                            </div>
                        </div>

                        <div style="margin-top:20px;">
                            <p><strong>Modo Mantenimiento:</strong></p>
                            <label class="switch">
                                <input type="checkbox" name="modoMantenimiento" <?= $configSistema['modoMantenimiento'] ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <p style="margin-top:20px;">
                            <strong>Email:</strong>
                            <input type="email" name="emailSoporte" value="<?= htmlspecialchars($configSistema['emailSoporte']) ?>" class="form-control">
                        </p>

                        <div style="margin-top:20px;">
                            <button type="submit" name="guardar" class="btn btn-primary">Guardar Cambios</button>
                            <button type="submit" name="restaurar" class="btn btn-secondary">Restaurar Valores</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
