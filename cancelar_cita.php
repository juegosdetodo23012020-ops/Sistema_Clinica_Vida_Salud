<?php
session_start();
require 'config/database.php';

// Importamos PHPMailer para el aviso
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cita = $_POST['id_cita'];
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : 'Cancelada por el usuario';
    $id_usuario = $_SESSION['usuario_id'];
    $origen = isset($_POST['origen']) ? $_POST['origen'] : 'citas';

    try {
        // 1. Llamamos a la función de PostgreSQL
        $sql = "SELECT fn_cancelar_cita(:id_cita, :id_usuario, :motivo)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita,
            ':id_usuario' => $id_usuario,
            ':motivo' => $motivo
        ]);

        // 2. NUEVO: Buscar el correo del paciente para avisarle
        $sql_info = "SELECT p.email AS correo_paciente, p.nombres AS nombre_paciente, c.codigo, c.fecha, c.hora_inicio 
                     FROM cita c
                     JOIN paciente p ON c.id_paciente = p.id_paciente
                     WHERE c.id_cita = :id_cita";
        $stmt_info = $conexion->prepare($sql_info);
        $stmt_info->execute([':id_cita' => $id_cita]);
        $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        // 3. Enviar correo (si el paciente tiene email registrado)
        if ($info && !empty($info['correo_paciente'])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                // 👇 PON TUS CREDENCIALES AQUÍ
                 $mail->Username   = 'vidasalud.demo@gmail.com'; 
                 $mail->Password   = 'msmbhqbjowjrkvwq'; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('tu_correo@gmail.com', 'Clínica Vida Salud');
                $mail->addAddress($info['correo_paciente'], $info['nombre_paciente']);

                $mail->isHTML(true);
                $mail->Subject = 'Cancelación de Cita - Clínica Vida Salud';
                $mail->Body    = "
                    <h2>Hola {$info['nombre_paciente']}</h2>
                    <p>Te informamos que tu cita con código <strong>{$info['codigo']}</strong> programada para el <strong>" . date('d/m/Y', strtotime($info['fecha'])) . "</strong> a las <strong>" . substr($info['hora_inicio'], 0, 5) . "</strong> ha sido cancelada.</p>
                    <p><strong>Motivo:</strong> {$motivo}</p>
                    <p>Por favor, ingresa al portal para reprogramar tu cita.</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Si el correo falla, no detenemos la cancelación. Solo lo ignoramos o guardamos en log.
            }
        }

        // 4. Redirección inteligente al éxito
        if ($origen == 'mis_citas') {
            header("Location: mis_citas.php?mensaje=cancelada");
        } elseif ($origen == 'agenda') {
            header("Location: agenda.php?mensaje=cancelada");
        } else {
            header("Location: citas.php?mensaje=cancelada");
        }
        exit;

    } catch (PDOException $e) {
        error_log("Error al cancelar cita: " . $e->getMessage());
        
        if ($origen == 'mis_citas') {
            header("Location: mis_citas.php?error=sistema");
        } elseif ($origen == 'agenda') {
            header("Location: agenda.php?error=sistema");
        } else {
            header("Location: citas.php?error=sistema");
        }
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>