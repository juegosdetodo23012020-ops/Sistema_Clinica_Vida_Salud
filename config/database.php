<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos extraídos de tu cadena de conexión de Neon
$host = "ep-hidden-glitter-b4n6ilin-pooler.c-6.us-east-2.aws.neon.tech";
$port = "5432";
$dbname = "neondb";
$user = "neondb_owner";
$password = "npg_qyRViE9M3LPB";

// El endpoint ID es la primera parte de tu host
$endpoint_id = "ep-hidden-glitter-b4n6ilin-pooler";

try {
    // LA MAGIA ESTÁ AQUÍ: Agregamos "options=endpoint=..." al DSN
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=endpoint=$endpoint_id";
    
    $conexion = new PDO($dsn, $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mantenemos el search_path por si el script SQL creó el esquema 'clinica'
    $conexion->exec("SET search_path TO clinica, public;");
    
    // echo "<h1>¡Conexión exitosa a Neon!</h1>";
} catch (PDOException $e) {
    echo "<h1>Error al conectar: " . $e->getMessage() . "</h1>";
}
?>