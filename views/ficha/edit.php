<?php
if (!isset($ficha)) {
    header('Location: ' . url('ficha', 'show'));
    exit;
}

require_once __DIR__ . '/form.php';
?>