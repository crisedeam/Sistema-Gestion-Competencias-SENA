<?php 
// La variable $centro viene del controlador
if (!isset($centro)) {
    header('Location: index.php?c=centroFormacion&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
