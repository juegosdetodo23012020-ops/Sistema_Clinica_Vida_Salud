<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validamos que el ID llegue correctamente
    if (!isset($_POST['id_cita']) || empty($_POST['id_cita'])) {
        die("Error: No se recibió el ID de la cita. Revisa el formulario.");
    }

    $id_cita = $_POST['id_cita'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $id_usuario = $_SESSION['usuario_id'];
    $origen = isset($_POST['origen']) ? $_POST['origen'] : 'citas';

    try {
        $sql = "SELECT fn_cambiar_estado_cita(:id_cita, :id_usuario, CAST(:nuevo_estado AS estado_cita))";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':nuevo_estado' => $nuevo_estado
        ]);

        if ($origen == 'agenda') {
            header("Location: agenda.php?mensaje=estado_cambiado");
        } else {
            header("Location: citas.php?mensaje=estado_cambiado");
        }
        exit;

    } catch (PDOException $e) {
        error_log("Error al cambiar estado: " . $e->getMessage());
        
        if (strpos($e->getMessage(), 'RN19') !== false || strpos($e->getMessage(), 'transicion') !== false) {
            $error = 'transicion_invalida';
        } elseif (strpos($e->getMessage(), 'RN12') !== false) {
            $error = 'sin_permiso';
        } else {
            $error = 'sistema';
        }
        
        if ($origen == 'agenda') {
            header("Location: agenda.php?error=" . $error);
        } else {
            header("Location: citas.php?error=" . $error);
        }
        exit;
    }
} else {
    header("Location: citas.php");
    exit;
}
?>