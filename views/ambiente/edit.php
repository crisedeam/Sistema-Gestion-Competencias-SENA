<?php 
// La variable $ambiente viene del controlador
if (!isset($ambiente)) {
    header('Location: index.php?c=ambiente&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
