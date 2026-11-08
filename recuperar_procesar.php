<?php
session_start();
require_once 'config/database.php'; 

// Importamos PHPMailer para el envío real
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mensaje_generico = "Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.";

    try {
        // 1. Buscar cuenta activa
        $stmt = $conexion->prepare("
            SELECT id_usuario, nombres, correo 
            FROM usuario 
            WHERE lower(correo) = lower(?) AND estado = true
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // 2. Invalidar tokens anteriores
            $stmt = $conexion->prepare("UPDATE token_recuperacion SET fecha_uso = now() WHERE id_usuario = ? AND fecha_uso IS NULL");
            $stmt->execute([$usuario['id_usuario']]);

            // 3. Generar token
            $token = bin2hex(random_bytes(32)); 
            $token_hash = hash('sha256', $token); 

            // 4. Obtener minutos de vigencia
            $stmt = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'token_vigencia_min'");
            $minutos = (int)$stmt->fetchColumn() ?: 30;

            // 5. Guardar en BD
            $stmt = $conexion->prepare("
                INSERT INTO token_recuperacion (id_usuario, token_hash, fecha_expiracion) 
                VALUES (?, ?, now() + interval '1 minute' * ?)
            ");
            $stmt->execute([$usuario['id_usuario'], $token_hash, $minutos]);

            // 6. ENVÍO DE CORREO REAL CON PHPMailer
            // Armamos el enlace dinámicamente para que funcione en local y en Render
            $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $enlace = $protocolo . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/restablecer_contrasena.php?token=" . $token;

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            $mail->Username   = 'vidasalud.demo@gmail.com'; 
            $mail->Password   = 'msmbhqbjowjrkvwq';  
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('vidasalud.prueba@gmail.com', 'Clinica Vida Salud');
            $mail->addAddress($usuario['correo'], $usuario['nombres']);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de Contrasena - Clinica Vida Salud';
            $mail->Body    = "
                <h2>Hola {$usuario['nombres']}</h2>
                <p>Recibimos una solicitud para restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva (válido por $minutos minutos):</p>
                <p><a href='$enlace' style='padding: 10px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
                <p>Si no solicitaste esto, ignora este correo.</p>
            ";

            try {
                $mail->send();
                $_SESSION['mensaje'] = $mensaje_generico;
                $_SESSION['tipo_mensaje'] = 'success';
            } catch (Exception $e) {
                // Si falla el correo, por seguridad mostramos el mensaje genérico, pero podrías loguear el error
                $_SESSION['mensaje'] = $mensaje_generico;
                $_SESSION['tipo_mensaje'] = 'info';
            }

        } else {
            $_SESSION['mensaje'] = $mensaje_generico;
            $_SESSION['tipo_mensaje'] = 'info';
        }

        header('Location: recuperar_contrasena.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error en el sistema. Intenta más tarde.";
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: recuperar_contrasena.php');
        exit;
    }
}
?>