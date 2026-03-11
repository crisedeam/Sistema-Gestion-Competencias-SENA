<?php 
// La variable $programa viene del controlador
if (!isset($programa)) {
    header('Location: index.php?c=programa&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
