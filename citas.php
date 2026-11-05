<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require 'config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Cita - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        button:hover { background-color: #218838; }
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
        <!-- FORMULARIO DE AGENDAMIENTO -->
        <div class="form-section">
            <h3>Agendar Nueva Cita</h3>    

              <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Cita agendada correctamente!</div>
             <?php elseif (isset($_GET['error']) && $_GET['error'] == 'ocupado'): ?>
                <div class="alert alert-danger">Error: El médico ya tiene una cita en ese horario (Cruce de horarios).</div>
             <?php elseif (isset($_GET['error']) && $_GET['error'] == 'horario'): ?>
                <div class="alert alert-danger">Error: La fecha u hora está fuera del horario de atención del médico.</div>
             <?php elseif (isset($_GET['error']) && $_GET['error'] == 'sistema'): ?>
                <div class="alert alert-danger">Error del sistema al agendar la cita.</div>
             <?php endif; ?>

            <form action="citas_procesar.php" method="POST">
                <label>Paciente:</label>
                <select name="id_paciente" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $pac_sql = "SELECT id_paciente, apellidos, nombres, ci FROM paciente WHERE estado = true ORDER BY apellidos";
                    $pac_stmt = $conexion->query($pac_sql);
                    while ($pac = $pac_stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $pac['id_paciente'] . "'>" . htmlspecialchars($pac['apellidos'] . ", " . $pac['nombres'] . " (CI: ".$pac['ci'].")") . "</option>";
                    }
                    ?>
                </select>

                <label>Médico:</label>
                <select name="id_medico" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $med_sql = "SELECT id_medico, apellidos, nombres FROM medico WHERE estado = true ORDER BY apellidos";
                    $med_stmt = $conexion->query($med_sql);
                    while ($med = $med_stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $med['id_medico'] . "'>" . htmlspecialchars("Dr(a). " . $med['apellidos'] . ", " . $med['nombres']) . "</option>";
                    }
                    ?>
                </select>

                <label>Fecha de la Cita:</label>
                <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">

                <label>Hora de Inicio:</label>
                <input type="time" name="hora_inicio" required step="900"> <!-- step="900" permite intervalos de 15 minutos --> 

                <label>Modalidad:</label>
                <select name="modalidad" required>
                    <option value="PRESENCIAL">Presencial</option>
                    <option value="VIRTUAL">Virtual</option>
                </select>

                <label>Motivo (Opcional):</label>
                <textarea name="motivo" rows="2"></textarea>

                <button type="submit">Registrar Cita</button>
            </form>
        </div>

        <!-- TABLA DE ÚLTIMAS CITAS -->
                <!-- TABLA DE GESTIÓN DE CITAS (Modificada para Ciclo 2) -->
        <div class="table-section">
            <h3>Gestión de Citas</h3>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'cancelada'): ?>
                <div class="alert alert-success">¡Cita cancelada correctamente! El horario ha sido liberado.</div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha/Hora</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Estado</th>
                        <th>Acciones (Cancelar)</th> <!-- NUEVA COLUMNA -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Agregamos 'id_cita' a la consulta para poder usarlo en el formulario
                    $sql = "SELECT id_cita, codigo, fecha, hora_inicio, paciente, medico, estado FROM v_cita_detalle ORDER BY fecha DESC, hora_inicio DESC LIMIT 10";
                    $stmt = $conexion->query($sql);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<td style='display:flex; gap:5px;'>";
                        // Solo mostramos los botones si la cita está activa
                        if ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA') {
                            // Botón de Reprogramar
                            echo "<a href='reprogramar_cita.php?id=" . $row['id_cita'] . "' style='background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold;'>Reprogramar</a>";
                            
                            // Botón de Cancelar (Formulario)
                            echo "<form action='cancelar_cita.php' method='POST' style='margin:0;' onsubmit='return confirm(\"¿Seguro de cancelar?\")'>";
                            echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>";
                            echo "<input type='text' name='motivo' placeholder='Motivo...' required style='padding: 5px; width: 100px; margin:0;'>";
                            echo "<button type='submit' style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold;'>Cancelar</button>";
                            echo "</form>";
                        } else {
                            echo "<span style='color:gray;'>No aplica</span>";
                        }
                        echo "</td>";
                       
                        // Solo mostramos el botón si la cita está activa
                        if ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA') {
                            echo "<form action='cancelar_cita.php' method='POST' style='display:flex; gap:5px; margin:0;' onsubmit='return confirm(\"¿Estás seguro de cancelar esta cita?\")'>";
                            echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>";
                            echo "<input type='text' name='motivo' placeholder='Motivo...' required style='padding: 5px; width: 120px; margin:0;'>";
                            echo "<button type='submit' style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; width: auto; font-weight: bold;'>Cancelar</button>";
                            echo "</form>";
                        } else {
                            echo "<span style='color:gray;'>No aplica</span>";
                        }
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>