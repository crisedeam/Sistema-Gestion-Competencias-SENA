<?php 
// La variable $instructor viene del controlador
// La variable $centroFormacion viene del controlador si es coordinador
if (!isset($instructor)) {
    header('Location: index.php?c=instructor&a=show');
    exit;
}

require_once('form.php');
?>
