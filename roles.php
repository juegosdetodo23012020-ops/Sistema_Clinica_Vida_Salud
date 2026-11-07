<?php
session_start();
// Solo el Administrador puede ver esto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: dashboard.php");
    exit;
}
require 'config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Roles y Permisos - Clínica Vida Salud</title>
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
        <h2>Matriz de Roles y Permisos del Sistema (RBAC)</h2>
        <p style="margin-bottom: 20px;">Esta pantalla muestra los permisos asignados a cada rol en la base de datos PostgreSQL.</p>

        <div class="panel-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Permisos Asignados (Códigos)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.nombre AS rol, 
                                STRING_AGG(p.codigo, ' | ') AS permisos
                                FROM rol r
                                LEFT JOIN rol_permiso rp ON r.id_rol = rp.id_rol
                                LEFT JOIN permiso p ON rp.id_permiso = p.id_permiso
                                GROUP BY r.id_rol, r.nombre
                                ORDER BY r.id_rol";
                        
                        $stmt = $conexion->query($sql);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td><b>" . htmlspecialchars($row['rol']) . "</b></td>";
                            echo "<td>" . htmlspecialchars($row['permisos'] ?? 'Sin permisos asignados') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>