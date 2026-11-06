<?php
require 'config/database.php';

$id_especialidad = isset($_GET['id_especialidad']) ? $_GET['id_especialidad'] : null;

if (!$id_especialidad) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT m.id_medico, m.apellidos, m.nombres 
        FROM medico m
        JOIN medico_especialidad me ON m.id_medico = me.id_medico
        WHERE me.id_especialidad = :id_esp AND m.estado = true
        ORDER BY m.apellidos";

$stmt = $conexion->prepare($sql);
$stmt->execute([':id_esp' => $id_especialidad]);
$medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($medicos);
?>