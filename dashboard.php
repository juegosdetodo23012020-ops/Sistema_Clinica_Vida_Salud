<?php
// 1. INICIAR SESIÓN
session_start();

// 2. CONTROL DE SEGURIDAD
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// 3. RECUPERAR DATOS
$rol = $_SESSION['rol_id'];
$usuario = $_SESSION['nombre_usuario'];
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
                <a href="#" class="card">
                    <h3>Reportes</h3>
                    <p>Ver estadísticas de la clínica.</p>
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
                    <h3>Agendar Cita</h3>
                    <p>Registrar nuevas citas médicas.</p>
                </a>
                <a href="disponibilidad.php" class="card">
                    <h3>Consultar Disponibilidad</h3>
                    <p>Ver horarios libres de los médicos.</p>
                </a>
            <?php endif; ?>
                
            <?php if ($rol == 3): // Si es Médico ?>
                <a href="#" class="card">
                    <h3>Mi Agenda</h3>
                    <p>Ver mis citas programadas.</p>
                </a>
            <?php endif; ?>
           
            <?php if ($rol == 4): // Si es Paciente ?>
                <a href="#" class="card">
                    <h3>Reservar Cita</h3>
                    <p>Agendar una nueva consulta.</p>
                </a>
                <a href="#" class="card">
                    <h3>Mi Perfil</h3>
                    <p>Actualizar mis datos personales.</p>
                </a>
            <?php endif; ?>

        </div> <!-- Fin del menu-gri>d -->
    </div>

</body>
</html>