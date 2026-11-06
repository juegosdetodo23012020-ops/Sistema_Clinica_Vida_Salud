<?php
session_start();
require 'config/database.php';

// Seguridad: Solo el paciente (Rol 4) puede entrar aquí
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 4) {
    header("Location: dashboard.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Buscamos los datos actuales del paciente
$sql = "SELECT p.id_paciente, p.ci, p.nombres, p.apellidos, p.telefono, p.email, p.direccion, 
               im.tipo_sangre, im.alergias, im.enfermedades_cronicas,
               ce.nombre AS emergencia_nombre, ce.parentesco, ce.telefono AS emergencia_telefono
        FROM paciente p
        LEFT JOIN informacion_medica im ON p.id_paciente = im.id_paciente
        LEFT JOIN contacto_emergencia ce ON p.id_paciente = ce.id_paciente
        WHERE p.id_usuario = :id_usuario";
        
$stmt = $conexion->prepare($sql);
$stmt->execute([':id_usuario' => $id_usuario]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$perfil) {
    die("Error: No se encontró el perfil del paciente.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Clínica Vida Salud</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: #0056b3; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; background: white; margin: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .readonly { background-color: #e9ecef; cursor: not-allowed; }
        h3 { border-bottom: 2px solid #0056b3; padding-bottom: 5px; color: #0056b3; margin-top: 30px; }
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
        <h2>Mi Perfil</h2>
        
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
            <div class="alert alert-success">¡Perfil actualizado correctamente!</div>
        <?php endif; ?>

        <form action="mi_perfil_procesar.php" method="POST">
            <input type="hidden" name="id_paciente" value="<?php echo $perfil['id_paciente']; ?>">

            <h3>Datos Personales (No editables)</h3>
            <div class="grid-form">
                <div>
                    <label>Documento de Identidad (CI):</label>
                    <input type="text" value="<?php echo $perfil['ci']; ?>" readonly class="readonly">
                </div>
                <div>
                    <label>Nombres Completos:</label>
                    <input type="text" value="<?php echo $perfil['nombres'] . ' ' . $perfil['apellidos']; ?>" readonly class="readonly">
                </div>
            </div>

            <h3>Datos de Contacto (Editables)</h3>
            <div class="grid-form">
                <div>
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?php echo $perfil['telefono']; ?>" required>
                </div>
                <div>
                    <label>Correo Electrónico:</label>
                    <input type="email" name="email" value="<?php echo $perfil['email']; ?>">
                </div>
                <div class="full-width">
                    <label>Dirección:</label>
                    <input type="text" name="direccion" value="<?php echo $perfil['direccion']; ?>">
                </div>
            </div>

            <h3>Información Médica (Editables)</h3>
            <div class="grid-form">
                <div>
                    <label>Tipo de Sangre:</label>
                    <input type="text" name="tipo_sangre" value="<?php echo $perfil['tipo_sangre']; ?>" placeholder="Ej: O+">
                </div>
                <div>
                    <label>Alergias:</label>
                    <input type="text" name="alergias" value="<?php echo $perfil['alergias']; ?>">
                </div>
                <div class="full-width">
                    <label>Enfermedades Crónicas:</label>
                    <textarea name="enfermedades_cronicas" rows="2"><?php echo $perfil['enfermedades_cronicas']; ?></textarea>
                </div>
            </div>

            <h3>Contacto de Emergencia (Editables)</h3>
            <div class="grid-form">
                <div>
                    <label>Nombre del Contacto:</label>
                    <input type="text" name="emergencia_nombre" value="<?php echo $perfil['emergencia_nombre']; ?>">
                </div>
                <div>
                    <label>Parentesco:</label>
                    <input type="text" name="parentesco" value="<?php echo $perfil['parentesco']; ?>">
                </div>
                <div class="full-width">
                    <label>Teléfono de Emergencia:</label>
                    <input type="text" name="emergencia_telefono" value="<?php echo $perfil['emergencia_telefono']; ?>">
                </div>
            </div>

            <button type="submit">Actualizar Mi Perfil</button>
        </form>
    </div>

</body>
</html>