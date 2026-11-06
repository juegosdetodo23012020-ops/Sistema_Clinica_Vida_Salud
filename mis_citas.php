<?php
session_start();
require 'config/database.php';

// Seguridad: Solo el paciente (Rol 4) puede entrar aquí
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 4) {
    header("Location: dashboard.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// 1. Buscamos el ID del paciente asociado a este usuario
$stmt_pac = $conexion->prepare("SELECT id_paciente, nombres, apellidos FROM paciente WHERE id_usuario = :id_usuario");
$stmt_pac->execute([':id_usuario' => $id_usuario]);
$paciente = $stmt_pac->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Error: No se encontró la ficha de paciente para este usuario.");
}

$id_paciente = $paciente['id_paciente'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; flex-wrap: wrap; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); min-width: 300px;}
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); min-width: 400px;}
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button[type="submit"] { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
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
        <!-- FORMULARIO PARA RESERVAR NUEVA CITA -->
        <div class="form-section">
            <h3>Reservar Nueva Cita</h3>    

            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Cita reservada correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'ocupado'): ?>
                <div class="alert alert-danger">Error: El médico ya tiene una cita en ese horario.</div>
            <?php endif; ?>

            <form action="mis_citas_procesar.php" method="POST">
                <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">

                <label>Especialidad:</label>
                <select id="select_especialidad" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $esp_sql = "SELECT id_especialidad, nombre FROM especialidad WHERE estado = true ORDER BY nombre";
                    $esp_stmt = $conexion->query($esp_sql);
                    while ($esp = $esp_stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $esp['id_especialidad'] . "'>" . htmlspecialchars($esp['nombre']) . "</option>";
                    }
                    ?>
                </select>

                <label>Médico:</label>
                <select name="id_medico" id="select_medico" required>
                    <option value="">Seleccione especialidad primero...</option>
                </select>

                <label>Fecha de la Cita:</label>
                <input type="date" name="fecha" id="fecha_cita" required min="<?php echo date('Y-m-d'); ?>">

                <label>Hora (Seleccione un bloque):</label>
                <input type="time" name="hora_inicio" id="hora_inicio" required step="900" readonly>

                <!-- PANEL VISUAL -->
                <div id="panel_horarios" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;"></div>

                <label>Modalidad:</label>
                <select name="modalidad" required>
                    <option value="PRESENCIAL">Presencial</option>
                    <option value="VIRTUAL">Virtual</option>
                </select>

                <label>Motivo (Opcional):</label>
                <textarea name="motivo" rows="2"></textarea>

                <button type="submit">Reservar Cita</button>
            </form>
        </div>

        <!-- TABLA DE MIS CITAS -->
        <div class="table-section">
            <h3>Mi Historial de Citas</h3>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha/Hora</th>
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
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['fecha'] . " " . $row['hora_inicio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['medico']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SCRIPT PARA FILTRO DE MÉDICOS Y PANEL VISUAL -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectEspecialidad = document.getElementById('select_especialidad');
            const selectMedico = document.getElementById('select_medico');
            const inputFecha = document.getElementById('fecha_cita');
            const inputHora = document.getElementById('hora_inicio');
            const panel = document.getElementById('panel_horarios');

            // 1. Filtrar médicos por especialidad
            selectEspecialidad.addEventListener('change', function() {
                const idEsp = this.value;
                selectMedico.innerHTML = '<option value="">Cargando...</option>';
                panel.innerHTML = '';
                
                if (!idEsp) {
                    selectMedico.innerHTML = '<option value="">Seleccione especialidad primero...</option>';
                    return;
                }

                fetch(`api_medicos.php?id_especialidad=${idEsp}`)
                    .then(response => response.json())
                    .then(data => {
                        selectMedico.innerHTML = '<option value="">Seleccione médico...</option>';
                        data.forEach(med => {
                            const option = document.createElement('option');
                            option.value = med.id_medico;
                            option.textContent = `Dr(a). ${med.apellidos}, ${med.nombres}`;
                            selectMedico.appendChild(option);
                        });
                    });
            });

            // 2. Cargar panel visual
            function cargarHorarios() {
                const idMedico = selectMedico.value;
                const fecha = inputFecha.value;
                panel.innerHTML = ''; 

                if (!idMedico || !fecha) return;

                panel.innerHTML = '<span style="color:gray;">Cargando horarios...</span>';

                fetch(`api_horarios.php?id_medico=${idMedico}&fecha=${fecha}`)
                    .then(response => response.json())
                    .then(data => {
                        panel.innerHTML = ''; 
                        if (data.error) {
                            panel.innerHTML = `<span style="color:red; font-weight:bold;">${data.error}</span>`;
                            return;
                        }
                        data.forEach(bloque => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.textContent = bloque.hora;
                            btn.style.padding = '8px 12px';
                            btn.style.border = 'none';
                            btn.style.borderRadius = '5px';
                            btn.style.fontWeight = 'bold';

                            if (bloque.disponible) {
                                btn.style.backgroundColor = '#28a745';
                                btn.style.color = 'white';
                                btn.style.cursor = 'pointer';
                                btn.onclick = function() {
                                    inputHora.value = bloque.hora;
                                    document.querySelectorAll('#panel_horarios button').forEach(b => b.style.border = 'none');
                                    btn.style.border = '3px solid #0056b3';
                                };
                            } else {
                                btn.style.backgroundColor = '#dc3545';
                                btn.style.color = 'white';
                                btn.style.cursor = 'not-allowed';
                                btn.style.opacity = '0.6';
                                btn.disabled = true;
                            }
                            panel.appendChild(btn);
                        });
                    });
            }

            selectMedico.addEventListener('change', cargarHorarios);
            inputFecha.addEventListener('change', cargarHorarios);
        });
    </script>
</body>
</html>