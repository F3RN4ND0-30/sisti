<?php
// Configuración
$servidor   = 'localhost';     // o 127.0.0.1
$basedatos  = 'sisti';         // el nombre de tu BD en phpMyAdmin
$usuario_sql = 'root';         // por defecto en XAMPP/WAMP
$password_sql = '';            // vacío si no has puesto contraseña
$logFile  = __DIR__ . '/log_conexion.txt';

// Log
function log_conexion($msg)
{
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

// Conexión
try {
    log_conexion("Intentando conexión con MySQL (usuario: $usuario_sql)...");

    $dsn = "mysql:host=$servidor;dbname=$basedatos;charset=utf8mb4";
    $conexion = new PDO($dsn, $usuario_sql, $password_sql, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    log_conexion("✅ Conectado correctamente a $basedatos en $servidor");
} catch (PDOException $e) {
    log_conexion("❌ Error de conexión: " . $e->getMessage());
    die("Error de conexión: " . $e->getMessage());
}
