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
$total_notificaciones = 0;

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
               
        // Contamos las notificaciones NO LEÍDAS
        $sql_notif = "SELECT COUNT(n.id_notificacion) 
                      FROM notificacion n
                      JOIN cita c ON n.id_cita = c.id_cita
                      WHERE c.id_paciente = :id_paciente 
                      AND n.leida = false 
                      AND n.estado_envio != 'ANULADA'";
        $stmt_notif = $conexion->prepare($sql_notif);
        $stmt_notif->execute([':id_paciente' => $id_pac]);
        $total_notificaciones = $stmt_notif->fetchColumn();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Dashboard - Clínica Vida Salud</title>
    
    
</head>
 <body>
    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <span>Bienvenido(a), <?php echo htmlspecialchars(($_SESSION['nombres'] ?? '') . ' ' . ($_SESSION['apellidos'] ?? '')); ?></span>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="main-content">
        <h1 class="dashboard-title">Panel de Control</h1>

        <?php if ($rol == 1): ?>
            <h2 class="section-title">🛡️ P1: Seguridad y Usuarios</h2>
            <div class="menu-grid">
                <a href="usuarios.php" class="card border-p1"><h3>Gestión de Usuarios</h3><p>Administrar cuentas.</p></a>
                <a href="roles.php" class="card border-p1"><h3>Roles y Permisos</h3><p>Ver matriz de acceso.</p></a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 1 || $rol == 2): ?>
            <h2 class="section-title">🏥 P2: Gestión Clínica Base</h2>
            <div class="menu-grid">
                <a href="pacientes.php" class="card border-p2"><h3>Gestión de Pacientes</h3><p>Registrar y buscar.</p></a>
                <a href="medicos.php" class="card border-p2"><h3>Gestión de Médicos</h3><p>Administrar especialistas.</p></a>
                <a href="especialidades.php" class="card border-p2"><h3>Especialidades</h3><p>Listar áreas médicas.</p></a>
                <a href="disponibilidad.php" class="card border-p2"><h3>Disponibilidad</h3><p>Ver horarios libres.</p></a>
                 <a href="horarios.php" class="card border-p2">
                    <h3>Gestionar Horarios</h3>
                    <p>Asignar turnos a los médicos.</p>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 1 || $rol == 2): ?>
            <h2 class="section-title">📅 P3: Gestión de Citas</h2>
            <div class="menu-grid">
                <a href="citas.php" class="card border-p3"><h3>Gestión de Citas</h3><p>Agendar y cancelar.</p></a>
                <a href="agenda.php" class="card border-p3"><h3>Agenda Médica</h3><p>Ver citas programadas.</p></a>
                <a href="historial.php" class="card border-p3"><h3>Historial</h3><p>Ver por paciente.</p></a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 1 || $rol == 2): ?>
            <h2 class="section-title">📊 P4: Administración</h2>
            <div class="menu-grid">
                <a href="reportes.php" class="card border-p4"><h3>Reportes</h3><p>Estadísticas de la clínica.</p></a>
                <a href="notificaciones.php" class="card border-p4"><h3>Notificaciones</h3><p>Gestionar recordatorios.</p></a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 3): ?>
            <h2 class="section-title">🩺 Mi Espacio</h2>
            <div class="menu-grid">
                <a href="agenda.php" class="card border-p3"><h3>Mi Agenda</h3><p>Ver mis citas.</p></a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 4): ?>
            <h2 class="section-title">👤 Mi Portal</h2>
            <div class="menu-grid">
                <a href="mis_citas.php" class="card border-p3"><h3>Mis Citas / Reservar</h3><p>Agendar una consulta.</p></a>
                <a href="mi_perfil.php" class="card border-p2"><h3>Mi Perfil</h3><p>Actualizar mis datos.</p></a>
                <a href="mis_notificaciones.php" class="card border-p4"><h3>Notificaciones</h3><p>Ver recordatorios.</p></a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
</html>