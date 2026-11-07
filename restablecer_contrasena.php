<?php
session_start();
require_once 'config/database.php'; 

$token = $_GET['token'] ?? '';
$token_valido = false;
$error = '';

if (empty($token)) {
    $error = "Enlace inválido. No se proporcionó un token.";
} else {
    $token_hash = hash('sha256', $token);
    
    try {
        $stmt = $conexion->prepare("
            SELECT t.id_usuario 
            FROM token_recuperacion t
            JOIN usuario u ON t.id_usuario = u.id_usuario
            WHERE t.token_hash = ? 
              AND t.fecha_uso IS NULL 
              AND t.fecha_expiracion > now()
              AND u.estado = true
        ");
        $stmt->execute([$token_hash]);
        
        if ($stmt->fetch()) {
            $token_valido = true;
        } else {
            $error = "El enlace es inválido, ha vencido o ya fue utilizado. Por favor, solicita uno nuevo.";
        }
    } catch (PDOException $e) {
        $error = "Error en el sistema: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Clínica Vida Salud</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #0056b3; text-align: center; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        
        /* --- ESTILOS DEL OJITO CORREGIDOS --- */
        .password-wrapper { 
            position: relative; 
            width: 100%; 
        }
        
        input[type="password"], input[type="text"] { 
            width: 100%; 
            padding: 10px 40px 10px 10px; /* Deja 40px a la derecha para el ojito */
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 16px;
        }
        
        .toggle-password { 
            position: absolute; 
            right: 12px; 
            top: 50%; 
            transform: translateY(-50%); /* Lo centra verticalmente */
            cursor: pointer; 
            font-size: 18px; 
            user-select: none; 
            z-index: 10; /* Se asegura de estar clickeable */
        }
        /* ---------------------------------- */
        
        .btn { width: 100%; padding: 12px; background-color: #0056b3; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .btn:hover { background-color: #004494; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #0056b3; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Restablecer Contraseña</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <div class="links"><a href="recuperar_contrasena.php">Solicitar un nuevo enlace</a></div>
    <?php endif; ?>

    <?php if ($token_valido): ?>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 20px;">
            Ingresa tu nueva contraseña a continuación.
        </p>
        <form action="restablecer_procesar.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="password">Nueva Contraseña:</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <!-- Usamos span en lugar de button para evitar que interfiera -->
                    <span class="toggle-password" onclick="togglePassword('password', 'eye1')" id="eye1">👁️</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmar Contraseña:</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirm" id="password_confirm" required minlength="6" placeholder="Repite la contraseña">
                    <span class="toggle-password" onclick="togglePassword('password_confirm', 'eye2')" id="eye2">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">Guardar Nueva Contraseña</button>
        </form>
    <?php endif; ?>
</div>

<!-- Script para mostrar/ocultar contraseña -->
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈"; 
    } else {
        input.type = "password";
        icon.textContent = "👁️"; 
    }
}
</script>

</body>
</html>