<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require 'config/database.php';

$id_paciente = isset($_POST['id_paciente']) ? $_POST['id_paciente'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Historial de Citas - Clínica Vida Salud</title>
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
        <h2>Historial de Citas por Paciente</h2>
        
        <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div style="flex: 1; min-width: 250px;">
                <label>Seleccione un Paciente:</label>
                <select name="id_paciente" required style="margin-bottom: 0;">
                    <option value="">Buscar paciente...</option>
                    <?php
                    $pac_sql = "SELECT id_paciente, apellidos, nombres, ci FROM paciente ORDER BY apellidos";
                    $pac_stmt = $conexion->query($pac_sql);
                    while ($pac = $pac_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($id_paciente == $pac['id_paciente']) ? 'selected' : '';
                        echo "<option value='{$pac['id_paciente']}' $selected>{$pac['apellidos']}, {$pac['nombres']} (CI: {$pac['ci']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <button type="submit" style="width: auto; padding: 10px 25px;">Ver Historial</button>
            </div>
        </form>

        <?php if (!empty($id_paciente)): ?>
            <div class="panel-table">
                <h3>Historial de Citas</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Código</th>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT codigo, fecha, hora_inicio, medico, especialidad, estado 
                                    FROM v_historial_paciente 
                                    WHERE id_paciente = :id_paciente 
                                    ORDER BY fecha DESC, hora_inicio DESC";
                            $stmt = $conexion->prepare($sql);
                            $stmt->execute([':id_paciente' => $id_paciente]);
                            
                            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($citas) > 0) {
                                foreach ($citas as $row) {
                                    $badge_class = 'bg-programada';
                                    if ($row['estado'] == 'ATENDIDA') $badge_class = 'bg-atendida';
                                    if ($row['estado'] == 'CANCELADA') $badge_class = 'bg-cancelada';
                                    if ($row['estado'] == 'INASISTENCIA') $badge_class = 'bg-inasistencia';

                                    echo "<tr>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
                                    echo "<td>" . substr($row['hora_inicio'], 0, 5) . "</td>";
                                    echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                                    echo "<td>" . htmlspecialchars($row['medico']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
                                    echo "<td><span class='badge $badge_class'>" . htmlspecialchars($row['estado']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>Este paciente no tiene historial de citas.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>