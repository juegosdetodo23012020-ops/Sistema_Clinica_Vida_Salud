<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Capturamos los datos
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    try {
        // 2. Insertamos en la base de datos.
        // OJO: No validamos duplicados con un SELECT aquí. 
        // Dejamos que la base de datos lo haga gracias a la restricción UNIQUE de 'nombre_normalizado'.
        $sql = "INSERT INTO especialidad (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion
        ]);

        // 3. Si todo sale bien, redirigimos con éxito.
        header("Location: especialidades.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // 4. CAPTURA DE ERROR DE DUPLICADO:
        // El código de error de PostgreSQL para "unique violation" es '23505'
        if ($e->getCode() == '23505') {
            header("Location: especialidades.php?error=duplicado");
            exit;
        } else {
            // Si es otro error (ej. base de datos caída), lo guardamos en log.
            error_log("Error al registrar especialidad: " . $e->getMessage());
            header("Location: especialidades.php?error=sistema");
            exit;
        }
    }
} else {
    header("Location: especialidades.php");
    exit;
}
?>