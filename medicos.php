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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Médicos - Clínica Vida Salud</title>
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
                <h3>Registrar Médico</h3>
                
                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                    <div class="alert alert-success">¡Médico registrado correctamente!</div>
                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                    <div class="alert alert-danger">Error: El registro profesional o el correo ya existe.</div>
                <?php endif; ?>

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
                    <select name="especialidades[]" multiple required style="height: 80px;">
                        <?php
                        $esp_sql = "SELECT id_especialidad, nombre FROM especialidad WHERE estado = true";
                        $esp_stmt = $conexion->query($esp_sql);
                        while ($esp = $esp_stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $esp['id_especialidad'] . "'>" . htmlspecialchars($esp['nombre']) . "</option>";
                        }
                        ?>
                        <label style="display: flex; align-items: center; gap: 10px; margin-top: 10px; margin-bottom: 15px; cursor: pointer;">
                        <input type="checkbox" name="puede_marcar_atendida" value="1" style="width: auto; margin: 0; transform: scale(1.3);">
                         <span>Permitir que este médico gestione la asistencia de sus citas (<b>Atendida / Inasistencia</b>)</span>
                    </label>
                    </select>

                    <button type="submit">Guardar Médico</button>
                </form>
            </div>

            <!-- COLUMNA DERECHA: TABLA DE LISTADO -->
            <div class="panel-table">
                <h3>Médicos Registrados</h3>
                <div class="table-responsive">
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

        </div> <!-- Fin layout-split -->
    </div> <!-- Fin main-content -->

</body>
</html>