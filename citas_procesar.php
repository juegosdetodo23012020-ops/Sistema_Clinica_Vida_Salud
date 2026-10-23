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

    // Calculamos la hora de fin (sumamos 15 minutos a la hora de inicio)
    $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +15 minutes'));

           try {
        // 1. Obtener la especialidad principal del médico
        // (La tabla cita SÍ necesita la especialidad, y como un médico puede tener varias, tomamos la primera)
        $sql_esp = "SELECT id_especialidad FROM medico_especialidad WHERE id_medico = :id_medico LIMIT 1";
        $stmt_esp = $conexion->prepare($sql_esp);
        $stmt_esp->execute([':id_medico' => $id_medico]);
        $esp = $stmt_esp->fetch(PDO::FETCH_ASSOC);
        
        if (!$esp) {
            die("ERROR LÓGICO: El médico seleccionado no tiene ninguna especialidad asignada en la base de datos.");
        }
        $id_especialidad = $esp['id_especialidad'];

        // 2. Insertar la cita
        // NOTA: Ya NO enviamos id_horario porque la tabla no lo tiene.
        // El Trigger de PostgreSQL validará automáticamente si la hora está dentro del horario del médico.
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
            ':creada_por' => $_SESSION['usuario_id']
        ]);

        // Si todo sale bien, redirigimos con éxito
        header("Location: citas.php?mensaje=exito");
        exit;

       } catch (PDOException $e) {
        // Si la base de datos lanza un error por horario o duplicado
        if (strpos($e->getMessage(), 'RN05') !== false) {
            header("Location: citas.php?error=horario");
        } elseif (strpos($e->getMessage(), 'ex_cita_sin_cruce') !== false || strpos($e->getMessage(), 'unique') !== false) {
            header("Location: citas.php?error=ocupado");
        } else {
            header("Location: citas.php?error=sistema");
        }
        exit;
    }
} else {
    header("Location: citas.php");
    exit;
}
?>
