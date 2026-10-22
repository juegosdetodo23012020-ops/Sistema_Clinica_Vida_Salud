<?php
// 1. INICIAR SESIÓN: Necesitamos recordar quién es el usuario.
session_start();

// 2. CONTROL DE SEGURIDAD (Requerimiento RF01): 
// Si la variable 'usuario_id' NO existe en la sesión, significa que el usuario no se ha logueado.
// Lo redirigimos al index.php inmediatamente y detenemos el código.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// 3. RECUPERAR DATOS: Guardamos los datos de la sesión en variables más cortas para usarlas abajo.
$rol = $_SESSION['rol_id'];
$usuario = $_SESSION['nombre_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Clínica Vida Salud</title>
    <style>
        /* 4. DISEÑO BÁSICO (Luego lo pasaremos a CSS externo): 
           Aquí hacemos que la barra superior sea azul y el fondo gris. */
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { padding: 40px; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; }
    </style>
</head>
<body>

    <!-- 5. BARRA DE NAVEGACIÓN: Muestra el nombre del usuario y el botón de cerrar sesión -->
    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <!-- Imprimimos el nombre del usuario que guardamos en la sesión -->
            <span>Bienvenido, <b><?php echo $usuario; ?></b> (Rol ID: <?php echo $rol; ?>)</span> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <h1>Panel de Control</h1>
        
        <!-- 6. MENÚ BASADO EN ROLES (Requerimiento RF01) -->
        <!-- Aquí usamos IF en PHP para mostrar botones distintos según el rol. -->
        <!-- Rol 1 = Administrador -->
        <?php if ($rol == 1): ?>
            <div class="card">
                <h3>Módulos de Administrador</h3>
                <p>Aquí podrás gestionar Usuarios, Especialidades y ver Reportes.</p>
            </div>
        <?php endif; ?>

        <!-- Rol 1 = Administrador, Rol 2 = Recepcionista -->
        <?php if ($rol == 1 || $rol == 2): ?>
            <div class="card">
                <h3>Módulos de Recepción</h3>
                <p>Aquí podrás gestionar Pacientes, Médicos y Agendar Citas.</p>
            </div>
        <?php endif; ?>

        <!-- Rol 3 = Médico -->
        <?php if ($rol == 3): ?>
            <div class="card">
                <h3>Módulos de Médico</h3>
                <p>Aquí podrás ver tu agenda diaria y marcar citas como atendidas.</p>
            </div>
        <?php endif; ?>

        <!-- Rol 4 = Paciente -->
        <?php if ($rol == 4): ?>
            <div class="card">
                <h3>Portal del Paciente</h3>
                <p>Aquí podrás reservar citas y ver tu historial.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>