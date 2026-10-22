<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Clínica Vida Salud</title>
    <!-- 1. Aquí conectamos el diseño. Aún no hemos creado este archivo, por eso se verá feo, pero funcionará. -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <!-- 2. Este es el cuadro blanco donde va el formulario -->
    <div class="login-container">
        <h2>Clínica Vida Salud</h2>
        
        <!-- 3. AQUÍ ESTÁ LA MAGIA: 
             action="login_process.php" -> Le dice al formulario a qué archivo enviar los datos cuando hagas clic en "Ingresar".
             method="POST" -> Es la forma segura de enviar contraseñas (no se ven en la URL). -->
        <form action="login_process.php" method="POST">
            
            <div class="input-group">
                <label for="correo">Usuario:</label>
                <!-- 4. El atributo name="correo" es VITAL. 
                     Es la etiqueta con la que PHP atrapará el dato usando $_POST['correo'] en el login_process.php -->
                <input type="text" id="correo" name="correo" required placeholder="Ej: admin">
            </div>

            <div class="input-group">
                <label for="password">Contraseña:</label>
                <!-- 5. Lo mismo aquí. name="password" será $_POST['password'] en PHP. -->
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <!-- 6. El botón que envía todo -->
            <button type="submit">Ingresar al Sistema</button>
        </form>
    </div>

</body>
</html>