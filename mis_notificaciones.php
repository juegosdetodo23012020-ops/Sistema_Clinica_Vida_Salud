<?php
session_start();
require 'config/database.php';

// Seguridad: Solo el paciente (Rol 4) puede entrar aquí
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 4) {
    header("Location: dashboard.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Buscamos el ID del paciente
$stmt_pac = $conexion->prepare("SELECT id_paciente FROM paciente WHERE id_usuario = :id_usuario");
$stmt_pac->execute([':id_usuario' => $id_usuario]);
$pac = $stmt_pac->fetch(PDO::FETCH_ASSOC);

if (!$pac) {
    die("Error: Perfil de paciente no encontrado.");
}
$id_paciente = $pac['id_paciente'];

// Lógica para marcar como leída
if (isset($_POST['marcar_leida'])) {
    $id_notif = $_POST['id_notificacion'];
    $stmt = $conexion->prepare("UPDATE notificacion SET leida = true WHERE id_notificacion = :id");
    $stmt->execute([':id' => $id_notif]);
    header("Location: mis_notificaciones.php?mensaje=leida");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Mis Notificaciones - Clínica Vida Salud</title>
</head>
<body>

    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <span>Bienvenido, <?php echo $_SESSION['nombre_usuario']; ?></span> | 
            <a href="dashboard.php">Inicio</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="main-content">
        <h2>Mis Recordatorios</h2>
        <p style="margin-bottom: 20px;">Aquí puedes ver los avisos de tus próximas citas.</p>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'leida'): ?>
            <div class="alert alert-success">¡Recordatorio marcado como leído!</div>
        <?php endif; ?>

        <div class="panel-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mensaje</th>
                            <th>Fecha del Recordatorio</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT n.id_notificacion, n.mensaje, n.fecha_programada, n.leida 
                                FROM notificacion n
                                JOIN cita c ON n.id_cita = c.id_cita
                                WHERE c.id_paciente = :id_paciente 
                                AND n.estado_envio != 'ANULADA'
                                ORDER BY n.fecha_programada DESC";
                        
                        $stmt = $conexion->prepare($sql);
                        $stmt->execute([':id_paciente' => $id_paciente]);
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $es_leida = $row['leida'] ? true : false;
                            $badge_class = $es_leida ? 'bg-leida' : 'bg-no-leida';
                            $estado_texto = $es_leida ? 'LEÍDO' : 'NUEVO';

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['mensaje']) . "</td>";
                            echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_programada'])) . "</td>";
                            echo "<td><span class='badge $badge_class'>$estado_texto</span></td>";
                            
                            echo "<td>";
                            if (!$es_leida) {
                                echo "<form method='POST' style='margin:0;'>";
                                echo "<input type='hidden' name='id_notificacion' value='" . $row['id_notificacion'] . "'>";
                                echo "<button type='submit' name='marcar_leida' style='background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px;'>Marcar como Leída</button>";
                                echo "</form>";
                            } else {
                                echo "<span style='color: gray;'>-</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>