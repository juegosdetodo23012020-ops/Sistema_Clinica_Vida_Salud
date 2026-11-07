<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$id_cita = isset($_GET['id']) ? $_GET['id'] : null;
$origen = isset($_GET['origen']) ? $_GET['origen'] : 'citas';

if (!$id_cita) {
    header("Location: " . ($origen == 'mis_citas' ? 'mis_citas.php' : 'citas.php'));
    exit;
}

// Consultamos los datos actuales de la cita
$sql = "SELECT id_cita, codigo, fecha, hora_inicio, paciente, medico, estado, id_medico FROM v_cita_detalle WHERE id_cita = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_cita]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita || ($cita['estado'] != 'PROGRAMADA' && $cita['estado'] != 'CONFIRMADA')) {
    header("Location: " . ($origen == 'mis_citas' ? 'mis_citas.php' : 'citas.php') . "?error=no_reprogramable");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Reprogramar Cita - Clínica Vida Salud</title>
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
        
        <div class="panel-form" style="max-width: 600px; margin: 0 auto;">
            <h2 style="text-align: center;">Reprogramar Cita: <?php echo $cita['codigo']; ?></h2>
            
            <!-- MENSAJES DE ERROR -->
            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 'ocupado'): ?>
                    <div class="alert alert-danger">⚠️ Error: El médico ya tiene otra cita en ese horario. Elija otra hora.</div>
                <?php elseif ($_GET['error'] == 'fuera_horario'): ?>
                    <div class="alert alert-danger">⚠️ Error: La hora seleccionada está fuera del turno de atención del médico.</div>
                <?php elseif ($_GET['error'] == 'fecha_pasada'): ?>
                    <div class="alert alert-danger">⚠️ Error: No puede reprogramar una cita a una fecha u hora que ya pasó.</div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- INFO ACTUAL -->
            <div style="background: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p><b>Paciente:</b> <?php echo htmlspecialchars($cita['paciente']); ?></p>
                <p><b>Médico:</b> <?php echo htmlspecialchars($cita['medico']); ?></p>
                <p><b>Fecha Actual:</b> <?php echo date('d/m/Y', strtotime($cita['fecha'])) . " a las " . substr($cita['hora_inicio'], 0, 5); ?></p>
            </div>

            <form action="reprogramar_procesar.php" method="POST">
                <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
                <!-- ENVIAMOS EL ORIGEN AL PROCESADOR -->
                <input type="hidden" name="origen" value="<?php echo $origen; ?>">
                <!-- Ocultamos el ID del médico para el JavaScript -->
                <input type="hidden" id="id_medico_repro" value="<?php echo $cita['id_medico']; ?>">
                
                <label><b>Nueva Fecha:</b></label>
                <input type="date" name="fecha" id="fecha_cita_repro" required min="<?php echo date('Y-m-d'); ?>">
                
                <label><b>Nueva Hora (Seleccione un bloque):</b></label>
                <input type="time" name="hora_inicio" id="hora_inicio_repro" required step="900" readonly style="background-color: #e9ecef; cursor: not-allowed;">

                <!-- PANEL VISUAL -->
                <div id="panel_horarios_repro" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px;"></div>
                
                <button type="submit" style="background: #ffc107; color: black;">Confirmar Reprogramación</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <?php $volver = ($origen == 'mis_citas') ? 'mis_citas.php' : 'citas.php'; ?>
                <a href="<?php echo $volver; ?>" style="color: #0056b3; text-decoration: none; font-weight: bold;">← Volver</a>
            </div>
        </div>
    </div>

    <!-- SCRIPT PARA EL PANEL VISUAL -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const idMedico = document.getElementById('id_medico_repro').value;
            const inputFecha = document.getElementById('fecha_cita_repro');
            const inputHora = document.getElementById('hora_inicio_repro');
            const panel = document.getElementById('panel_horarios_repro');

            function cargarHorarios() {
                const fecha = inputFecha.value;
                panel.innerHTML = ''; 

                if (!fecha || !idMedico) return;

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
                                btn.className = 'btn-horario-disponible';
                                btn.onclick = function() {
                                    inputHora.value = bloque.hora;
                                    document.querySelectorAll('#panel_horarios_repro button').forEach(b => b.classList.remove('btn-horario-seleccionado'));
                                    btn.classList.add('btn-horario-seleccionado');
                                };
                            } else {
                                btn.className = 'btn-horario-ocupado';
                                btn.disabled = true;
                            }
                            panel.appendChild(btn);
                        });
                    })
                    .catch(error => {
                        panel.innerHTML = '<span style="color:red;">Error al cargar horarios.</span>';
                    });
            }

            inputFecha.addEventListener('change', cargarHorarios);
        });
    </script>

</body>
</html>