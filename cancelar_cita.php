<?php
session_start();
require 'config/database.php';

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cita = $_POST['id_cita'];
    $motivo = trim($_POST['motivo']);
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // 1. Llamamos a la función de PostgreSQL que programó tu compañera
        // Esta función cambia el estado a CANCELADA y guarda el motivo.
        $sql = "SELECT fn_cancelar_cita(:id_cita, :id_usuario, :motivo)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':motivo' => $motivo
        ]);

        // 2. El Trigger de la base de datos (trg_cita_historial_y_recordatorio) 
        // se encarga automáticamente de:
        // - Registrar en el historial
        // - Anular la notificación pendiente
        // - Liberar el horario

        // Si todo sale bien, redirigimos con mensaje de éxito
        header("Location: citas.php?mensaje=cancelada");
        exit;

    } catch (PDOException $e) {
        error_log("Error al cancelar cita: " . $e->getMessage());
        // En caso de error, lo mandamos de vuelta
        echo "<h1>Error al cancelar la cita</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<a href='citas.php'>Volver</a>";
        exit;
    }
} else {
    header("Location: citas.php");
    exit;
}
?>