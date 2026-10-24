<?php
// Iniciar la sesión para terminar la sesión del usuario 
session_start();

// Eliminar todas las demas sesiones y variables de sesión
$_SESSION = array();

// Terminar la sesión en el servidor
session_destroy();

// volver al ususario al index.php (login)
header("Location: index.php");
exit;
?>