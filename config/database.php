<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURACIÓN LOCAL (XAMPP) ---
$host = "localhost";
$port = "5432";
$dbname = "clinica_vida_salud"; // El nombre de tu base de datos local
$user = "postgres"; // Usuario por defecto de pgAdmin (cámbialo si usas otro)
$password = "12345"; // Tu contraseña local

try {
    // Conexión local (sin las opciones de Neon)
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    $conexion = new PDO($dsn, $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mantenemos el search_path para tu esquema 'clinica'
    $conexion->exec("SET search_path TO clinica, public;");
    
} catch (PDOException $e) {
    echo "<h1>Error al conectar a la base de datos LOCAL: " . $e->getMessage() . "</h1>";
}
?>