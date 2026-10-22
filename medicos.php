<?php
session_start();
// Seguridad: Si no hay sesión, lo pateamos al login.
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
    <title>Médicos - Clínica Vida Salud</title>
    <style>
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
        <!-- FORMULARIO DE REGISTRO DE MÉDICO -->
        <div class="form-section">
            <h3>Registrar Médico</h3>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Médico registrado correctamente!</div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                <div class="alert alert-danger">Error: El registro profesional o el correo ya existe.</div>
            <?php endif; ?>

            <!-- El action envía los datos a medicos_procesar.php -->
            <form action="medicos_procesar.php" method="POST">
                <label>Registro Profesional:</label>
                <input type="text" name="registro_prof" required placeholder="Ej: RM-12345">

                <label>Nombres:</label>
                <input type="text" name="nombres" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" required>

                <label>Correo Electrónico (Para su cuenta de acceso):</label>
                <input type="email" name="email" required>

                <label>Contraseña de Acceso:</label>
                <input type="password" name="password" required>

                <label>Especialidades (Mantén presionado Ctrl para elegir varias):</label>
                <!-- El name con corchetes [] permite enviar un arreglo (array) de datos -->
                <select name="especialidades[]" multiple required style="height: 80px;">
                    <?php
                    // Cargamos las especialidades dinámicamente desde la base de datos
                    $esp_sql = "SELECT id_especialidad, nombre FROM especialidad WHERE estado = true";
                    $esp_stmt = $conexion->query($esp_sql);
                    while ($esp = $esp_stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $esp['id_especialidad'] . "'>" . htmlspecialchars($esp['nombre']) . "</option>";
                    }
                    ?>
                </select>

                <button type="submit">Guardar Médico</button>
            </form>
        </div>

        <!-- TABLA DE LISTADO -->
        <div class="table-section">
            <h3>Médicos Registrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Registro Prof.</th>
                        <th>Especialidades</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta SQL avanzada: STRING_AGG agrupa las especialidades en un solo texto
                    $sql = "SELECT m.codigo, m.nombres, m.apellidos, m.registro_prof, 
                                   STRING_AGG(e.nombre, ', ') AS especialidades
                            FROM medico m
                            LEFT JOIN medico_especialidad me ON m.id_medico = me.id_medico
                            LEFT JOIN especialidad e ON me.id_especialidad = e.id_especialidad
                            WHERE m.estado = true
                            GROUP BY m.id_medico, m.codigo, m.nombres, m.apellidos, m.registro_prof
                            ORDER BY m.apellidos ASC";
                    
                    $stmt = $conexion->query($sql);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td><b>" . htmlspecialchars($row['codigo']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['apellidos'] . ", " . $row['nombres']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['registro_prof']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['especialidades'] ?? 'Sin especialidad') . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>