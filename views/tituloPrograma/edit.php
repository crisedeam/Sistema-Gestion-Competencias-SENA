<?php 
// La variable $titulo viene del controlador
if (!isset($titulo)) {
    header('Location: index.php?c=tituloPrograma&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
