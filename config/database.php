<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos de conexión a NEON (Nube)
$host = "ep-hidden-glitter-b4n6ilin-pooler.c-6.us-east-2.aws.neon.tech";
$port = "5432";
$dbname = "neondb";
$user = "neondb_owner";
$password = "npg_qyRViE9M3LPB";
$endpoint_id = "ep-hidden-glitter-b4n6ilin-pooler";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=endpoint=$endpoint_id";
    $conexion = new PDO($dsn, $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("SET search_path TO clinica, public;");
} catch (PDOException $e) {
    echo "<h1>Error al conectar a Neon: " . $e->getMessage() . "</h1>";
}
?>