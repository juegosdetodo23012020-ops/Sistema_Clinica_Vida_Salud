<?php
session_start();
require 'config/database.php';

// Seguridad básica: debe ser POST y debe haber sesión de paciente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id']) && $_SESSION['rol_id'] == 4) {
    
    $id_cita = $_POST['id_cita'] ?? 0;
    
    // Generamos un código de referencia único simulando la transacción de la pasarela
    // (Ejemplo: SIM-65A3B2C1D4)
    $referencia = 'SIM-' . strtoupper(uniqid());
    
    try {
        // Insertamos el pago en la base de datos
        $sql = "INSERT INTO pago (id_cita, referencia, monto, estado_pago) 
                VALUES (:id_cita, :ref, 50.00, 'APROBADO')";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_cita' => $id_cita, 
            ':ref' => $referencia
        ]);
        
        // Guardamos el mensaje en sesión para mostrarlo en 'mis_citas.php'
        $_SESSION['mensaje_pago'] = "¡Pago procesado con éxito! Referencia: " . $referencia;
        
        // Redirigimos de vuelta a las citas del paciente
        header("Location: mis_citas.php");
        exit;
        
    } catch (PDOException $e) {
        // Si hay un error, lo mostramos temporalmente para depurar
        die("Error al procesar el pago en la base de datos: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar directo o no es paciente, lo pateamos
    header("Location: mis_citas.php");
    exit;
}
?>