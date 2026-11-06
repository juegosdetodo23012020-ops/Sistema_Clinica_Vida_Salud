<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    // Solo el Administrador (Rol 1) puede ver reportes
    header("Location: dashboard.php");
    exit;
}
require 'config/database.php';

// Fechas por defecto (últimos 30 días)
$fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : date('Y-m-d', strtotime('-30 days'));
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end; background: #e9ecef; padding: 15px; border-radius: 5px; }
        .filters input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 0; }
        .filters button { background-color: #0056b3; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin: 0; width: auto;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
        .card-report { border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <span>Bienvenido, <?php echo $_SESSION['nombre_usuario']; ?></span> | 
            <a href="dashboard.php">Inicio</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <h2>Reportes Administrativos</h2>
        
        <form method="POST" class="filters">
            <div>
                <label>Desde:</label><br>
                <input type="date" name="fecha_desde" value="<?php echo $fecha_desde; ?>" required>
            </div>
            <div>
                <label>Hasta:</label><br>
                <input type="date" name="fecha_hasta" value="<?php echo $fecha_hasta; ?>" required>
            </div>
            <button type="submit">Generar Reportes</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            
            <!-- REPORTE 1: Citas por Estado -->
            <div class="card-report">
                <h3>1. Citas por Estado (<?php echo $fecha_desde; ?> al <?php echo $fecha_hasta; ?>)</h3>
                <table>
                    <thead><tr><th>Estado</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php
                        // Llamamos a la función de PostgreSQL
                        $stmt = $conexion->prepare("SELECT * FROM fn_reporte_citas_por_estado(:desde, :hasta)");
                        $stmt->execute([':desde' => $fecha_desde, ':hasta' => $fecha_hasta]);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr><td>" . htmlspecialchars($row['estado']) . "</td><td>" . $row['total'] . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- REPORTE 2: Citas por Médico -->
            <div class="card-report">
                <h3>2. Rendimiento por Médico</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Médico</th><th>Programadas</th><th>Confirmadas</th><th>Atendidas</th><th>Canceladas</th><th>Inasistencias</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conexion->prepare("SELECT * FROM fn_reporte_citas_por_medico(:desde, :hasta)");
                        $stmt->execute([':desde' => $fecha_desde, ':hasta' => $fecha_hasta]);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td><b>" . htmlspecialchars($row['medico']) . "</b></td>";
                            echo "<td>" . $row['programadas'] . "</td>";
                            echo "<td>" . $row['confirmadas'] . "</td>";
                            echo "<td style='color: green; font-weight:bold;'>" . $row['atendidas'] . "</td>";
                            echo "<td style='color: red;'>" . $row['canceladas'] . "</td>";
                            echo "<td>" . $row['inasistencias'] . "</td>";
                            echo "<td><b>" . $row['total'] . "</b></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- REPORTE 3: Citas por Especialidad -->
            <div class="card-report">
                <h3>3. Demanda por Especialidad</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Especialidad</th><th>Programadas</th><th>Atendidas</th><th>Canceladas</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conexion->prepare("SELECT * FROM fn_reporte_citas_por_especialidad(:desde, :hasta)");
                        $stmt->execute([':desde' => $fecha_desde, ':hasta' => $fecha_hasta]);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td><b>" . htmlspecialchars($row['especialidad']) . "</b></td>";
                            echo "<td>" . $row['programadas'] . "</td>";
                            echo "<td style='color: green;'>" . $row['atendidas'] . "</td>";
                            echo "<td style='color: red;'>" . $row['canceladas'] . "</td>";
                            echo "<td><b>" . $row['total'] . "</b></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

</body>
</html>