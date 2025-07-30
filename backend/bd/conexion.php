<?php

// Configuración
$servidor = 'DESKTOP-B340BGP\SQLEXPRESS';
$basedatos = 'DB_HELPDESK';

// Modo de autenticación (cambiar aquí)
$usar_windows = true; // true = Windows Auth, false = SQL Auth
$usuario_sql = 'saF';
$password_sql = 'Muni1234';

try {
    if ($usar_windows) {
        // Autenticación Windows
        $dsn = "sqlsrv:Server=$servidor;Database=$basedatos;TrustServerCertificate=true";
        $conexion = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        // Autenticación SQL Server
        $dsn = "sqlsrv:Server=$servidor;Database=$basedatos;TrustServerCertificate=true";
        $conexion = new PDO($dsn, $usuario_sql, $password_sql, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
} catch (PDOException $e) {
    // Fallback a ODBC si falla sqlsrv
    try {
        if ($usar_windows) {
            $dsn = "odbc:Driver={ODBC Driver 17 for SQL Server};Server=$servidor;Database=$basedatos;Trusted_Connection=yes;";
            $conexion = new PDO($dsn);
        } else {
            $dsn = "odbc:Driver={ODBC Driver 17 for SQL Server};Server=$servidor;Database=$basedatos;UID=$usuario_sql;PWD=$password_sql;";
            $conexion = new PDO($dsn);
        }
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die("Error de conexión: " . $e2->getMessage());
    }
}
