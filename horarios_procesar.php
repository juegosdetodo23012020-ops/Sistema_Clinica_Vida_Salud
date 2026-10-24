<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_medico = $_POST['id_medico'];
    $dia_semana = $_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $duracion = 15; // Nuestra configuración de 15 minutos

    try {
        $sql = "INSERT INTO horario_atencion (id_medico, dia_semana, hora_inicio, hora_fin, duracion_cita_min) 
                VALUES (:id_medico, :dia, :inicio, :fin, :duracion)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_medico' => $id_medico,
            ':dia' => $dia_semana,
            ':inicio' => $hora_inicio,
            ':fin' => $hora_fin,
            ':duracion' => $duracion
        ]);

        header("Location: horarios.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // El código 23P01 es cuando choca con la restricción EXCLUDE (cruce de horarios)
        if ($e->getCode() == '23P01') {
            header("Location: horarios.php?error=cruce");
            exit;
        } else {
            error_log("Error al crear horario: " . $e->getMessage());
            header("Location: horarios.php?error=sistema");
            exit;
        }
    }
} else {
    header("Location: horarios.php");
    exit;
}
?>