<?php
session_start();
// Solo el Administrador puede ver esto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: dashboard.php");
    exit;
}
require 'config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horarios - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
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
        <div class="form-section">
            <h3>Asignar Horario a Médico</h3>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Horario registrado correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'cruce'): ?>
                <div class="alert alert-danger">Error: El médico ya tiene un horario en ese día (Cruce de horas).</div>
            <?php endif; ?>

            <form action="horarios_procesar.php" method="POST">
                <label>Médico:</label>
                <select name="id_medico" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $stmt = $conexion->query("SELECT id_medico, apellidos, nombres FROM medico WHERE estado = true");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id_medico']}'>Dr(a). {$row['apellidos']}, {$row['nombres']}</option>";
                    }
                    ?>
                </select>

                <label>Día de la Semana:</label>
                <select name="dia_semana" required>
                    <option value="LUNES">Lunes</option>
                    <option value="MARTES">Martes</option>
                    <option value="MIERCOLES">Miércoles</option>
                    <option value="JUEVES">Jueves</option>
                    <option value="VIERNES">Viernes</option>
                    <option value="SABADO">Sábado</option>
                </select>

                <label>Hora de Inicio:</label>
                <input type="time" name="hora_inicio" required step="900">

                <label>Hora de Fin:</label>
                <input type="time" name="hora_fin" required step="900">

                <button type="submit">Guardar Horario</button>
            </form>
        </div>

        <div class="table-section">
            <h3>Horarios Registrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Médico</th>
                        <th>Día</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT m.apellidos, m.nombres, h.dia_semana, h.hora_inicio, h.hora_fin 
                            FROM horario_atencion h
                            JOIN medico m ON h.id_medico = m.id_medico
                            ORDER BY m.apellidos, h.dia_semana";
                    $stmt = $conexion->query($sql);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td><b>Dr(a). " . htmlspecialchars($row['apellidos'] . ", " . $row['nombres']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['dia_semana']) . "</td>";
                        echo "<td>" . htmlspecialchars(substr($row['hora_inicio'], 0, 5)) . "</td>";
                        echo "<td>" . htmlspecialchars(substr($row['hora_fin'], 0, 5)) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>