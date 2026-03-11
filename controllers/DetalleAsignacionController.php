<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/DetalleAsignacion.php';
require_once __DIR__ . '/../models/Asignacion.php';
require_once __DIR__ . '/../Database.php';

class DetalleAsignacionController {
    
    function __construct() {}

    function index() {
        // Redirigir a la lista de asignaciones
        header('Location: index.php?c=asignacion&a=show');
        exit;
    }

    function register() {
        // Obtener ID de asignación
        $asignacionId = $_GET['asig_id'] ?? null;
        
        if (!$asignacionId) {
            $_SESSION['mensaje'] = 'ID de asignación no proporcionado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }
        
        // Obtener información de la asignación
        $asignacion = Asignacion::find($asignacionId);
        
        if (!$asignacion) {
            $_SESSION['mensaje'] = 'Asignación no encontrada';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }
        
        require_once('views/detalleAsignacion/create.php');
    }

    function save() {
        try {
            $asignacionId = $_POST['asig_id'];
            
            // Validar datos
            if (empty($_POST['detasig_fecha']) || empty($_POST['detasig_hora_ini']) || empty($_POST['detasig_hora_fin'])) {
                throw new Exception('Todos los campos son obligatorios');
            }
            
            // Validar que hora fin sea mayor que hora inicio
            if ($_POST['detasig_hora_fin'] <= $_POST['detasig_hora_ini']) {
                throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
            }
            
            // Crear detalle de asignación
            $detalle = new DetalleAsignacion(
                null,
                $asignacionId,
                $_POST['detasig_fecha'],
                $_POST['detasig_hora_ini'],
                $_POST['detasig_hora_fin']
            );
            
            DetalleAsignacion::save($detalle);
            
            $_SESSION['mensaje'] = 'Detalle de asignación creado exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        // Redirigir a los detalles de la asignación
        header('Location: index.php?c=asignacion&a=detalles&id=' . $asignacionId);
        exit;
    }

    function show() {
        // Obtener ID de asignación
        $asignacionId = $_GET['asig_id'] ?? null;
        
        if (!$asignacionId) {
            $_SESSION['mensaje'] = 'ID de asignación no proporcionado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }
        
        // Obtener detalles de la asignación
        $asignacion = Asignacion::find($asignacionId);
        $detallesAsignacion = DetalleAsignacion::searchByAsignacion($asignacionId);
        
        require_once('views/detalleAsignacion/index.php');
    }

    function updateshow() {
        $id = $_GET['id'];
        $detalle = DetalleAsignacion::find($id);
        
        if (!$detalle) {
            $_SESSION['mensaje'] = 'Detalle de asignación no encontrado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }
        
        // Obtener información de la asignación
        $asignacion = Asignacion::find($detalle->getAsignacionId());
        
        require_once('views/detalleAsignacion/edit.php');
    }

    function update() {
        try {
            // Validar datos
            if (empty($_POST['detasig_fecha']) || empty($_POST['detasig_hora_ini']) || empty($_POST['detasig_hora_fin'])) {
                throw new Exception('Todos los campos son obligatorios');
            }
            
            // Validar que hora fin sea mayor que hora inicio
            if ($_POST['detasig_hora_fin'] <= $_POST['detasig_hora_ini']) {
                throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
            }
            
            // Actualizar detalle de asignación
            $detalle = new DetalleAsignacion(
                $_POST['detasig_id'],
                $_POST['asig_id'],
                $_POST['detasig_fecha'],
                $_POST['detasig_hora_ini'],
                $_POST['detasig_hora_fin']
            );
            
            DetalleAsignacion::update($detalle);
            
            $_SESSION['mensaje'] = 'Detalle de asignación actualizado exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        // Redirigir a los detalles de la asignación
        header('Location: index.php?c=asignacion&a=detalles&id=' . $_POST['asig_id']);
        exit;
    }

    function delete() {
        try {
            $id = $_GET['id'];
            $asignacionId = $_GET['asig_id'] ?? null;
            
            DetalleAsignacion::delete($id);
            
            $_SESSION['mensaje'] = 'Detalle de asignación eliminado exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al eliminar: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        // Redirigir a los detalles de la asignación
        if ($asignacionId) {
            header('Location: index.php?c=asignacion&a=detalles&id=' . $asignacionId);
        } else {
            header('Location: index.php?c=asignacion&a=show');
        }
        exit;
    }

    function search() {
        // Buscar por asignación
        if (!empty($_POST['asig_id'])) {
            $asignacionId = $_POST['asig_id'];
            $detallesAsignacion = DetalleAsignacion::searchByAsignacion($asignacionId);
            $asignacion = Asignacion::find($asignacionId);
            require_once('views/detalleAsignacion/index.php');
        } else {
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }
    }

    function error() {
        require_once('views/error.php');
    }
}
?>
