<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Clase base para todos los controladores
 * Proporciona funcionalidad común
 */
abstract class Controller
{

    /**
     * Nombre del controlador (usado para URLs)
     * Debe ser definido por cada controlador hijo
     */
    protected $controllerName;

    /**
     * Construir URL de redirección con parámetros
     */
    protected function buildRedirectUrl($action = 'show', $params = [])
    {
        // Preservar coor_id de la URL actual si existe y no se proporcionó explícitamente
        if (!isset($params['coor_id']) && isset($_GET['coor_id'])) {
            $params['coor_id'] = $_GET['coor_id'];
        }

        return url($this->controllerName, $action, $params);
    }

    /**
     * Establecer mensaje de sesión
     */
    protected function setMessage($mensaje, $tipo = 'success')
    {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = $tipo;
    }

    /**
     * Redirigir con mensaje
     */
    protected function redirectWithMessage($mensaje, $tipo = 'success', $action = 'show', $params = [])
    {
        $this->setMessage($mensaje, $tipo);
        header('Location: ' . $this->buildRedirectUrl($action, $params));
        exit;
    }

    /**
     * Redirigir sin mensaje
     */
    protected function redirect($action = 'show', $params = [])
    {
        header('Location: ' . $this->buildRedirectUrl($action, $params));
        exit;
    }

    /**
     * Validar que existan parámetros POST
     */
    protected function validatePost($requiredFields)
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Campos requeridos faltantes: ' . implode(', ', $missing));
        }

        return true;
    }

    /**
     * Validar que existan parámetros GET
     */
    protected function validateGet($requiredFields)
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($_GET[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Parámetros requeridos faltantes: ' . implode(', ', $missing));
        }

        return true;
    }

    /**
     * Obtener parámetro POST con valor por defecto
     */
    protected function getPost($key, $default = null)
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Obtener parámetro GET con valor por defecto
     */
    protected function getGet($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Cargar vista
     */
    protected function loadView($viewPath, $data = [])
    {
        // Extraer variables para la vista
        extract($data);
        require_once($viewPath);
    }

    /**
     * Página de error
     */
    public function error()
    {
        require_once('views/error.php');
    }
}
?>