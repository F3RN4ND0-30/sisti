<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../../login.php');
    exit();
}

require_once '../../../backend/bd/conexion.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Crear Ticket | HelpDesk MPP</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../../../backend/css/vistas/escritorio.css">
    <link rel="stylesheet" href="../../../backend/css/navbar/navbar.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../backend/img/logoPisco.png" />

    <style>
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php include '../../navbar/navbar.php'; ?>

    <div class="main-content">
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-12">
                    <h2><i class="material-icons" style="vertical-align: middle;">add_circle</i> Crear Nuevo Ticket</h2>
                    <p class="mb-0">Registrar incidencia desde el panel administrativo</p>
                </div>
            </div>
        </div>

        <div class="form-container">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dni = $_POST['dni'] ?? '';
                $nombres = $_POST['nombres'] ?? '';
                $apPaterno = $_POST['apPaterno'] ?? '';
                $apMaterno = $_POST['apMaterno'] ?? '';
                $idArea = $_POST['idArea'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $prioridad = $_POST['prioridad'] ?? 'Normal';

                if (!empty($dni) && !empty($nombres) && !empty($apPaterno) && !empty($idArea) && !empty($descripcion)) {
                    try {
                        $conexion->beginTransaction();

                        // Verificar/crear usuario externo
                        $stmt = $conexion->prepare("SELECT IdUsuarioExterno FROM tb_UsuariosExternos WHERE Dni = ?");
                        $stmt->execute([$dni]);
                        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$usuario) {
                            $stmtUsuario = $conexion->prepare("
                                INSERT INTO tb_UsuariosExternos (Dni, Nombres, ApellidoPaterno, ApellidoMaterno, FechaCreacion)
                                VALUES (?, ?, ?, ?, GETDATE())
                            ");
                            $stmtUsuario->execute([$dni, $nombres, $apPaterno, $apMaterno]);
                            $usuarioId = $conexion->lastInsertId();
                        } else {
                            $usuarioId = $usuario['IdUsuarioExterno'];
                        }

                        // Generar código de ticket
                        $fechaActual = date('Ymd');
                        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $codigoTicket = "TCK-$fechaActual-$random";

                        // Insertar ticket
                        $stmtTicket = $conexion->prepare("INSERT INTO tb_Tickets (CodigoTicket, FechaCreacion) VALUES (?, GETDATE())");
                        $stmtTicket->execute([$codigoTicket]);
                        $ticketId = $conexion->lastInsertId();

                        // Insertar incidente
                        $stmtIncidente = $conexion->prepare("
                            INSERT INTO tb_Incidentes (IdTicket, IdUsuarioExterno, IdArea, Descripcion, IdEstadoIncidente, Prioridad, FechaCreacion)
                            VALUES (?, ?, ?, ?, 1, ?, GETDATE())
                        ");
                        $stmtIncidente->execute([$ticketId, $usuarioId, $idArea, $descripcion, $prioridad]);

                        $conexion->commit();
                        echo '<div class="alert alert-success">✅ <strong>Ticket creado exitosamente!</strong><br>Código: <strong>' . $codigoTicket . '</strong></div>';

                        // Limpiar variables para nueva entrada
                        $dni = $nombres = $apPaterno = $apMaterno = $idArea = $descripcion = '';
                    } catch (PDOException $e) {
                        $conexion->rollBack();
                        echo '<div class="alert alert-danger">❌ Error al crear ticket: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">❌ Por favor complete todos los campos obligatorios.</div>';
                }
            }
            ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dni">DNI *</label>
                            <input type="text" class="form-control" id="dni" name="dni" maxlength="8"
                                value="<?php echo htmlspecialchars($dni ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombres">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres"
                                value="<?php echo htmlspecialchars($nombres ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apPaterno">Apellido Paterno *</label>
                            <input type="text" class="form-control" id="apPaterno" name="apPaterno"
                                value="<?php echo htmlspecialchars($apPaterno ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apMaterno">Apellido Materno</label>
                            <input type="text" class="form-control" id="apMaterno" name="apMaterno"
                                value="<?php echo htmlspecialchars($apMaterno ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="idArea">Área Afectada *</label>
                            <select class="form-control" id="idArea" name="idArea" required>
                                <option value="">Seleccione un área</option>
                                <?php
                                try {
                                    $stmt = $conexion->prepare("SELECT IdArea, Nombre FROM tb_Areas WHERE Activo = 1 ORDER BY Nombre");
                                    $stmt->execute();
                                    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($areas as $area) {
                                        $selected = (isset($idArea) && $idArea == $area['IdArea']) ? 'selected' : '';
                                        echo "<option value='" . $area['IdArea'] . "' $selected>" . htmlspecialchars($area['Nombre']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Error al cargar áreas</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prioridad">Prioridad</label>
                            <select class="form-control" id="prioridad" name="prioridad">
                                <option value="Baja" <?php echo (isset($prioridad) && $prioridad == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                <option value="Normal" <?php echo (!isset($prioridad) || $prioridad == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                <option value="Alta" <?php echo (isset($prioridad) && $prioridad == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                                <option value="Crítica" <?php echo (isset($prioridad) && $prioridad == 'Crítica') ? 'selected' : ''; ?>>Crítica</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción del Problema *</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                        placeholder="Describa detalladamente el problema técnico..." required><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons" style="vertical-align: middle;">add</i>
                        Crear Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>