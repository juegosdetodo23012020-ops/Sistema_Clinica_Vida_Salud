<?php
// 1. Iniciamos la sesión
session_start();

// 2. Traemos la conexión a la base de datos
require 'config/database.php';

// 3. Verificamos que los datos vengan del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 4. Atrapamos los datos
    $login = trim($_POST['correo']); 
    $password = $_POST['password'];

    try {
        // 5. Buscamos en la base de datos
        $sql = "SELECT id_usuario, nombre_usuario, password_hash, id_rol, nombres, apellidos 
                FROM usuario 
                WHERE lower(correo) = lower(:correo) OR lower(nombre_usuario) = lower(:correo)";
        
        $stmt = $conexion->prepare($sql);
        // 🔥 EL ERROR ESTABA AQUÍ: decía ':login' en lugar de ':correo'
        $stmt->execute([':correo' => $login]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 6. VERIFICACIÓN
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            
            // 7. Guardamos los datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol_id'] = $usuario['id_rol'];
            $_SESSION['nombres'] = $usuario['nombres'];
            $_SESSION['apellidos'] = $usuario['apellidos'];
            
            // 8. Enviamos al Dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // 9. Error de credenciales
            header("Location: index.php?error=credenciales");
            exit;
        }
    } catch (PDOException $e) {
        // 10. Error de BD
        error_log("Error en login: " . $e->getMessage());
        header("Location: index.php?error=sistema");
        exit;
    }
} else {
    // 11. Acceso directo
    header("Location: index.php");
    exit;
}
?>