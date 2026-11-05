<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require 'config/database.php';

$rol_id = $_SESSION['rol_id'];
$id_usuario = $_SESSION['usuario_id'];

// 1. Lógica para saber qué médico mostrar
if ($rol_id == 3) {
    // Si es Médico, buscamos su propio ID de médico
    $stmt = $conexion->prepare("SELECT id_medico, nombres, apellidos FROM medico WHERE id_usuario = :id_usuario");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $mi_medico = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_medico_filtro = $mi_medico['id_medico'];
    $nombre_medico_filtro = "Dr(a). " . $mi_medico['nombres'] . " " . $mi_medico['apellidos'];
} else {
    // Si es Admin o Recepcionista, pueden seleccionar a cualquier médico
    $id_medico_filtro = isset($_POST['id_medico']) ? $_POST['id_medico'] : '';
    $nombre_medico_filtro = "Seleccione un médico";
}

// 2. Fecha por defecto (hoy) o la que seleccione el usuario
$fecha_filtro = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda Médica - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end; background: #e9ecef; padding: 15px; border-radius: 5px; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0; }
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
        <h2>Agenda Médica</h2>
        
        <form method="POST" class="filters">
            <?php if ($rol_id != 3): ?>
                <!-- Si es Admin/Recepción, mostramos la lista de médicos -->
                <div>
                    <label>Médico:</label><br>
                    <select name="id_medico" required>
                        <option value="">Seleccione...</option>
                        <?php
                        $med_sql = "SELECT id_medico, apellidos, nombres FROM medico WHERE estado = true";
                        $med_stmt = $conexion->query($med_sql);
                        while ($med = $med_stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($id_medico_filtro == $med['id_medico']) ? 'selected' : '';
                            echo "<option value='{$med['id_medico']}' $selected>Dr(a). {$med['apellidos']}, {$med['nombres']}</option>";
                        }
                        ?>
                    </select>
                </div>
            <?php else: ?>
                <!-- Si es Médico, mostramos su nombre fijo -->
                <div>
                    <label>Médico:</label><br>
                    <input type="text" value="<?php echo $nombre_medico_filtro; ?>" readonly style="background:#ccc;">
                </div>
            <?php endif; ?>

            <div>
                <label>Fecha:</label><br>
                <input type="date" name="fecha" value="<?php echo $fecha_filtro; ?>" required>
            </div>
            
            <button type="submit">Consultar</button>
        </form>

        <?php if (!empty($id_medico_filtro)): ?>
            <h3>Citas para el <?php echo date('d/m/Y', strtotime($fecha_filtro)); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Código</th>
                        <th>Paciente</th>
                        <th>Especialidad</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultamos la vista v_cita_detalle
                    $sql = "SELECT codigo, hora_inicio, paciente, especialidad, modalidad, estado, motivo 
                            FROM v_cita_detalle 
                            WHERE id_medico = :id_medico AND fecha = :fecha 
                            ORDER BY hora_inicio ASC";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':id_medico' => $id_medico_filtro,
                        ':fecha' => $fecha_filtro
                    ]);
                    
                    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($citas) > 0) {
                        foreach ($citas as $row) {
                            // Asignamos un color al badge según el estado
                            $badge_class = 'bg-programada';
                            if ($row['estado'] == 'ATENDIDA') $badge_class = 'bg-atendida';
                            if ($row['estado'] == 'CANCELADA') $badge_class = 'bg-cancelada';
                            if ($row['estado'] == 'INASISTENCIA') $badge_class = 'bg-inasistencia';

                            echo "<tr>";
                            echo "<td><b>" . substr($row['hora_inicio'], 0, 5) . "</b></td>";
                            echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['modalidad']) . "</td>";
                            echo "<td><span class='badge $badge_class'>" . htmlspecialchars($row['estado']) . "</span></td>";
                            echo "<td>" . htmlspecialchars($row['motivo'] ?? '-') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>No hay citas agendadas para este día.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>