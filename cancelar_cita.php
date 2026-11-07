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
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : 'Cancelada por el usuario';
    $id_usuario = $_SESSION['usuario_id'];
    
    // Recibimos el origen (mis_citas, agenda o citas) para saber a dónde redirigir
    $origen = isset($_POST['origen']) ? $_POST['origen'] : 'citas';

    try {
        // 1. Llamamos a la función de PostgreSQL
        $sql = "SELECT fn_cancelar_cita(:id_cita, :id_usuario, :motivo)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':motivo' => $motivo
        ]);

        // 2. Redirección inteligente al éxito
        if ($origen == 'mis_citas') {
            header("Location: mis_citas.php?mensaje=cancelada");
        } elseif ($origen == 'agenda') {
            header("Location: agenda.php?mensaje=cancelada");
        } else {
            header("Location: citas.php?mensaje=cancelada");
        }
        exit;

    } catch (PDOException $e) {
        error_log("Error al cancelar cita: " . $e->getMessage());
        
        // Redirección inteligente al error
        if ($origen == 'mis_citas') {
            header("Location: mis_citas.php?error=sistema");
        } elseif ($origen == 'agenda') {
            header("Location: agenda.php?error=sistema");
        } else {
            header("Location: citas.php?error=sistema");
        }
        exit;
    }
} else {
    // Si alguien entra directo al archivo sin POST, lo mandamos al dashboard
    header("Location: dashboard.php");
    exit;
}
?>