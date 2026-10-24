<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
require 'config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultar Disponibilidad - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select { padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #0056b3; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #004494; }
        .horarios-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 20px; }
        .btn-horario { background-color: #28a745; color: white; padding: 10px; text-align: center; border-radius: 5px; text-decoration: none; }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <a href="dashboard.php">Inicio</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <h2>Consultar Horarios Disponibles</h2>
        <p>Seleccione un médico y una fecha para ver los bloques de 15 minutos libres.</p>
        
        <form method="POST">
            <select name="id_medico" required>
                <option value="">Seleccione un Médico...</option>
                <?php
                $stmt = $conexion->query("SELECT id_medico, apellidos, nombres FROM medico WHERE estado = true");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['id_medico']}'>Dr(a). {$row['apellidos']}, {$row['nombres']}</option>";
                }
                ?>
            </select>
            <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
            <button type="submit">Consultar Disponibilidad</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_medico = $_POST['id_medico'];
            $fecha = $_POST['fecha'];

            try {
                // Llamamos a la función de PostgreSQL que se programo para obtener los horarios disponibles del médico en la fecha seleccionada
                $sql = "SELECT hora_inicio FROM fn_cupos_disponibles(:id_medico, :fecha)";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([':id_medico' => $id_medico, ':fecha' => $fecha]);
                
                $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<h3>Horarios disponibles para el " . date('d/m/Y', strtotime($fecha)) . ":</h3>";
                
                if (count($horarios) > 0) {
                    echo "<div class='horarios-grid'>";
                    foreach ($horarios as $h) {
                        echo "<div class='btn-horario'>" . substr($h['hora_inicio'], 0, 5) . "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p style='color: red;'>No hay disponibilidad para este médico en esta fecha.</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </div>

</body>
</html>