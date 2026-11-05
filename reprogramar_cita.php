<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Recibimos el ID de la cita por la URL
$id_cita = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_cita) {
    header("Location: citas.php");
    exit;
}

// Consultamos los datos actuales de la cita
$sql = "SELECT codigo, fecha, hora_inicio, paciente, medico, estado FROM v_cita_detalle WHERE id_cita = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_cita]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita || ($cita['estado'] != 'PROGRAMADA' && $cita['estado'] != 'CONFIRMADA')) {
    header("Location: citas.php?error=no_reprogramable");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reprogramar Cita</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 50px; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        input { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px; }
        .info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reprogramar Cita: <?php echo $cita['codigo']; ?></h2>
        
        <div class="info">
            <p><b>Paciente:</b> <?php echo $cita['paciente']; ?></p>
            <p><b>Médico:</b> <?php echo $cita['medico']; ?></p>
            <p><b>Fecha Actual:</b> <?php echo $cita['fecha'] . " a las " . $cita['hora_inicio']; ?></p>
        </div>

        <form action="reprogramar_procesar.php" method="POST">
            <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
            
            <label><b>Nueva Fecha:</b></label>
            <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
            
            <label><b>Nueva Hora (bloques de 15 min):</b></label>
            <input type="time" name="hora_inicio" required step="900">
            
            <button type="submit">Confirmar Reprogramación</button>
        </form>
        <br>
        <a href="citas.php" style="display:block; text-align:center; color: #0056b3;">Volver a la lista</a>
    </div>
</body>
</html>