<?php
// Iniciar la sesión para poder destruirla
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión en el servidor
session_destroy();

// Redirigir al usuario a la página de inicio de sesión con el mensaje de éxito
header("Location: index.php?mensaje=logout");
exit;
?>