<?php
session_start();
require 'config/database.php';

// Seguridad: Solo el paciente (Rol 4) puede entrar aquí
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 4) {
    header("Location: dashboard.php");
    exit;
}

$id_cita = $_GET['id'] ?? 0;

// Buscamos los datos de la cita para mostrarlos en el resumen
$sql = "SELECT c.id_cita, c.codigo, c.fecha, c.hora_inicio, m.nombres as medico, e.nombre as especialidad 
        FROM cita c
        JOIN medico m ON c.id_medico = m.id_medico
        JOIN especialidad e ON c.id_especialidad = e.id_especialidad
        WHERE c.id_cita = :id AND c.id_paciente = (SELECT id_paciente FROM paciente WHERE id_usuario = :user_id)";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_cita, ':user_id' => $_SESSION['usuario_id']]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita) { die("Cita no encontrada o no tienes permiso."); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasarela de Pago - Clínica Vida Salud</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos específicos para simular la pasarela */
        .gateway-box { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #28a745; }
        .card-input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px;}
        .btn-pay { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; margin-top: 10px;}
        .btn-pay:hover { background: #218838; }
        .summary-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #e9ecef;}
    </style>
</head>
<body style="background-color: #f4f6f9;">

    <div class="gateway-box">
        <h2 style="text-align: center; color: #28a745; margin-top: 0;">💳 Pasarela de Pago Segura</h2>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 20px;">Simulación de Checkout (Modo Sandbox)</p>
        
        <div class="summary-box">
            <p><strong>Concepto:</strong> Consulta de <?= htmlspecialchars($cita['especialidad']) ?></p>
            <p><strong>Médico:</strong> Dr(a). <?= htmlspecialchars($cita['medico']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($cita['fecha'])) ?> a las <?= date('H:i', strtotime($cita['hora_inicio'])) ?></p>
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 10px 0;">
            <h3 style="text-align: center; color: #0056b3; margin: 0;">Total a Pagar: Bs150.00</h3>
        </div>
        
        <form action="pagar_procesar.php" method="POST">
            <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">
            
            <label style="font-weight: bold;">Número de Tarjeta:</label>
            <input type="text" class="card-input" value="4111 1111 1111 1111" placeholder="4111 1111 1111 1111" required>
            
            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label style="font-weight: bold;">Expiración:</label>
                    <input type="text" class="card-input" value="12/25" placeholder="MM/YY" required>
                </div>
                <div style="flex: 1;">
                    <label style="font-weight: bold;">CVV:</label>
                    <input type="text" class="card-input" value="123" placeholder="123" required>
                </div>
            </div>
            
            <button type="submit" class="btn-pay">Confirmar Pago de Bs150.00</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="mis_citas.php" style="color: #666; text-decoration: none; font-size: 14px;">Cancelar y Volver</a>
        </div>
    </div>

</body>
</html>