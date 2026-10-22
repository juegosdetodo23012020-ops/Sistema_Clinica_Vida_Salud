<?php
session_start();
// Seguridad: Si no hay sesión activa, lo pateamos al login.
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
    <title>Especialidades - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        button:hover { background-color: #218838; }
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
        <!-- FORMULARIO DE REGISTRO -->
        <div class="form-section">
            <h3>Registrar Especialidad</h3>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Especialidad registrada correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                <div class="alert alert-danger">Error: Esa especialidad ya existe.</div>
            <?php endif; ?>

            <form action="especialidades_procesar.php" method="POST">
                <label>Nombre de la Especialidad:</label>
                <input type="text" name="nombre" required placeholder="Ej: Cardiología">

                <label>Descripción (Opcional):</label>
                <textarea name="descripcion" rows="3" placeholder="Breve descripción..."></textarea>

                <button type="submit">Guardar Especialidad</button>
            </form>
        </div>

        <!-- TABLA DE LISTADO -->
        <div class="table-section">
            <h3>Especialidades Registradas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultamos las especialidades activas
                    $sql = "SELECT id_especialidad, nombre, descripcion, estado FROM especialidad WHERE estado = true ORDER BY nombre ASC";
                    $stmt = $conexion->query($sql);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id_especialidad']) . "</td>";
                        echo "<td><b>" . htmlspecialchars($row['nombre']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['descripcion'] ?? 'Sin descripción') . "</td>";
                        echo "<td>Activo</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>