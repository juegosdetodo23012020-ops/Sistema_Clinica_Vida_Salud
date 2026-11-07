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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Agendar Cita - Clínica Vida Salud</title>
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
        
        <div class="layout-split">
            
            <!-- COLUMNA IZQUIERDA: FORMULARIO DE AGENDAMIENTO -->
            <div class="panel-form">
                <h3>Agendar Nueva Cita</h3>    

                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                    <div class="alert alert-success">¡Cita agendada correctamente!</div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'ocupado'): ?>
                    <div class="alert alert-danger">Error: El médico ya tiene una cita en ese horario.</div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'horario'): ?>
                    <div class="alert alert-danger">Error: La fecha u hora está fuera del horario de atención.</div>
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
                    <select name="id_medico" id="select_medico" required>
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
                    <input type="date" name="fecha" id="fecha_cita" required min="<?php echo date('Y-m-d'); ?>">

                    <label>Hora de Inicio (Seleccione un bloque):</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required step="900">

                    <!-- PANEL VISUAL DE HORARIOS -->
                    <div id="panel_horarios" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;"></div>

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

            <!-- COLUMNA DERECHA: TABLA DE GESTIÓN -->
            <div class="panel-table">
                <h3>Gestión de Citas</h3>
                
                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'cancelada'): ?>
                    <div class="alert alert-success">¡Cita cancelada correctamente! El horario ha sido liberado.</div>
                <?php endif; ?>

                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'reprogramada'): ?>
                    <div class="alert alert-success">¡Cita reprogramada correctamente!</div>
                <?php endif; ?>

                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'estado_cambiado'): ?>
                    <div class="alert alert-success">¡Estado de la cita actualizado correctamente!</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha/Hora</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id_cita, codigo, fecha, hora_inicio, paciente, medico, estado FROM v_cita_detalle ORDER BY fecha DESC, hora_inicio DESC LIMIT 10";
                            $stmt = $conexion->query($sql);
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['fecha'] . " " . $row['hora_inicio']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['medico']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                                
                                echo "<td>";
                                if ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA') {
                                    echo "<div style='display: flex; gap: 5px; align-items: center; flex-wrap: wrap;'>";
                                    
                                    echo "<a href='reprogramar_cita.php?id=" . $row['id_cita'] . "' style='background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 13px; white-space: nowrap;'>Reprogramar</a>";
                                    
                                    echo "<form action='cancelar_cita.php' method='POST' style='margin: 0;' onsubmit='return pedirMotivo(event, this)'>";
                                    echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>";
                                    echo "<input type='hidden' name='origen' value='citas'>";
                                    echo "<button type='submit' style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px;'>Cancelar</button>";
                                    echo "</form>";

                                    echo "<form action='cambiar_estado.php' method='POST' style='margin: 0; display: flex; gap: 5px;'>";
                                    echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>";
                                    echo "<select name='nuevo_estado' required style='padding: 5px; margin: 0; font-size: 12px; width: 110px;'>";
                                    echo "<option value=''>Estado...</option>";
                                    if ($row['estado'] == 'PROGRAMADA') { echo "<option value='CONFIRMADA'>Confirmada</option>"; }
                                    echo "<option value='ATENDIDA'>Atendida</option>";
                                    echo "<option value='INASISTENCIA'>Inasistencia</option>";
                                    echo "</select>";
                                    echo "<button type='submit' style='background-color: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 13px;'>Cambiar</button>";
                                    echo "</form>";
                                    
                                    echo "</div>";
                                } else {
                                    echo "<span style='color: gray; font-size: 13px;'>No aplica</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- Fin layout-split -->
    </div> <!-- Fin main-content -->

    <!-- SCRIPT PARA EL PANEL VISUAL Y MODALES -->
    <script>
        // 1. FUNCIÓN PARA EL MOTIVO DE CANCELACIÓN
        function pedirMotivo(event, form) {
            event.preventDefault();
            let motivo = prompt("¿Cuál es el motivo de la cancelación?");
            if (motivo === null || motivo.trim() === "") {
                alert("Debe ingresar un motivo para cancelar la cita.");
                return false;
            }
            let inputMotivo = form.querySelector('input[name="motivo"]');
            inputMotivo.value = motivo;
            form.submit();
        }

        // 2. LÓGICA DEL PANEL VISUAL (AJAX)
        document.addEventListener("DOMContentLoaded", function() {
            const selectMedico = document.getElementById('select_medico');
            const inputFecha = document.getElementById('fecha_cita');
            const inputHora = document.getElementById('hora_inicio');
            const panel = document.getElementById('panel_horarios');

            function cargarHorarios() {
                const idMedico = selectMedico.value;
                const fecha = inputFecha.value;

                panel.innerHTML = ''; // Limpiar panel

                if (!idMedico || !fecha) return;

                panel.innerHTML = '<span style="color:gray;">Cargando horarios...</span>';

                fetch(`api_horarios.php?id_medico=${idMedico}&fecha=${fecha}`)
                    .then(response => response.json())
                    .then(data => {
                        panel.innerHTML = ''; 

                        if (data.error) {
                            panel.innerHTML = `<span style="color:red;">${data.error}</span>`;
                            return;
                        }

                        if (data.length === 0) {
                            panel.innerHTML = '<span style="color:red;">El médico no atiende este día.</span>';
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
                                btn.className = 'btn-horario-disponible'; // Usamos la clase verde
                                btn.onclick = function() {
                                    inputHora.value = bloque.hora;
                                    document.querySelectorAll('#panel_horarios_repro button, #panel_horarios button').forEach(b => b.classList.remove('btn-horario-seleccionado'));
                                    btn.classList.add('btn-horario-seleccionado');
                                };
                            } else {
                                btn.className = 'btn-horario-ocupado'; // Usamos la clase roja
                                btn.disabled = true;
                            }
                            panel.appendChild(btn);
                        });
                    })
                    .catch(error => {
                        panel.innerHTML = '<span style="color:red;">Error al conectar con el servidor.</span>';
                        console.error(error);
                    });
            }

            // Escuchar cambios
            selectMedico.addEventListener('change', cargarHorarios);
            inputFecha.addEventListener('change', cargarHorarios);
        });
    </script>

</body>
</html>