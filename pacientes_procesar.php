<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $ci = trim($_POST['ci']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $sexo = $_POST['sexo'];
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    try {
        // Validaciones
        $check_sql = "SELECT id_paciente FROM paciente WHERE ci = :ci";
        $check_stmt = $conexion->prepare($check_sql);
        $check_stmt->execute([':ci' => $ci]);
        if ($check_stmt->rowCount() > 0) {
            header("Location: pacientes.php?error=duplicado");
            exit;
        }

        $check_email = $conexion->prepare("SELECT id_usuario FROM usuario WHERE lower(correo) = lower(:correo)");
        $check_email->execute([':correo' => $email]);
        if ($check_email->rowCount() > 0) {
            header("Location: pacientes.php?error=email_duplicado");
            exit;
        }

        $conexion->beginTransaction();

        // 1. Buscar ID del rol Paciente
        $stmt_rol = $conexion->query("SELECT id_rol FROM rol WHERE nombre = 'Paciente' LIMIT 1");
        $id_rol_paciente = $stmt_rol->fetchColumn();
        if (!$id_rol_paciente) {
            throw new Exception("No se encontró el rol 'Paciente' en la base de datos.");
        }

        // 2. Generar usuario y contraseña
        $nombre_usuario = strtolower(explode('@', $email)[0]) . rand(100, 999);
        $password_temporal = 'Paciente123*'; 
        $hash = password_hash($password_temporal, PASSWORD_BCRYPT);

        // 3. Insertar Usuario y obtener su ID con RETURNING
        $sql_user = "INSERT INTO usuario (id_rol, nombre_usuario, nombres, apellidos, correo, password_hash, estado) 
                     VALUES (:id_rol, :nombre_usuario, :nombres, :apellidos, :correo, :hash, true) RETURNING id_usuario";
        $stmt_user = $conexion->prepare($sql_user);
        $stmt_user->execute([
            ':id_rol' => $id_rol_paciente,
            ':nombre_usuario' => $nombre_usuario,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':correo' => $email,
            ':hash' => $hash
        ]);
        
        $id_usuario_creado = $stmt_user->fetchColumn();

        // 4. Insertar Paciente
        $sql_paciente = "INSERT INTO paciente (ci, nombres, apellidos, fecha_nacimiento, sexo, telefono, email, id_usuario) 
                         VALUES (:ci, :nombres, :apellidos, :fecha_nac, :sexo, :telefono, :email, :id_usuario)";
        
        $stmt_paciente = $conexion->prepare($sql_paciente);
        $stmt_paciente->execute([
            ':ci' => $ci,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':fecha_nac' => $fecha_nacimiento,
            ':sexo' => $sexo,
            ':telefono' => $telefono,
            ':email' => $email,
            ':id_usuario' => $id_usuario_creado
        ]);

        $conexion->commit();

        header("Location: pacientes.php?mensaje=exito&user=" . urlencode($nombre_usuario) . "&pass=" . urlencode($password_temporal));
        exit;

    } catch (Exception $e) {
        $conexion->rollBack();
        // DIAGNÓSTICO: Mostrar error en pantalla
        echo "<h1 style='color:red;'>❌ Error al registrar paciente:</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<br><a href='pacientes.php'>Volver a intentar</a>";
        exit;
    }
} else {
    header("Location: pacientes.php");
    exit;
}
?>