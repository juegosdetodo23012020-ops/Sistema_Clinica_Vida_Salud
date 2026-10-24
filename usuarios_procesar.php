<?php
session_start();
require 'config/database.php';

// Solo el admin puede procesar esto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $password_plana = $_POST['password'];
    $id_rol = $_POST['id_rol'];

    try {
        // Encriptar la contraseña
        $hash = password_hash($password_plana, PASSWORD_DEFAULT);

        // Insertar en la base de datos
        $sql = "INSERT INTO usuario (id_rol, nombre_usuario, nombres, apellidos, correo, password_hash) 
                VALUES (:id_rol, :usuario, :nombres, :apellidos, :correo, :hash)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_rol' => $id_rol,
            ':usuario' => $nombre_usuario,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':hash' => $hash
        ]);

        header("Location: usuarios.php?mensaje=exito");
        exit;

    } catch (PDOException $e) {
        // Si el correo o el usuario ya existen (código de error 23505 en Postgres)
        if ($e->getCode() == '23505') {
            header("Location: usuarios.php?error=duplicado");
            exit;
        } else {
            error_log("Error al crear usuario: " . $e->getMessage());
            header("Location: usuarios.php?error=sistema");
            exit;
        }
    }
} else {
    header("Location: usuarios.php");
    exit;
}
?>