<?php 
// La variable $sede viene del controlador
if (!isset($sede)) {
    header('Location: index.php?c=sede&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
