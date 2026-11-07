<?php
session_start();
require 'config/database.php';

// Solo Admin (1) y Recepcionista (2) pueden ver esto
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol_id'] != 1 && $_SESSION['rol_id'] != 2)) {
    header("Location: dashboard.php");
    exit;
}

// Lógica para reenviar UNA notificación
if (isset($_POST['reenviar_id'])) {
    $id_notif = $_POST['reenviar_id'];
    $stmt = $conexion->prepare("UPDATE notificacion SET estado_envio = 'ENVIADA', fecha_envio = NOW() WHERE id_notificacion = :id");
    $stmt->execute([':id' => $id_notif]);
    header("Location: notificaciones.php?mensaje=reenviado");
    exit;
}

// Lógica para reenviar TODAS las pendientes (Operación en Lote)
if (isset($_POST['enviar_todos'])) {
    // Actualizamos todas las notificaciones que estén PENDIENTES o FALLIDAS
    $stmt = $conexion->prepare("UPDATE notificacion SET estado_envio = 'ENVIADA', fecha_envio = NOW() WHERE estado_envio IN ('PENDIENTE', 'FALLIDA')");
    $stmt->execute();
    header("Location: notificaciones.php?mensaje=masivo");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Notificaciones - Clínica Vida Salud</title>
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
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <div>
                <h2>Gestión de Recordatorios</h2>
                <p>El sistema genera automáticamente una notificación 24 horas antes de cada cita.</p>
            </div>
            
            <!-- BOTÓN PARA ENVIAR TODOS -->
            <form method="POST" onsubmit="return confirm('¿Estás seguro de marcar TODAS las notificaciones pendientes como enviadas?');">
                <button type="submit" name="enviar_todos" style="background: var(--success); color: black; padding: 12px 20px; font-size: 15px; width: auto;">
                     Reenviar Todos los Pendientes
                </button>
            </form>
        </div>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'reenviado'): ?>
            <div class="alert alert-success">¡Notificación marcada como ENVIADA correctamente!</div>
        <?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] == 'masivo'): ?>
            <div class="alert alert-success">¡Todas las notificaciones pendientes fueron marcadas como ENVIADAS!</div>
        <?php endif; ?>

        <div class="panel-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cita</th>
                            <th>Mensaje</th>
                            <th>Canal</th>
                            <th>Fecha Programada</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT n.id_notificacion, c.codigo AS cod_cita, n.mensaje, n.canal, n.fecha_programada, n.estado_envio 
                                FROM notificacion n
                                JOIN cita c ON n.id_cita = c.id_cita
                                ORDER BY n.fecha_programada DESC";
                        $stmt = $conexion->query($sql);
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $badge_class = 'bg-pendiente';
                            if ($row['estado_envio'] == 'ENVIADA') $badge_class = 'bg-enviada';
                            if ($row['estado_envio'] == 'ANULADA') $badge_class = 'bg-anulada';

                            echo "<tr>";
                            echo "<td>" . $row['id_notificacion'] . "</td>";
                            echo "<td><b>" . htmlspecialchars($row['cod_cita']) . "</b></td>";
                            echo "<td>" . htmlspecialchars($row['mensaje']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['canal']) . "</td>";
                            echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_programada'])) . "</td>";
                            echo "<td><span class='badge $badge_class'>" . $row['estado_envio'] . "</span></td>";
                            
                            echo "<td>";
                            if ($row['estado_envio'] == 'PENDIENTE' || $row['estado_envio'] == 'FALLIDA') {
                                echo "<form method='POST' style='margin:0;'>";
                                echo "<input type='hidden' name='reenviar_id' value='" . $row['id_notificacion'] . "'>";
                                echo "<button type='submit' style='background-color: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px;'>Reenviar</button>";
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