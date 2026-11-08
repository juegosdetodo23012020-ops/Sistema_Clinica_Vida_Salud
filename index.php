<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Vida Salud</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos exclusivos para que el Login se vea perfecto */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9; /* Fondo gris claro */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            color: #0056b3;
            text-align: center;
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #0056b3;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #004494;
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #0056b3;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Clínica Vida Salud</h2>
        
        <!-- MENSAJE DE CIERRE DE SESIÓN EXITOSO -->
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'logout'): ?>
            <div class="alert alert-success">¡Has cerrado sesión exitosamente!</div>
        <?php endif; ?>

        <!-- NUEVO: MENSAJE DE CONTRASEÑA ACTUALIZADA CON ÉXITO -->
        <?php if (isset($_SESSION['mensaje_login'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['mensaje_login']) ?>
            </div>
            <?php unset($_SESSION['mensaje_login']); ?>
        <?php endif; ?>

        <!-- FORMULARIO DE LOGIN -->
        <form action="login_process.php" method="POST">
            
            <div class="input-group">
                <label for="correo">Usuario o Correo:</label>
                <input type="text" id="correo" name="correo" required placeholder="Ej: admin">
            </div>

            <div class="input-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <button type="submit" class="btn-submit">Ingresar al Sistema</button>
        </form>

        <!-- ENLACE DE RECUPERACIÓN -->
        <div class="forgot-password">
            <a href="recuperar_contrasena.php">¿Olvidaste tu contraseña?</a>
        </div>

    </div>

</body>
</html>