<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$id_cita = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_cita) {
    header("Location: citas.php");
    exit;
}

// Consultamos los datos actuales de la cita (AGREGAMOS id_medico)
$sql = "SELECT id_cita, codigo, fecha, hora_inicio, paciente, medico, estado, id_medico FROM v_cita_detalle WHERE id_cita = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_cita]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita || ($cita['estado'] != 'PROGRAMADA' && $cita['estado'] != 'CONFIRMADA')) {
    header("Location: citas.php?error=no_reprogramable");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reprogramar Cita</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 50px; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        input { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button[type="submit"] { background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px; margin-top: 15px;}
        .info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reprogramar Cita: <?php echo $cita['codigo']; ?></h2>
        
        <!-- MENSAJES DE ERROR -->
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] == 'ocupado'): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                    ⚠️ Error: El médico ya tiene otra cita en ese horario. Elija otra hora.
                </div>
            <?php elseif ($_GET['error'] == 'fuera_horario'): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                    ⚠️ Error: La hora seleccionada está fuera del turno de atención del médico.
                </div>
            <?php elseif ($_GET['error'] == 'fecha_pasada'): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
                    ⚠️ Error: No puede reprogramar una cita a una fecha u hora que ya pasó.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="info">
            <p><b>Paciente:</b> <?php echo $cita['paciente']; ?></p>
            <p><b>Médico:</b> <?php echo $cita['medico']; ?></p>
            <p><b>Fecha Actual:</b> <?php echo $cita['fecha'] . " a las " . $cita['hora_inicio']; ?></p>
        </div>

        <form action="reprogramar_procesar.php" method="POST">
            <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
            <!-- Ocultamos el ID del médico para el JavaScript -->
            <input type="hidden" id="id_medico_repro" value="<?php echo $cita['id_medico']; ?>">
            
            <label><b>Nueva Fecha:</b></label>
            <input type="date" name="fecha" id="fecha_cita_repro" required min="<?php echo date('Y-m-d'); ?>">
            
            <label><b>Nueva Hora (Seleccione un bloque):</b></label>
            <input type="time" name="hora_inicio" id="hora_inicio_repro" required step="900" readonly>

            <!-- PANEL VISUAL -->
            <div id="panel_horarios_repro" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;"></div>
            
            <button type="submit">Confirmar Reprogramación</button>
        </form>
        <br>
        <a href="citas.php" style="display:block; text-align:center; color: #0056b3;">Volver a la lista</a>
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
                                btn.style.backgroundColor = '#28a745';
                                btn.style.color = 'white';
                                btn.style.cursor = 'pointer';
                                btn.onclick = function() {
                                    inputHora.value = bloque.hora;
                                    document.querySelectorAll('#panel_horarios_repro button').forEach(b => b.style.border = 'none');
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