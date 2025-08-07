<?php
session_name('HELPDESK_SISTEMA');
session_start();

if (!isset($_SESSION['hd_activo']) || $_SESSION['hd_activo'] !== true) {
    header('location: ../login.php');
    exit();
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - HelpDesk</title>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- CSS propio -->
    <link rel="stylesheet" href="/sisti/backend/css/admin/usuarios.css">
    <link rel="icon" type="image/png" href="../../backend/img/logoPisco.png" />

</head>

<body>
    <?php include '../navbar/navbar.php'; ?>

    <div class="container-fluid p-4">
        <!-- Contenedor de alertas -->
        <div class="usuarios-alert-container"></div>

        <div class="row">
            <div class="col-12">
                <div class="card usuarios-card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="material-icons me-2">people</i> Gestión de Usuarios</h4>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                            <i class="material-icons me-1">person_add</i> Nuevo Usuario
                        </button>
                    </div>

                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="tablaUsuarios" class="table usuarios-table table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>DNI</th>
                                        <th>Nombres</th>
                                        <th>Apellido Paterno</th>
                                        <th>Apellido Materno</th>
                                        <th>Usuario</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody><!-- Cargado por JS --></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR USUARIO -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content usuarios-modal">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="material-icons me-2">person_add</i> Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearUsuario">
                        <!-- 🔹 Datos Personales -->
                        <div class="usuarios-section">
                            <h6 class="usuarios-section-title"><i class="material-icons">person</i> Datos Personales</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>DNI *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dni" maxlength="8" required placeholder="12345678">
                                        <button type="button" class="btn btn-outline-primary" id="btnBuscarReniec" title="Buscar en RENIEC">
                                            <i class="material-icons">search</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Nombres *</label>
                                    <input type="text" class="form-control" id="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Apellido Paterno *</label>
                                    <input type="text" class="form-control" id="apellido_paterno" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Apellido Materno *</label>
                                    <input type="text" class="form-control" id="apellido_materno" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Usuario (auto) *</label>
                                    <input type="text" class="form-control" id="usuario" readonly required>
                                </div>
                            </div>
                        </div>

                        <!-- 🔹 Datos del Sistema -->
                        <div class="usuarios-section">
                            <h6 class="usuarios-section-title"><i class="material-icons">admin_panel_settings</i> Datos del Sistema</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Rol *</label>
                                    <select class="form-select" id="id_roles" required></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Contraseña Temporal *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" required>
                                        <button type="button" class="btn btn-outline-secondary" id="btnGenerarPassword"><i class="material-icons">refresh</i></button>
                                    </div>
                                    <small class="text-muted">O generar automáticamente</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="material-icons me-1">cancel</i> Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUsuario"><i class="material-icons me-1">save</i> Crear Usuario</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content usuarios-modal">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="material-icons me-2">edit</i> Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        <input type="hidden" id="edit_id_usuarios">
                        <div class="usuarios-section">
                            <h6 class="usuarios-section-title"><i class="material-icons">person</i> Información</h6>
                            <div class="row">
                                <div class="col-md-4"><label>DNI</label><input type="text" class="form-control" id="edit_dni" readonly></div>
                                <div class="col-md-4"><label>Nombres</label><input type="text" class="form-control" id="edit_nombre"></div>
                                <div class="col-md-4"><label>Apellido Paterno</label><input type="text" class="form-control" id="edit_apellido_paterno"></div>
                                <div class="col-md-4"><label>Apellido Materno</label><input type="text" class="form-control" id="edit_apellido_materno"></div>
                                <div class="col-md-4"><label>Usuario</label><input type="text" class="form-control" id="edit_usuario" readonly></div>
                            </div>
                        </div>
                        <div class="usuarios-section">
                            <h6 class="usuarios-section-title"><i class="material-icons">settings</i> Configuración</h6>
                            <div class="row">
                                <div class="col-md-6"><label>Rol</label><select class="form-select" id="edit_id_roles"></select></div>
                                <div class="col-md-6"><label>Estado</label><select class="form-select" id="edit_activo">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="material-icons me-1">cancel</i> Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarUsuario"><i class="material-icons me-1">update</i> Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CAMBIAR CONTRASEÑA -->
    <div class="modal fade" id="modalPasswordUsuario" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content usuarios-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="material-icons me-2">lock_reset</i> Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPasswordUsuario">
                        <input type="hidden" id="pass_id_usuarios">
                        <div class="usuarios-section">
                            <h6 class="usuarios-section-title"><i class="material-icons">vpn_key</i> Nueva Contraseña</h6>
                            <div class="mb-3"><label>Contraseña Nueva *</label><input type="password" class="form-control" id="nueva_password" required></div>
                            <div class="mb-3"><label>Confirmar Contraseña *</label><input type="password" class="form-control" id="confirmar_password" required></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="material-icons me-1">cancel</i> Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarPassword"><i class="material-icons me-1">save</i> Guardar Nueva Contraseña</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/sisti/backend/js/admin/usuarios.js"></script>
</body>

</html>