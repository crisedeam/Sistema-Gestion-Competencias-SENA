<?php 
// La variable $relacion viene del controlador
if (!isset($relacion)) {
    header('Location: index.php?c=competenciaPrograma&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
