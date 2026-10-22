<?php
// 1. Iniciamos la sesión para poder guardar los datos del usuario.
session_start();

// 2. Traemos la conexión a la base de datos (el archivo que está en config).
require 'config/database.php';

// 3. Verificamos que los datos vengan del formulario (método POST).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 4. Atrapamos lo que el usuario escribió en el index.php.
    $login = trim($_POST['correo']); // El HTML dice name="correo"
    $password = $_POST['password'];

    try {
        // 5. Buscamos en la base de datos de tu compañera (esquema clinica).
        // Buscamos por nombre_usuario o por correo.
        $sql = "SELECT id_usuario, nombre_usuario, password_hash, id_rol 
                FROM usuario 
                WHERE (lower(nombre_usuario) = lower(:login) OR lower(correo) = lower(:login)) 
                AND estado = true";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':login' => $login]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 6. VERIFICACIÓN: ¿El usuario existe Y la contraseña es correcta?
        // password_verify() compara "Admin123*" con el hash encriptado de PostgreSQL.
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            
            // 7. Si es correcto, guardamos los datos en la sesión (la "mochila" de PHP).
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol_id'] = $usuario['id_rol'];
            
            // 8. Lo enviamos al Dashboard.
            header("Location: dashboard.php");
            exit;
        } else {
            // 9. Si la contraseña está mal, lo devolvemos al login con un error.
            header("Location: index.php?error=credenciales");
            exit;
        }
    } catch (PDOException $e) {
        // 10. Si hay error de base de datos, lo guardamos y mostramos error genérico.
        error_log("Error en login: " . $e->getMessage());
        header("Location: index.php?error=sistema");
        exit;
    }
} else {
    // 11. Si alguien entra directo a este archivo por URL, lo devolvemos al login.
    header("Location: index.php");
    exit;
}
?>