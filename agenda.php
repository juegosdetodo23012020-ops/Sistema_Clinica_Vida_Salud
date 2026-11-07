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
    $stmt = $conexion->prepare("SELECT id_medico, nombres, apellidos FROM medico WHERE id_usuario = :id_usuario");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $mi_medico = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_medico_filtro = $mi_medico['id_medico'];
    $nombre_medico_filtro = "Dr(a). " . $mi_medico['nombres'] . " " . $mi_medico['apellidos'];
} else {
    $id_medico_filtro = isset($_POST['id_medico']) ? $_POST['id_medico'] : '';
    $nombre_medico_filtro = "Seleccione un médico";
}

$fecha_filtro = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Agenda Médica - Clínica Vida Salud</title>
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
        <h2>Agenda Médica</h2>
        
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'estado_cambiado'): ?>
            <div class="alert alert-success">¡Estado de la cita actualizado correctamente!</div>
        <?php endif; ?>

        <!-- FILTROS DE BÚSQUEDA -->
        <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <?php if ($rol_id != 3): ?>
                <div style="flex: 1; min-width: 250px;">
                    <label>Médico:</label>
                    <select name="id_medico" required style="margin-bottom: 0;">
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
                <div style="flex: 1; min-width: 250px;">
                    <label>Médico:</label>
                    <input type="text" value="<?php echo $nombre_medico_filtro; ?>" readonly style="background:#e9ecef; margin-bottom: 0;">
                </div>
            <?php endif; ?>

            <div style="flex: 1; min-width: 200px;">
                <label>Fecha:</label>
                <input type="date" name="fecha" value="<?php echo $fecha_filtro; ?>" required style="margin-bottom: 0;">
            </div>
            
            <div>
                <button type="submit" style="width: auto; padding: 10px 25px;">Consultar</button>
            </div>
        </form>

        <?php if (!empty($id_medico_filtro)): ?>
            <div class="panel-table">
                <h3>Citas para el <?php echo date('d/m/Y', strtotime($fecha_filtro)); ?></h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Código</th>
                                <th>Paciente</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th>Acciones (Médico)</th> <!-- NUEVA COLUMNA -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Agregamos 'id_cita' a la consulta
                            $sql = "SELECT id_cita, codigo, hora_inicio, paciente, especialidad, modalidad, estado, motivo 
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
                                    $badge_class = 'bg-programada';
                                    if ($row['estado'] == 'ATENDIDA') $badge_class = 'bg-atendida';
                                    if ($row['estado'] == 'CANCELADA') $badge_class = 'bg-cancelada';
                                    if ($row['estado'] == 'INASISTENCIA') $badge_class = 'bg-inasistencia';

                                    echo "<tr>";
                                    echo "<td><b>" . substr($row['hora_inicio'], 0, 5) . "</b></td>";
                                    echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
                                    echo "<td><span class='badge $badge_class'>" . htmlspecialchars($row['estado']) . "</span></td>";
                                    
                                    // LÓGICA DE ACCIONES (Solo para el médico)
                                    echo "<td>";
                                    if ($rol_id == 3 && ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA')) {
                                       
                                        echo "<form action='cambiar_estado.php' method='POST' style='margin: 0; display: flex; gap: 5px;'>";
                                        echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>"; // <-- ESTA ES LA QUE FALTABA
                                        echo "<input type='hidden' name='origen' value='agenda'>";
                                        echo "<select name='nuevo_estado' required style='padding: 5px; margin: 0; font-size: 12px; width: 110px;'>";
                                        echo "<option value=''>Seleccione...</option>";
                                        echo "<option value='ATENDIDA'>Atendida</option>";
                                        echo "<option value='INASISTENCIA'>Inasistencia</option>";
                                        echo "</select>";
                                        echo "<button type='submit' style='background-color: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px;'>Guardar</button>";
                                        echo "</form>";
                                    } else {
                                        echo "<span style='color: gray; font-size: 12px;'>-</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>No hay citas agendadas para este día.</td></tr>";
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