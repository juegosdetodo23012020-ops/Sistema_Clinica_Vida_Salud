<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$port = "5432";
$dbname = "clinica_vida_salud";
$user = "postgres";
$password = "12345"; // ¡CAMBIA ESTO POR TU CONTRASEÑA REAL DE POSTGRESQL!

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conexion = new PDO($dsn, $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("SET search_path TO clinica, public;");
    //echo "<h1>¡Conexión exitosa, ESTO ES UNA LOCURA !</h1>";
} catch (PDOException $e) {
    echo "<h1>Error al conectar: busca el error  " . $e->getMessage() . "</h1>";
}
?>