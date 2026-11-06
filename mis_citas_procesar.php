<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_paciente = $_POST['id_paciente'];
    $id_medico = $_POST['id_medico'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $modalidad = $_POST['modalidad'];
    $motivo = trim($_POST['motivo']);
    $id_usuario = $_SESSION['usuario_id'];

    // Calculamos la hora de fin
    $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +15 minutes'));

    try {
        // Buscamos la especialidad del médico para guardarla en la cita
        $sql_esp = "SELECT id_especialidad FROM medico_especialidad WHERE id_medico = :id_medico LIMIT 1";
        $stmt_esp = $conexion->prepare($sql_esp);
        $stmt_esp->execute([':id_medico' => $id_medico]);
        $esp = $stmt_esp->fetch(PDO::FETCH_ASSOC);
        $id_especialidad = $esp['id_especialidad'];

        // Insertamos la cita (El Trigger de PostgreSQL validará el cruce de horarios)
        $sql = "INSERT INTO cita (id_paciente, id_medico, id_especialidad, fecha, hora_inicio, hora_fin, modalidad, motivo, creada_por) 
                VALUES (:id_paciente, :id_medico, :id_especialidad, :fecha, :hora_inicio, :hora_fin, :modalidad, :motivo, :creada_por)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $id_paciente,
            ':id_medico' => $id_medico,
            ':id_especialidad' => $id_especialidad,
            ':fecha' => $fecha,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
            ':modalidad' => $modalidad,
            ':motivo' => $motivo,
            ':creada_por' => $id_usuario
        ]);

        header("Location: mis_citas.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // Si el Trigger detecta que el horario está ocupado, redirigimos con error
        header("Location: mis_citas.php?error=ocupado");
        exit;
    }
} else {
    header("Location: mis_citas.php");
    exit;
}
?>