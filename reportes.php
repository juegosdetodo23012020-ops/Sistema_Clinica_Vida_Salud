<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: dashboard.php");
    exit;
}
require 'config/database.php';

$fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : date('Y-m-d', strtotime('-30 days'));
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Reportes - Clínica Vida Salud</title>
    
    <!-- Estilos específicos para impresión -->
    <style>
        @media print {
            .navbar, .no-print, button, .filters { display: none !important; }
            body { background: white; }
            .main-content { padding: 0; margin: 0; }
            .panel-table { box-shadow: none; border: none; }
            table { font-size: 12px; }
        }
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

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <h2>Reportes Administrativos</h2>
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <button onclick="window.print()" class="btn btn-success no-print" style="width: auto; margin-bottom: 20px;">🖨️ Imprimir / Guardar PDF</button>
            <?php endif; ?>
        </div>
        
        <form method="POST" class="no-print" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div style="flex: 1; min-width: 200px;">
                <label>Desde:</label>
                <input type="date" name="fecha_desde" value="<?php echo $fecha_desde; ?>" required style="margin-bottom: 0;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label>Hasta:</label>
                <input type="date" name="fecha_hasta" value="<?php echo $fecha_hasta; ?>" required style="margin-bottom: 0;">
            </div>
            <div>
                <button type="submit" style="width: auto; padding: 10px 25px;">Generar Reportes</button>
            </div>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            
            <!-- REPORTE 1: Citas por Estado -->
            <div class="panel-table" style="margin-bottom: 30px;">
                <h3>1. Citas por Estado (<?php echo date('d/m/Y', strtotime($fecha_desde)); ?> al <?php echo date('d/m/Y', strtotime($fecha_hasta)); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Estado</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php
                            $stmt = $conexion->prepare("SELECT * FROM fn_reporte_citas_por_estado(:desde, :hasta)");
                            $stmt->execute([':desde' => $fecha_desde, ':hasta' => $fecha_hasta]);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr><td>" . htmlspecialchars($row['estado']) . "</td><td>" . $row['total'] . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- REPORTE 2: Citas por Médico -->
            <div class="panel-table" style="margin-bottom: 30px;">
                <h3>2. Rendimiento por Médico</h3>
                <div class="table-responsive">
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
            </div>

            <!-- REPORTE 3: Citas por Especialidad -->
            <div class="panel-table">
                <h3>3. Demanda por Especialidad</h3>
                <div class="table-responsive">
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
            </div>

        <?php endif; ?>
    </div>

</body>
</html>