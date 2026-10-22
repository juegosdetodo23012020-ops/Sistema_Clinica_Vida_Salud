<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Capturamos los datos del formulario
    $registro_prof = trim($_POST['registro_prof']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $especialidades = $_POST['especialidades']; // Esto es un array (lista) de IDs

    try {
        // 2. VALIDACIÓN (RN02): Verificar que el registro profesional o el correo no existan
        $check_sql = "SELECT id_usuario FROM usuario WHERE correo = :email 
                      UNION 
                      SELECT id_usuario FROM medico WHERE registro_prof = :registro";
        $check_stmt = $conexion->prepare($check_sql);
        $check_stmt->execute([':email' => $email, ':registro' => $registro_prof]);
        
        if ($check_stmt->rowCount() > 0) {
            header("Location: medicos.php?error=duplicado");
            exit;
        }

        // 3. INICIAR TRANSACCIÓN (Si algo falla, se cancela todo)
        $conexion->beginTransaction();

        // 4. PASO A: Crear el usuario (Rol 3 = Médico)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_user = "INSERT INTO usuario (id_rol, nombre_usuario, nombres, apellidos, correo, password_hash) 
                     VALUES (3, :usuario, :nombres, :apellidos, :email, :hash)";
        $stmt_user = $conexion->prepare($sql_user);
        // Generamos un nombre de usuario basado en el correo (ej. jperez)
        $nombre_usuario = explode('@', $email)[0]; 
        $stmt_user->execute([
            ':usuario' => $nombre_usuario,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':email' => $email,
            ':hash' => $hash
        ]);
        
        // Obtenemos el ID del usuario que se acaba de crear
        $id_usuario = $conexion->lastInsertId();

        // 5. PASO B: Crear el perfil del médico
        $sql_medico = "INSERT INTO medico (id_usuario, registro_prof, nombres, apellidos, telefono, email) 
                       VALUES (:id_usuario, :registro, :nombres, :apellidos, :telefono, :email)";
        $stmt_medico = $conexion->prepare($sql_medico);
        $stmt_medico->execute([
            ':id_usuario' => $id_usuario,
            ':registro' => $registro_prof,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':telefono' => $telefono,
            ':email' => $email
        ]);
        
        // Obtenemos el ID del médico que se acaba de crear
        $id_medico = $conexion->lastInsertId();

        // 6. PASO C: Asignar las especialidades (Relación Muchos a Muchos)
        // Recorremos el array de especialidades que el usuario seleccionó
        $sql_esp = "INSERT INTO medico_especialidad (id_medico, id_especialidad) VALUES (:id_medico, :id_esp)";
        $stmt_esp = $conexion->prepare($sql_esp);
        
        foreach ($especialidades as $id_esp) {
            $stmt_esp->execute([
                ':id_medico' => $id_medico,
                ':id_esp' => $id_esp
            ]);
        }

        // 7. CONFIRMAR TRANSACCIÓN (Guardar todos los cambios de golpe)
        $conexion->commit();

        // Si todo sale bien, redirigimos
        header("Location: medicos.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // 8. SI HAY ERROR: Deshacer todos los cambios (Rollback)
        $conexion->rollBack();
        error_log("Error al registrar médico: " . $e->getMessage());
        header("Location: medicos.php?error=sistema");
        exit;
    }
} else {
    header("Location: medicos.php");
    exit;
}
?>