<?php
require_once '../../bd/conexion.php';
$conn = $conexion; // Se asegura que $conn use la conexión de conexion.php
header('Content-Type: application/json');

// === RUTEO DE ACCIONES ===
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents("php://input"), true);

switch ($action) {
    case 'listar':
        listarUsuarios($conn);
        break;
    case 'roles':
        listarRoles($conn);
        break;
    case 'crear':
        crearUsuario($conn, $input);
        break;
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        obtenerUsuario($conn, $id);
        break;
    case 'editar':
        editarUsuario($conn, $input);
        break;
    case 'toggle':
        toggleUsuario($conn, $input);
        break;
    case 'password':
        cambiarPassword($conn, $input);
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
        break;
}

// ==================================================
// === FUNCIONES SQL SERVER - USUARIOS
// ==================================================

// Listar todos los usuarios con su rol
function listarUsuarios($conn)
{
    try {
        $sql = "SELECT u.Id_Usuarios, u.Dni, u.Nombre, u.Apellido_Paterno, u.Apellido_Materno,
                       u.Usuario, u.Activo, u.Fecha_Creacion, r.Nombre AS rol_nombre
                FROM tb_Usuarios u
                LEFT JOIN tb_Roles r ON u.Id_Roles = r.Id_Roles
                ORDER BY u.Fecha_Creacion DESC";
        $res = $conn->query($sql);
        $usuarios = $res->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["data" => $usuarios]); // ✅ DataTables requiere la key "data"
    } catch (PDOException $e) {
        echo json_encode(["data" => [], "error" => $e->getMessage()]);
    }
}

// Listar roles activos
function listarRoles($conn)
{
    try {
        $sql = "SELECT Id_Roles, Nombre FROM tb_Roles WHERE Estado = 1 ORDER BY Nombre";
        $res = $conn->query($sql);
        echo json_encode(["status" => "success", "data" => $res->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Crear nuevo usuario
function crearUsuario($conn, $data)
{
    if (!isset($data['dni'], $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['id_roles'], $data['usuario'], $data['password'])) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        return;
    }

    try {
        $clave = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO tb_Usuarios 
            (Dni, Nombre, Apellido_Paterno, Apellido_Materno, Id_Roles, Activo, Fecha_Creacion, Usuario, Clave)
            VALUES (?, ?, ?, ?, ?, 1, GETDATE(), ?, ?)");
        $stmt->execute([
            $data['dni'],
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['id_roles'],
            strtolower($data['usuario']),
            $clave
        ]);
        echo json_encode(["status" => "success", "message" => "Usuario creado correctamente"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Obtener usuario específico
function obtenerUsuario($conn, $id)
{
    try {
        $stmt = $conn->prepare("SELECT * FROM tb_Usuarios WHERE Id_Usuarios = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) echo json_encode(["status" => "success", "data" => $usuario]);
        else echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Editar rol y estado de usuario
function editarUsuario($conn, $data)
{
    if (!isset($data['id_usuarios'], $data['id_roles'], $data['activo'])) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        return;
    }
    try {
        $stmt = $conn->prepare("UPDATE tb_Usuarios SET Id_Roles = ?, Activo = ? WHERE Id_Usuarios = ?");
        $stmt->execute([$data['id_roles'], $data['activo'], $data['id_usuarios']]);
        echo json_encode(["status" => "success", "message" => "Usuario actualizado"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Activar / desactivar usuario
function toggleUsuario($conn, $data)
{
    try {
        $stmt = $conn->prepare("UPDATE tb_Usuarios SET Activo = ? WHERE Id_Usuarios = ?");
        $stmt->execute([$data['estado'], $data['id_usuarios']]);
        echo json_encode(["status" => "success", "message" => "Estado actualizado"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Cambiar contraseña
function cambiarPassword($conn, $data)
{
    try {
        $hash = password_hash($data['nueva'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE tb_Usuarios SET Clave = ? WHERE Id_Usuarios = ?");
        $stmt->execute([$hash, $data['id_usuarios']]);
        echo json_encode(["status" => "success", "message" => "Contraseña actualizada"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
