<?php
session_start();
require 'config/database.php';

// Solo devolveremos datos si hay una sesión activa
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Recibimos los parámetros por la URL (GET)
$id_medico = isset($_GET['id_medico']) ? $_GET['id_medico'] : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;

if (!$id_medico || !$fecha) {
    echo json_encode(["error" => "Faltan parámetros"]);
    exit;
}

try {
      // 1. Obtenemos los cupos LIBRES usando la función de PostgreSQL
      $sql_libres = "SELECT hora_inicio FROM fn_cupos_disponibles(:id_medico, :fecha)";
      $stmt_libres = $conexion->prepare($sql_libres);
     $stmt_libres->execute([':id_medico' => $id_medico, ':fecha' => $fecha]);
     $horas_libres = $stmt_libres->fetchAll(PDO::FETCH_COLUMN);

      // 2. Obtenemos los horarios de atención del médico para ese día (para saber los bloques totales)
      $dias = ['1'=>'LUNES','2'=>'MARTES','3'=>'MIERCOLES','4'=>'JUEVES','5'=>'VIERNES','6'=>'SABADO','7'=>'DOMINGO'];
     $dia_semana = $dias[date('N', strtotime($fecha))];
    
     $sql_horario = "SELECT hora_inicio, hora_fin, duracion_cita_min 
                    FROM horario_atencion 
                    WHERE id_medico = :id_medico AND dia_semana = :dia";
     $stmt_horario = $conexion->prepare($sql_horario);
     $stmt_horario->execute([':id_medico' => $id_medico, ':dia' => $dia_semana]);
     $horario = $stmt_horario->fetch(PDO::FETCH_ASSOC);

      $respuesta = [];

    
        if ($horario) {
        // Generamos los bloques de 15 minutos y vemos cuáles están libres
        $inicio = strtotime($horario['hora_inicio']);
        $fin = strtotime($horario['hora_fin']);
        $duracion = $horario['duracion_cita_min'] * 60;

        for ($i = $inicio; $i < $fin; $i += $duracion) {
            $hora_bloque = date('H:i:s', $i);
            $hora_mostrar = date('H:i', $i);
            
            $disponible = in_array($hora_bloque, $horas_libres);

            $respuesta[] = [
                "hora" => $hora_mostrar,
                "disponible" => $disponible
            ];
        }
     } else {
        // Si no hay horario para ese día, buscamos qué días SÍ atiende
        $sql_dias = "SELECT dia_semana FROM horario_atencion WHERE id_medico = :id_medico ORDER BY id_horario";
        $stmt_dias = $conexion->prepare($sql_dias);
        $stmt_dias->execute([':id_medico' => $id_medico]);
        $dias_atendidos = $stmt_dias->fetchAll(PDO::FETCH_COLUMN);

        if (count($dias_atendidos) > 0) {
            // Formateamos los días para que se vean bonitos (Lunes, Miércoles, etc.)
            $dias_texto = implode(', ', array_unique($dias_atendidos));
            $respuesta = ["error" => "El médico no atiende este día. Sus días de atención son: " . $dias_texto];
        } else {
            $respuesta = ["error" => "El médico no tiene horarios configurados."];
        }
     }

     // Devolvemos la respuesta en formato JSON
      header('Content-Type: application/json');
       echo json_encode($respuesta);

     } catch (Exception $e) {
    echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
}
?>