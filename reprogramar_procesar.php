<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cita = $_POST['id_cita'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    
    // Calculamos la hora de fin sumando 15 minutos
    $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +15 minutes'));
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // Llamamos a la función de PostgreSQL
        $sql = "SELECT fn_reprogramar_cita(:id_cita, :id_usuario, :fecha, :inicio, :fin)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':fecha' => $fecha,
            ':inicio' => $hora_inicio,
            ':fin' => $hora_fin
        ]);

        // El Trigger trg_cita_validar valida si hay cruces, si está fuera de horario, etc.
        // El Trigger trg_cita_historial_y_recordatorio actualiza el historial y el recordatorio.

        header("Location: citas.php?mensaje=reprogramada");
        exit;

      } catch (PDOException $e) {
        // Capturamos el error real que viene de los Triggers de PostgreSQL
        $mensajeError = $e->getMessage();
        error_log("Error al reprogramar: " . $mensajeError);

        // Evaluamos qué regla de negocio se rompió
        if (strpos($mensajeError, 'RN05') !== false) {
            $tipo_error = 'fuera_horario';
        } elseif (strpos($mensajeError, 'RN06') !== false) {
            $tipo_error = 'fecha_pasada';
        } elseif (strpos($mensajeError, 'RN07') !== false || strpos($mensajeError, 'ex_cita_sin_cruce') !== false) {
            $tipo_error = 'ocupado';
        } else {
            $tipo_error = 'sistema'; // Errores desconocidos
        }

        // Redirigimos al formulario con el código de error específico
        header("Location: reprogramar_cita.php?id=" . $id_cita . "&error=" . $tipo_error);
        exit;
    }
} else {
    header("Location: citas.php");
    exit;
}
?>