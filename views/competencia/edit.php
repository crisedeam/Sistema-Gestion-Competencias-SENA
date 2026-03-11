<?php 
// La variable $competencia viene del controlador
if (!isset($competencia)) {
    header('Location: index.php?c=competencia&a=show');
    exit;
}

// Redirigir al formulario unificado
require_once __DIR__ . '/form.php';
?>
