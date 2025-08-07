<?php
// Configuración
$servidor = 'DESKTOP-B340BGP\SQLEXPRESS';
$basedatos = 'DB_HELPDESK';
$usar_windows = true; // true = Windows Auth | false = SQL Auth
$usuario_sql = 'saF';
$password_sql = 'Muni1234';
$logFile  = __DIR__ . '/log_conexion.txt';

// Log
function log_conexion($msg)
{
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

// Conexion
try {
    if ($usar_windows) {
        log_conexion("Intentando conexión con SQL Server (Windows Auth)...");
        $dsn = "sqlsrv:Server=$servidor;Database=$basedatos;TrustServerCertificate=true";
        $conexion = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        log_conexion("Conectado correctamente a $basedatos usando Windows Authentication");
    } else {
        log_conexion("Intentando conexión con SQL Server (Usuario: $usuario_sql)...");
        $dsn = "sqlsrv:Server=$servidor;Database=$basedatos;TrustServerCertificate=true";
        $conexion = new PDO($dsn, $usuario_sql, $password_sql, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        log_conexion("Conectado correctamente a $basedatos con usuario $usuario_sql");
    }
} catch (PDOException $e) {
    log_conexion("❌ Error de conexión: " . $e->getMessage());
    die("Error de conexión: " . $e->getMessage());
}
