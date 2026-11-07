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
    <title>Pacientes - Clínica Vida Salud</title>
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
                <h3>Registrar Paciente</h3>
                
                <!-- BLOQUE DE MENSAJES CORREGIDO (Solo un if, varios elseif, un solo endif) -->
                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                    <div class="alert alert-success">
                        ¡Paciente registrado correctamente!<br>
                        <strong>Usuario portal:</strong> <?= htmlspecialchars($_GET['user']) ?><br>
                        <strong>Clave temporal:</strong> <?= htmlspecialchars($_GET['pass']) ?>
                    </div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                    <div class="alert alert-danger">Error: La Cédula de Identidad ya existe.</div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'email_duplicado'): ?>
                    <div class="alert alert-danger">Error: El correo electrónico ya está registrado en otro usuario.</div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'sistema'): ?>
                    <div class="alert alert-danger">Error en el sistema al guardar. Revisa el código.</div>
                <?php endif; ?>
                <!-- FIN DEL BLOQUE DE MENSAJES -->

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
                    
                    <!-- NUEVO CAMPO -->
                    <label>Correo Electrónico:</label>
                    <input type="email" name="email" required placeholder="Ej: paciente@correo.com">

                    <button type="submit">Guardar Paciente</button>
                </form>
            </div>

            <!-- COLUMNA DERECHA: TABLA DE LISTADO -->
            <div class="panel-table">
                <h3>Listado de Pacientes Activos</h3>
                <div class="table-responsive">
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
                            $sql = "SELECT codigo, ci, apellidos, nombres, telefono FROM paciente WHERE estado = true ORDER BY apellidos ASC";
                            $stmt = $conexion->query($sql);
                            
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

        </div> <!-- Fin layout-split -->
    </div> <!-- Fin main-content -->

</body>
</html>