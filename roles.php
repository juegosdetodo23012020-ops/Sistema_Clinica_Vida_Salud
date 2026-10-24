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
    <title>Roles y Permisos - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
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
        <h2>Matriz de Roles y Permisos del Sistema (RBAC)</h2>
        <p>Esta pantalla muestra los permisos asignados a cada rol en la base de datos PostgreSQL.</p>

        <table>
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Permisos Asignados (Códigos)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta avanzada con STRING_AGG para agrupar los permisos de cada rol
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

</body>
</html>