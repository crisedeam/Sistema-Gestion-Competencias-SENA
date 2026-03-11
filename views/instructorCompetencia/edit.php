<?php 
// La variable $relacion viene del controlador
// Las variables $instructores y $programas vienen del controlador
if (!isset($relacion)) {
    header('Location: index.php?c=instructorCompetencia&a=show');
    exit;
}

require_once('form.php');
?>
