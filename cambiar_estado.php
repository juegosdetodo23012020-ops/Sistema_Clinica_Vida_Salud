<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cita = $_POST['id_cita'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // Llamamos a la función de PostgreSQL que programó tu compañera
        // Esta función actualiza el estado y dispara los Triggers de historial
        $sql = "SELECT fn_cambiar_estado_cita(:id_cita, :id_usuario, CAST(:nuevo_estado AS estado_cita))";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':nuevo_estado' => $nuevo_estado
        ]);

        // Si todo sale bien, redirigimos con mensaje de éxito
        header("Location: citas.php?mensaje=estado_cambiado");
        exit;

    } catch (PDOException $e) {
        // Capturamos errores (por ejemplo, transiciones no válidas)
        $mensajeError = $e->getMessage();
        error_log("Error al cambiar estado: " . $mensajeError);

        // Si es un error de transición no válida (RN19)
        if (strpos($mensajeError, 'RN19') !== false || strpos($mensajeError, 'transicion') !== false) {
            header("Location: citas.php?error=transicion_invalida");
        } elseif (strpos($mensajeError, 'RN12') !== false) {
            header("Location: citas.php?error=sin_permiso");
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