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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Mis Citas - Clínica Vida Salud</title>
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
        
        <!-- MENSAJE DE PAGO EXITOSO -->
        <?php if (isset($_SESSION['mensaje_pago'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; font-weight: bold;">
                <?= $_SESSION['mensaje_pago']; unset($_SESSION['mensaje_pago']); ?>
            </div>
        <?php endif; ?>

        <div class="layout-split">
            
            <!-- COLUMNA IZQUIERDA: FORMULARIO DE RESERVA -->
            <div class="panel-form">
                <h3>Reservar Nueva Cita</h3>    

                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                    <div class="alert alert-success">¡Cita reservada correctamente!</div>
                <?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] == 'cancelada'): ?>
                    <div class="alert alert-success">¡Cita cancelada correctamente!</div>
                <?php elseif (isset($_GET['mensaje']) && $_GET['mensaje'] == 'reprogramada'): ?>
                    <div class="alert alert-success">¡Cita reprogramada correctamente!</div>
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
                    <input type="time" name="hora_inicio" id="hora_inicio" required step="900" readonly style="background-color: #e9ecef; cursor: not-allowed;">

                    <!-- PANEL VISUAL -->
                    <div id="panel_horarios" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;"></div>

                    <label>Modalidad:</label>
                    <select name="modalidad" required>
                        <option value="PRESENCIAL">Presencial</option>
                        <option value="VIRTUAL">Virtual</option>
                    </select>

                    <label>Motivo (Opcional):</label>
                    <textarea name="motivo" rows="2"></textarea>

                    <button type="submit" style="background: var(--primary-gradient);">Reservar Cita</button>
                </form>
            </div>

            <!-- COLUMNA DERECHA: TABLA DE HISTORIAL Y ACCIONES -->
            <div class="panel-table">
                <h3>Mi Historial de Citas</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha/Hora</th>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th>Pago</th> <!-- NUEVA COLUMNA -->
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // CONSULTA MODIFICADA: Unimos con la tabla pago para saber si ya pagó
                            $sql = "SELECT c.id_cita, c.codigo, c.fecha, c.hora_inicio, 
                                           m.nombres || ' ' || m.apellidos AS medico, 
                                           e.nombre AS especialidad, 
                                           c.estado,
                                           p.estado_pago
                                    FROM cita c
                                    JOIN medico m ON c.id_medico = m.id_medico
                                    JOIN especialidad e ON c.id_especialidad = e.id_especialidad
                                    LEFT JOIN pago p ON c.id_cita = p.id_cita AND p.estado_pago = 'APROBADO'
                                    WHERE c.id_paciente = :id_paciente 
                                    ORDER BY c.fecha DESC, c.hora_inicio DESC";
                            
                            $stmt = $conexion->prepare($sql);
                            $stmt->execute([':id_paciente' => $id_paciente]);
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['fecha'] . " " . substr($row['hora_inicio'], 0, 5)) . "</td>";
                                echo "<td>" . htmlspecialchars($row['medico']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                                
                                // NUEVA CELDA DE PAGO
                                echo "<td>";
                                if ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA') {
                                    if (empty($row['estado_pago'])) {
                                        echo "<a href='pagar_cita.php?id=" . $row['id_cita'] . "' style='background-color: #28a745; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px;'>💳 Pagar</a>";
                                    } else {
                                        echo "<span style='color: #28a745; font-weight: bold; font-size: 12px;'>✅ Pagado</span>";
                                    }
                                } else {
                                    echo "<span style='color: gray; font-size: 12px;'>-</span>";
                                }
                                echo "</td>";
                                
                                echo "<td>";
                                if ($row['estado'] == 'PROGRAMADA' || $row['estado'] == 'CONFIRMADA') {
                                    echo "<div style='display: flex; gap: 5px; flex-wrap: wrap;'>";
                                    
                                    echo "<a href='reprogramar_cita.php?id=" . $row['id_cita'] . "&origen=mis_citas' style='background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px;'>Reprogramar</a>";
                                    
                                    echo "<form action='cancelar_cita.php' method='POST' style='margin: 0;' onsubmit='return confirm(\"¿Seguro de cancelar esta cita?\")'>";
                                    echo "<input type='hidden' name='id_cita' value='" . $row['id_cita'] . "'>";
                                    echo "<input type='hidden' name='motivo' value='Cancelada por el paciente desde el portal'>";
                                    echo "<input type='hidden' name='origen' value='mis_citas'>";
                                    echo "<button type='submit' style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;'>Cancelar</button>";
                                    echo "</form>";
                                    
                                    echo "</div>";
                                } else {
                                    echo "<span style='color: gray; font-size: 12px;'>No aplica</span>";
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

            // 2. Cargar panel visual (con las nuevas clases de colores)
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

                            if (bloque.disponible) {
                                btn.className = 'btn-horario-disponible'; // Clase verde
                                btn.onclick = function() {
                                    inputHora.value = bloque.hora;
                                    document.querySelectorAll('#panel_horarios button').forEach(b => b.classList.remove('btn-horario-seleccionado'));
                                    btn.classList.add('btn-horario-seleccionado');
                                };
                            } else {
                                btn.className = 'btn-horario-ocupado'; // Clase roja
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