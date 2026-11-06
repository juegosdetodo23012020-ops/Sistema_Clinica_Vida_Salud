<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_paciente = $_POST['id_paciente'];
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    
    $tipo_sangre = trim($_POST['tipo_sangre']);
    $alergias = trim($_POST['alergias']);
    $enfermedades = trim($_POST['enfermedades_cronicas']);
    
    $emergencia_nombre = trim($_POST['emergencia_nombre']);
    $parentesco = trim($_POST['parentesco']);
    $emergencia_telefono = trim($_POST['emergencia_telefono']);

    try {
        $conexion->beginTransaction();

        // 1. Actualizar tabla paciente
        $sql_pac = "UPDATE paciente SET telefono = :telefono, email = :email, direccion = :direccion WHERE id_paciente = :id";
        $stmt_pac = $conexion->prepare($sql_pac);
        $stmt_pac->execute([':telefono' => $telefono, ':email' => $email, ':direccion' => $direccion, ':id' => $id_paciente]);

        // 2. Actualizar o Insertar Información Médica
        $sql_med = "INSERT INTO informacion_medica (id_paciente, tipo_sangre, alergias, enfermedades_cronicas) 
                    VALUES (:id, :sangre, :alergias, :enf)
                    ON CONFLICT (id_paciente) 
                    DO UPDATE SET tipo_sangre = :sangre, alergias = :alergias, enfermedades_cronicas = :enf";
        $stmt_med = $conexion->prepare($sql_med);
        $stmt_med->execute([':id' => $id_paciente, ':sangre' => $tipo_sangre, ':alergias' => $alergias, ':enf' => $enfermedades]);

        // 3. Actualizar o Insertar Contacto de Emergencia
        $sql_eme = "INSERT INTO contacto_emergencia (id_paciente, nombre, parentesco, telefono) 
                    VALUES (:id, :nombre, :parentesco, :telefono)
                    ON CONFLICT (id_paciente) 
                    DO UPDATE SET nombre = :nombre, parentesco = :parentesco, telefono = :telefono";
        $stmt_eme = $conexion->prepare($sql_eme);
        $stmt_eme->execute([':id' => $id_paciente, ':nombre' => $emergencia_nombre, ':parentesco' => $parentesco, ':telefono' => $emergencia_telefono]);

        $conexion->commit();

        header("Location: mi_perfil.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al actualizar perfil: " . $e->getMessage());
        echo "<h1>Error al actualizar</h1><p>" . $e->getMessage() . "</p>";
        exit;
    }
} else {
    header("Location: mi_perfil.php");
    exit;
}
?>