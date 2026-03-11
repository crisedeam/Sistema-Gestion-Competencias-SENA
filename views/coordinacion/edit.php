<?php 
// La variable $coordinacion viene del controlador
if (!isset($coordinacion)) {
    header('Location: index.php?c=coordinacion&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
