<?php
session_start();
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';
$debug_link = $_SESSION['debug_link'] ?? '';
// Limpiamos los mensajes para que no se queden pegados si recargas la página
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['debug_link']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Clínica Vida Salud</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos de respaldo */
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #0056b3; text-align: center; margin-bottom: 20px; }
        p { text-align: center; color: #666; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background-color: #0056b3; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
        .btn:hover { background-color: #004494; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #0056b3; text-decoration: none; font-size: 14px; }
        .debug-box { background: #e2e8f0; padding: 15px; border-radius: 4px; word-break: break-all; text-align: center; margin-bottom: 15px;}
    </style>
</head>
<body>

<div class="card">
    <h2>Recuperar Contraseña</h2>
    <p>Ingresa tu correo electrónico registrado y te enviaremos un enlace para restablecer tu contraseña.</p>

    <!-- Mostrar mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar enlace simulado para pruebas locales -->
    <?php if ($debug_link): ?>
        <div class="debug-box">
            <a href="<?= htmlspecialchars($debug_link) ?>" style="color: #0056b3; font-weight: bold;">
                👉 Haz clic AQUÍ para restablecer tu contraseña
            </a>
        </div>
    <?php endif; ?>

    <form action="recuperar_procesar.php" method="POST">
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required placeholder="Ej: usuario@correo.com">
        </div>
        
        <button type="submit" class="btn">Enviar enlace de recuperación</button>
    </form>

    <div class="links">
        <a href="index.php">Volver al Iniciar Sesión</a>
    </div>
</div>

</body>
</html>