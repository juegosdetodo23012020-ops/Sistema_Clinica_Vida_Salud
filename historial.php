<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require 'config/database.php';

// Recibimos el ID del paciente si es que se seleccionó uno
$id_paciente = isset($_POST['id_paciente']) ? $_POST['id_paciente'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Citas - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end; background: #e9ecef; padding: 15px; border-radius: 5px; }
        .filters select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0; width: 300px; }
        .filters button { background-color: #0056b3; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin: 0; width: auto;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .bg-programada { background-color: #cce5ff; color: #004085; }
        .bg-atendida { background-color: #d4edda; color: #155724; }
        .bg-cancelada { background-color: #f8d7da; color: #721c24; }
        .bg-inasistencia { background-color: #fff3cd; color: #856404; }
    </style>
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

    <div class="container">
        <h2>Historial de Citas por Paciente</h2>
        
        <form method="POST" class="filters">
            <div>
                <label>Seleccione un Paciente:</label><br>
                <select name="id_paciente" required>
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
            <button type="submit">Ver Historial</button>
        </form>

        <?php if (!empty($id_paciente)): ?>
            <h3>Historial de Citas</h3>
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
                    // Usamos la vista v_historial_paciente que ya existe en la BD
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
        <?php endif; ?>
    </div>

</body>
</html>