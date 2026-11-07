<?php
session_start();
require_once 'config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // 1. Validar que las contraseñas coincidan (Flujo Secundario 6.1)
    if ($password !== $password_confirm) {
        $_SESSION['mensaje'] = "Las contraseñas no coinciden. Inténtalo de nuevo.";
        $_SESSION['tipo_mensaje'] = 'danger';
        header("Location: restablecer_contrasena.php?token=" . urlencode($token));
        exit;
    }

    // 2. Validar requisitos mínimos de seguridad
    if (strlen($password) < 6) {
        $_SESSION['mensaje'] = "La contraseña debe tener al menos 6 caracteres.";
        $_SESSION['tipo_mensaje'] = 'danger';
        header("Location: restablecer_contrasena.php?token=" . urlencode($token));
        exit;
    }

    $token_hash = hash('sha256', $token);
    // Encriptamos la nueva contraseña
    $nuevo_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Iniciamos transacción (para que todo se guarde o nada cambie si hay error)
        $conexion->beginTransaction();

        // 3. Validar de nuevo el token por seguridad
        $stmt = $conexion->prepare("SELECT id_usuario FROM token_recuperacion WHERE token_hash = ? AND fecha_uso IS NULL AND fecha_expiracion > now()");
        $stmt->execute([$token_hash]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token_data) {
            $conexion->rollBack();
            $_SESSION['mensaje'] = "El enlace ya no es válido o ha expirado. Solicita uno nuevo.";
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: recuperar_contrasena.php');
            exit;
        }

        $id_usuario = $token_data['id_usuario'];

        // 4. Actualizar la contraseña y reiniciar intentos fallidos (Flujo Principal paso 7)
        $stmt = $conexion->prepare("
            UPDATE usuario 
            SET password_hash = ?, intentos_fallidos = 0, bloqueado_hasta = NULL 
            WHERE id_usuario = ?
        ");
        $stmt->execute([$nuevo_hash, $id_usuario]);

        // 5. Marcar el token como usado
        $stmt = $conexion->prepare("UPDATE token_recuperacion SET fecha_uso = now() WHERE token_hash = ?");
        $stmt->execute([$token_hash]);

        $conexion->commit();

        // 6. Redirigir al Login con mensaje de éxito
        $_SESSION['mensaje_login'] = "¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.";
        $_SESSION['tipo_mensaje_login'] = 'success';
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        $_SESSION['mensaje'] = "Error al restablecer la contraseña: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = 'danger';
        header("Location: restablecer_contrasena.php?token=" . urlencode($token));
        exit;
    }
}
?>