<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cita = $_POST['id_cita'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $origen = isset($_POST['origen']) ? $_POST['origen'] : 'citas';
    
    // Calculamos la hora de fin sumando 15 minutos
    $hora_fin = date('H:i:s', strtotime($hora_inicio . ' +15 minutes'));
    $id_usuario = $_SESSION['usuario_id'];

    try {
        $sql = "SELECT fn_reprogramar_cita(:id_cita, :id_usuario, :fecha, :inicio, :fin)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':fecha' => $fecha,
            ':inicio' => $hora_inicio,
            ':fin' => $hora_fin
        ]);

        // Redirección al éxito (Inteligente)
        if ($origen == 'mis_citas') {
            header("Location: mis_citas.php?mensaje=reprogramada");
        } else {
            header("Location: citas.php?mensaje=reprogramada");
        }
        exit;

    } catch (PDOException $e) {
        $mensajeError = $e->getMessage();
        error_log("Error al reprogramar: " . $mensajeError);

        if (strpos($mensajeError, 'RN05') !== false) {
            $tipo_error = 'fuera_horario';
        } elseif (strpos($mensajeError, 'RN06') !== false) {
            $tipo_error = 'fecha_pasada';
        } elseif (strpos($mensajeError, 'RN07') !== false || strpos($mensajeError, 'ex_cita_sin_cruce') !== false) {
            $tipo_error = 'ocupado';
        } else {
            $tipo_error = 'sistema';
        }

        // Redirección al error (Inteligente, manteniendo el origen)
        header("Location: reprogramar_cita.php?id=" . $id_cita . "&error=" . $tipo_error . "&origen=" . $origen);
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>