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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Especialidades - Clínica Vida Salud</title>
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
        
        <div class="layout-split">
            
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="panel-form">
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

            <!-- COLUMNA DERECHA: TABLA DE LISTADO -->
            <div class="panel-table">
                <h3>Especialidades Registradas</h3>
                <div class="table-responsive">
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

        </div> <!-- Fin layout-split -->
    </div> <!-- Fin main-content -->

</body>
</html>