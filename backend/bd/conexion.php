<?php
// Configuración
$host = 'localhost'; // o IP del servidor MariaDB
$puerto = '3306'; // Puerto por defecto de MySQL/MariaDB
$basedatos = 'sisti';
$usuario_sql = 'root'; // <- Cambia esto
$password_sql = ''; // <- Cambia esto
$logFile  = __DIR__ . '/log_conexion.txt';

// Log
function log_conexion($msg)
{
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

// Conexion
try {
    log_conexion("Intentando conexión con MariaDB/MySQL (Usuario: $usuario_sql)...");

    // DSN para MariaDB
    $dsn = "mysql:host=$host;port=$puerto;dbname=$basedatos;charset=utf8mb4";

    $conexion = new PDO($dsn, $usuario_sql, $password_sql, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    log_conexion("✅ Conectado correctamente a $basedatos con usuario $usuario_sql");
} catch (PDOException $e) {
    log_conexion("❌ Error de conexión: " . $e->getMessage());
    die("Error de conexión: " . $e->getMessage());
}
