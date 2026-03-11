<?php
/**
 * Archivo principal - Enrutador MVC
 * Este archivo maneja todas las peticiones y las dirige al controlador correspondiente
 */

// Iniciar sesión
session_start();

// Cargar configuración global
require_once 'config.php';

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Configuración de errores (solo para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener parámetros de la URL
$controller = isset($_GET['controller']) ? $_GET['controller'] : (isset($_GET['c']) ? $_GET['c'] : null);
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_GET['a']) ? $_GET['a'] : 'index');

// Si no hay controlador especificado, verificar autenticación
if (!$controller) {
    // Si no hay sesión, redirigir al login
    if (!isset($_SESSION['user_id'])) {
        $controller = 'auth';
        $action = 'login';
    } else {
        // Si hay sesión, ir al dashboard
        $controller = 'dashboard';
        $action = 'index';
    }
}

// Capitalizar el nombre del controlador
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = 'controllers/' . $controllerName . '.php';

// Verificar si el archivo del controlador existe
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Crear instancia del controlador
    $controllerInstance = new $controllerName();
    
    // Verificar si el método existe
    if (method_exists($controllerInstance, $action)) {
        // Ejecutar el método
        $controllerInstance->$action();
    } else {
        // Método no encontrado
        echo "Error: El método '$action' no existe en el controlador '$controllerName'";
    }
} else {
    // Controlador no encontrado
    echo "Error: El controlador '$controllerName' no existe";
}
?>
