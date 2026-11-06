<?php
// 1. INICIAR SESIÓN
session_start();
// 1.1 Conexión a la base de datos
require 'config/database.php';

// 2. CONTROL DE SEGURIDAD
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// 3. RECUPERAR DATOS
$rol = $_SESSION['rol_id'];
$usuario = $_SESSION['nombre_usuario'];
// LÓGICA PARA EL WIDGET DE PRÓXIMA CITA (Solo para pacientes)
$proxima_cita = null;
$total_pendientes = 0;

    if ($rol == 4) {
    // Buscamos el ID del paciente
    $stmt_pac = $conexion->prepare("SELECT id_paciente FROM paciente WHERE id_usuario = :id_usuario");
    $stmt_pac->execute([':id_usuario' => $_SESSION['usuario_id']]);
    $pac = $stmt_pac->fetch(PDO::FETCH_ASSOC);

    if ($pac) {
        $id_pac = $pac['id_paciente'];
        
        // Buscamos la próxima cita
        $sql_prox = "SELECT codigo, fecha, hora_inicio, medico, especialidad 
                     FROM v_cita_detalle 
                     WHERE id_paciente = :id_paciente 
                     AND estado IN ('PROGRAMADA', 'CONFIRMADA') 
                     AND (fecha > CURRENT_DATE OR (fecha = CURRENT_DATE AND hora_inicio > CURRENT_TIME))
                     ORDER BY fecha ASC, hora_inicio ASC LIMIT 1";
        $stmt_prox = $conexion->prepare($sql_prox);
        $stmt_prox->execute([':id_paciente' => $id_pac]);
        $proxima_cita = $stmt_prox->fetch(PDO::FETCH_ASSOC);

        // Contamos cuántas citas pendientes tiene
        $sql_count = "SELECT COUNT(*) FROM v_cita_detalle WHERE id_paciente = :id_paciente AND estado IN ('PROGRAMADA', 'CONFIRMADA') AND (fecha > CURRENT_DATE OR (fecha = CURRENT_DATE AND hora_inicio > CURRENT_TIME))";
        $stmt_count = $conexion->prepare($sql_count);
        $stmt_count->execute([':id_paciente' => $id_pac]);
        $total_pendientes = $stmt_count->fetchColumn();
    }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; margin-left: 15px; }
        .container { padding: 40px; }
        h1 { color: #333; margin-bottom: 20px; }
        
        /* 4. DISEÑO DE LA CUADRÍCULA (GRID) PARA LOS BOTONES */
        .menu-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 20px; 
        }
        
        /* 5. ESTILO DE LAS TARJETAS (BOTONES) */
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            text-decoration: none; 
            color: #333; 
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border-top: 5px solid #0056b3;
        }
        .card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 6px 12px rgba(0,0,0,0.15); 
            background-color: #f8f9fa;
        }
        .card h3 { margin-top: 0; color: #0056b3; }
    </style>
</head>
<body>

    <!-- BARRA DE NAVEGACIÓN -->
    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <span>Bienvenido, <b><?php echo $usuario; ?></b> (Rol ID: <?php echo $rol; ?>)</span> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <h1>Panel de Control</h1>
                <!-- WIDGET DE PRÓXIMA CITA (Solo para Paciente) -->
        <?php if ($rol == 4): ?>
            <?php if ($proxima_cita): ?>
                <div style="background-color: #d1ecf1; border-left: 5px solid #17a2b8; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #0c5460;">📅 Tu próxima cita</h3>
                    <p style="font-size: 16px; margin-bottom: 5px;">
                        <b>Fecha:</b> <?php echo date('d/m/Y', strtotime($proxima_cita['fecha'])); ?> a las <b><?php echo substr($proxima_cita['hora_inicio'], 0, 5); ?></b>
                    </p>
                    <p style="font-size: 16px; margin-top: 0;">
                        <b>Médico:</b> <?php echo htmlspecialchars($proxima_cita['medico']); ?> (<?php echo htmlspecialchars($proxima_cita['especialidad']); ?>)
                    </p>
                    <?php if ($total_pendientes > 1): ?>
                        <p style="color: #856404; background: #fff3cd; display: inline-block; padding: 5px 10px; border-radius: 4px; font-weight: bold; margin-bottom: 0;">
                            ⚠️ Tienes <?php echo $total_pendientes; ?> citas pendientes en total.
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="background-color: #f8f9fa; border-left: 5px solid #6c757d; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <p style="margin: 0; font-size: 16px; color: #6c757d;">No tienes citas programadas próximamente. ¡Reserva una ahora!</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- AQUÍ COMIENZA EL MENÚ DE TARJETAS (El div menu-grid) -->
        <div class="menu-grid">
        
        <!-- 6. MENÚ BASADO EN ROLES -->
        <div class="menu-grid">
            
            <?php if ($rol == 1): // Si es Administrador ?>
                <a href="especialidades.php" class="card">
                    <h3>Gestión de Especialidades</h3>
                    <p>Registrar y listar áreas médicas.</p>
                </a>
                <a href="usuarios.php" class="card">
                    <h3>Gestión de Usuarios</h3>
                    <p>Administrar cuentas del sistema.</p>
                </a>
                <a href="roles.php" class="card">
                 <h3>Roles y Permisos esto la fregada</h3>
                  <p>Ver la matriz de acceso del sistema.</p>
                </a>
               
            <?php endif; ?>
                
            <?php if ($rol == 1 || $rol == 2): // Si es Admin o Recepcionista ?>
                <a href="pacientes.php" class="card">
                    <h3>Gestión de Pacientes</h3>
                    <p>Registrar y buscar pacientes.</p>
                </a>
                <a href="medicos.php" class="card">
                    <h3>Gestión de Médicos</h3>
                    <p>Administrar médicos y especialidades.</p>
                </a>
              
                <a href="citas.php" class="card">
                    <h3>Gestión de Citas</h3>
                    <p>Agendar, reprogramar y cancelar citas.</p>
                </a>
                <a href="disponibilidad.php" class="card">
                    <h3>Consultar Disponibilidad</h3>
                    <p>Ver horarios libres de los médicos.</p>
                </a>
                <a href="agenda.php" class="card">
                    <h3>Agenda Médica</h3>
                    <p>Ver todas las citas programadas.</p> 
                </a>
                <a href="historial.php" class="card">
                    <h3>Historial de Citas</h3>
                    <p>Ver el historial por paciente.</p>
                </a>
               <a href="reportes.php" class="card">
                    <h3>Reportes</h3>
                    <p>Ver estadísticas de la clínica.</p>
                </a>

            <?php endif; ?>
                
            <?php if ($rol == 3): // Si es Médico ?>
                <a href="agenda.php" class="card">
                    <h3>Mi Agenda</h3>
                    <p>Ver mis citas programadas.</p>
                </a>
            <?php endif; ?>
           
                 <?php if ($rol == 4): // Si es Paciente ?>
                <a href="mis_citas.php" class="card">
                    <h3>Mis Citas / Reservar</h3>
                    <p>Ver mi historial y agendar una consulta.</p>
                </a>
                <a href="mi_perfil.php" class="card">
                    <h3>Mi Perfil</h3>
                    <p>Actualizar mis datos personales.</p>
                </a>
            <?php endif; ?>

        </div> <!-- Fin del menu-gri>d -->
    </div>

</body>
</html>