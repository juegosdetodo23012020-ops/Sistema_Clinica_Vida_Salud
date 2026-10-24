<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Clínica Vida Salud</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="login-container">
        <h2>Clínica Vida Salud</h2>
        
        <!-- MENSAJE DE CIERRE DE SESIÓN EXITOSO (Va justo debajo del título) -->
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'logout'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 15px; font-weight: bold;">
                ¡Has cerrado sesión exitosamente!
            </div>
        <?php endif; ?>

        <!-- FORMULARIO DE LOGIN (Solo debe haber UNO) -->
        <form action="login_process.php" method="POST">
            
            <div class="input-group">
                <label for="correo">Usuario o Correo:</label>
                <input type="text" id="correo" name="correo" required placeholder="Ej: admin">
            </div>

            <div class="input-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <button type="submit">Ingresar al Sistema</button>
        </form>
    </div>

</body>
</html>