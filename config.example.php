<?php
/**
 * Archivo de configuración de ejemplo
 * 
 * INSTRUCCIONES:
 * 1. Copiar este archivo como config.php
 * 2. Modificar los valores de conexión a la base de datos
 * 3. Ajustar otras configuraciones según sea necesario
 */

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');          // Host de la base de datos
define('DB_NAME', 'ProgSENA');           // Nombre de la base de datos
define('DB_USER', 'root');               // Usuario de la base de datos
define('DB_PASS', '');                   // Contraseña de la base de datos
define('DB_CHARSET', 'utf8mb4');         // Charset de la conexión

// ============================================
// CONFIGURACIÓN DE RUTAS
// ============================================
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('/index.php', '', $scriptName);

define('BASE_PATH', $basePath);
define('BASE_URL', $basePath . '/index.php');
define('VIEWS_PATH', __DIR__ . '/views/');
define('CONTROLLERS_PATH', __DIR__ . '/controllers/');
define('MODELS_PATH', __DIR__ . '/models/');

// ============================================
// CONFIGURACIÓN DE SESIÓN
// ============================================
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================
define('APP_NAME', 'ProgSENA');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Bogota');

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// ============================================
// COLORES DEL TEMA (SENA)
// ============================================
define('PRIMARY_COLOR', '#1ABC9C');
define('PRIMARY_DARK', '#148F77');
define('PRIMARY_HOVER', '#48C9B0');

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Construir URL del sistema
 */
function url($controller, $action = 'show', $params = []) {
    $url = BASE_URL . '?controller=' . $controller . '&action=' . $action;
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    return $url;
}

/**
 * Redirigir a una URL del sistema
 */
function redirect($controller, $action = 'show', $params = []) {
    header('Location: ' . url($controller, $action, $params));
    exit;
}

/**
 * Escapar HTML para prevenir XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Obtener el rol del usuario actual
 */
function currentRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Obtener el ID del usuario actual
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtener el nombre del usuario actual
 */
function currentUserName() {
    return $_SESSION['user_name'] ?? 'Usuario';
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole($role) {
    return currentRole() === $role;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dateObj = is_string($date) ? new DateTime($date) : $date;
    return $dateObj->format($format);
}

/**
 * Formatear hora
 */
function formatTime($time) {
    if (empty($time)) return '';
    return substr($time, 0, 5); // HH:MM
}

// ============================================
// INICIAR SESIÓN
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Verificar timeout de sesión
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
