<?php
session_start();
// Seguridad: Solo el Administrador (rol 1) puede entrar aquí
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
    <title>Usuarios - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0056b3; color: white; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
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
        <div class="form-section">
            <h3>Registrar Nuevo Usuario</h3>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Usuario registrado correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                <div class="alert alert-danger">Error: El correo o nombre de usuario ya existe.</div>
            <?php endif; ?>

            <form action="usuarios_procesar.php" method="POST">
                <label>Nombre de Usuario (Login):</label>
                <input type="text" name="nombre_usuario" required placeholder="Ej: jperez">

                <label>Nombres:</label>
                <input type="text" name="nombres" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" required>

                <label>Correo Electrónico:</label>
                <input type="email" name="correo" required>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <label>Rol del Sistema:</label>
                <select name="id_rol" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $stmt = $conexion->query("SELECT id_rol, nombre FROM rol ORDER BY id_rol");
                    while ($rol = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$rol['id_rol']}'>{$rol['nombre']}</option>";
                    }
                    ?>
                </select>

                <button type="submit">Guardar Usuario</button>
            </form>
        </div>

        <div class="table-section">
            <h3>Usuarios Registrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Real</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta con JOIN para traer el nombre del rol
                    $sql = "SELECT u.nombre_usuario, u.nombres, u.apellidos, u.correo, r.nombre as rol, u.estado 
                            FROM usuario u 
                            JOIN rol r ON u.id_rol = r.id_rol 
                            ORDER BY u.id_usuario DESC";
                    $stmt = $conexion->query($sql);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $estado = $row['estado'] ? 'Activo' : 'Inactivo';
                        echo "<tr>";
                        echo "<td><b>" . htmlspecialchars($row['nombre_usuario']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['nombres'] . " " . $row['apellidos']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['rol']) . "</td>";
                        echo "<td>" . $estado . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>