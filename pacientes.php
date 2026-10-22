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
    <title>Pacientes - Clínica Vida Salud</title>
    <style>
        /* Estilos básicos para que se vea ordenado */
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; display: flex; gap: 20px; }
        .form-section { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-section { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
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
        <!-- COLUMNA IZQUIERDA: FORMULARIO -->
        <div class="form-section">
            <h3>Registrar Paciente</h3>
            
            <!-- Mensajes de respuesta -->
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Paciente registrado correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                <div class="alert alert-danger">Error: La Cédula de Identidad ya existe.</div>
            <?php endif; ?>

            <!-- El action envía los datos a pacientes_procesar.php -->
            <form action="pacientes_procesar.php" method="POST">
                <label>Cédula de Identidad (CI):</label>
                <input type="text" name="ci" required placeholder="Ej: 8765432">

                <label>Nombres:</label>
                <input type="text" name="nombres" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" required>

                <label>Fecha de Nacimiento:</label>
                <input type="date" name="fecha_nacimiento" required>

                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>

                <label>Teléfono:</label>
                <input type="text" name="telefono" required>

                <button type="submit">Guardar Paciente</button>
            </form>
        </div>

        <!-- COLUMNA DERECHA: TABLA DE LISTADO -->
        <div class="table-section">
            <h3>Listado de Pacientes Activos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>CI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultamos a PostgreSQL solo los pacientes activos
                    $sql = "SELECT codigo, ci, apellidos, nombres, telefono FROM paciente WHERE estado = true ORDER BY apellidos ASC";
                    $stmt = $conexion->query($sql);
                    
                    // Ciclo while para dibujar una fila por cada paciente
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['ci']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['apellidos'] . ", " . $row['nombres']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>