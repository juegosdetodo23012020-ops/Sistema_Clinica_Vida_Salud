<?php
session_start();
require 'config/database.php';

// Verificamos que los datos vengan del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Capturamos los datos enviados por el usuario
    $ci = trim($_POST['ci']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $sexo = $_POST['sexo'];
    $telefono = trim($_POST['telefono']);

    try {
        // 2. VALIDACIÓN DE LA REGLA DE NEGOCIO RN01 (CI Única)
        // Buscamos si ya existe un paciente con esa misma cédula.
        $check_sql = "SELECT id_paciente FROM paciente WHERE ci = :ci";
        $check_stmt = $conexion->prepare($check_sql);
        $check_stmt->execute([':ci' => $ci]);
        
        if ($check_stmt->rowCount() > 0) {
            // Si rowCount() devuelve más de 0, significa que la CI ya existe.
            // Lo devolvemos a la pantalla con un mensaje de error.
            header("Location: pacientes.php?error=duplicado");
            exit;
        }

        // 3. INSERTAR EL NUEVO PACIENTE
        // No enviamos el campo 'codigo' porque el Trigger de PostgreSQL lo genera solo.
        $sql = "INSERT INTO paciente (ci, nombres, apellidos, fecha_nacimiento, sexo, telefono) 
                VALUES (:ci, :nombres, :apellidos, :fecha_nac, :sexo, :telefono)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':ci' => $ci,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':fecha_nac' => $fecha_nacimiento,
            ':sexo' => $sexo,
            ':telefono' => $telefono
        ]);

        // 4. Si todo sale bien, redirigimos con mensaje de éxito.
        header("Location: pacientes.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // Si hay un error (ej. la fecha es inválida), lo guardamos en el log y mostramos error.
        error_log("Error al registrar paciente: " . $e->getMessage());
        header("Location: pacientes.php?error=sistema");
        exit;
    }
} else {
    // Si intentan entrar directo al archivo, los devolvemos.
    header("Location: pacientes.php");
    exit;
}
?>