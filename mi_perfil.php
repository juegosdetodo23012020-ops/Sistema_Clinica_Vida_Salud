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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Mi Perfil - Clínica Vida Salud</title>
</head>
<body>

    <div class="navbar">
        <h2>Clínica Vida Salud</h2>
        <div>
            <span>Bienvenido, <?php echo htmlspecialchars(($perfil['nombres'] ?? '') . ' ' . ($perfil['apellidos'] ?? '')); ?></span> |  
            <a href="dashboard.php">Inicio</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="panel-form" style="max-width: 800px; margin: 0 auto;">
            <h2 style="text-align: center;">Mi Perfil</h2>
            
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'exito'): ?>
                <div class="alert alert-success">¡Perfil actualizado correctamente!</div>
            <?php endif; ?>

            <form action="mi_perfil_procesar.php" method="POST">
                <input type="hidden" name="id_paciente" value="<?php echo $perfil['id_paciente']; ?>">

                <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 5px; color: var(--primary); margin-top: 20px;">Datos Personales (No editables)</h3>
                <div class="form-grid">
                    <div>
                        <label>Documento de Identidad (CI):</label>
                        <input type="text" value="<?php echo htmlspecialchars($perfil['ci'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                    <div>
                        <label>Nombres Completos:</label>
                        <input type="text" value="<?php echo htmlspecialchars(($perfil['nombres'] ?? '') . ' ' . ($perfil['apellidos'] ?? '')); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                </div>

                <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 5px; color: var(--primary); margin-top: 30px;">Datos de Contacto (Editables)</h3>
                <div class="form-grid">
                    <div>
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($perfil['telefono'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label>Correo Electrónico:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($perfil['email'] ?? ''); ?>">
                    </div>
                    <div class="full-width">
                        <label>Dirección:</label>
                        <input type="text" name="direccion" value="<?php echo htmlspecialchars($perfil['direccion'] ?? ''); ?>">
                    </div>
                </div>

                <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 5px; color: var(--primary); margin-top: 30px;">Información Médica (Editables)</h3>
                <div class="form-grid">
                    <div>
                        <label>Tipo de Sangre:</label>
                        <input type="text" name="tipo_sangre" value="<?php echo htmlspecialchars($perfil['tipo_sangre'] ?? ''); ?>" placeholder="Ej: O+">
                    </div>
                    <div>
                        <label>Alergias:</label>
                        <input type="text" name="alergias" value="<?php echo htmlspecialchars($perfil['alergias'] ?? ''); ?>">
                    </div>
                    <div class="full-width">
                        <label>Enfermedades Crónicas:</label>
                        <textarea name="enfermedades_cronicas" rows="2"><?php echo htmlspecialchars($perfil['enfermedades_cronicas'] ?? ''); ?></textarea>
                    </div>
                </div>

                <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 5px; color: var(--primary); margin-top: 30px;">Contacto de Emergencia (Editables)</h3>
                <div class="form-grid">
                    <div>
                        <label>Nombre del Contacto:</label>
                        <input type="text" name="emergencia_nombre" value="<?php echo htmlspecialchars($perfil['emergencia_nombre'] ?? ''); ?>">
                    </div>
                    <div>
                        <label>Parentesco:</label>
                        <input type="text" name="parentesco" value="<?php echo htmlspecialchars($perfil['parentesco'] ?? ''); ?>">
                    </div>
                    <div class="full-width">
                        <label>Teléfono de Emergencia:</label>
                        <input type="text" name="emergencia_telefono" value="<?php echo htmlspecialchars($perfil['emergencia_telefono'] ?? ''); ?>">
                    </div>
                </div>

                <button type="submit" style="background: var(--primary-gradient); margin-top: 20px;">Actualizar Mi Perfil</button>
            </form>
        </div>
    </div>

</body>
</html>