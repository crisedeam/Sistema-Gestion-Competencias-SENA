<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CentroFormacion.php';
require_once __DIR__ . '/../models/Coordinacion.php';
require_once __DIR__ . '/../models/Instructor.php';

class AuthController extends Controller {
    
    protected $controllerName = 'auth';

    /**
     * Mostrar formulario de login
     */
    function login() {
        // Si ya hay sesión activa, redirigir al dashboard
        if (isAuthenticated()) {
            redirect('dashboard', 'index');
        }
        
        $this->loadView('views/auth/login.php');
    }

    /**
     * Procesar login
     */
    function authenticate() {
        try {
            $this->validatePost(['correo', 'password']);
            
            $correo = $this->getPost('correo');
            $password = $this->getPost('password');
            
            // Intentar login como Centro de Formación
            $centro = CentroFormacion::findByEmail($correo);
            if ($centro && password_verify($password, $centro->getPassword())) {
                $this->createSession($centro->getId(), 'centro', $centro->getNombre(), $correo);
                redirect('dashboard', 'index');
            }
            
            // Intentar login como Coordinación
            $coordinacion = Coordinacion::login($correo, $password);
            if ($coordinacion) {
                $this->createSession($coordinacion->getId(), 'coordinador', $coordinacion->getNombreCoordinador(), $correo);
                redirect('dashboard', 'index');
            }
            
            // Intentar login como Instructor
            $instructor = Instructor::login($correo, $password);
            if ($instructor) {
                $nombreCompleto = $instructor->getNombres() . ' ' . $instructor->getApellidos();
                $this->createSession($instructor->getId(), 'instructor', $nombreCompleto, $correo);
                redirect('dashboard', 'index');
            }
            
            // Si llegamos aquí, las credenciales son incorrectas
            $_SESSION['error'] = 'Correo o contraseña incorrectos';
            redirect('auth', 'login');
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al iniciar sesión: ' . $e->getMessage();
            redirect('auth', 'login');
        }
    }

    /**
     * Crear sesión de usuario
     */
    private function createSession($id, $role, $nombre, $correo) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_nombre'] = $nombre;
        $_SESSION['user_correo'] = $correo;
        $_SESSION['login_time'] = time();
    }

    /**
     * Cerrar sesión
     */
    function logout() {
        session_destroy();
        redirect('auth', 'login');
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated() {
        return isAuthenticated();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole($role) {
        return hasRole($role);
    }

    /**
     * Obtener el rol del usuario actual
     */
    public static function getUserRole() {
        return currentRole();
    }

    /**
     * Requerir autenticación
     */
    public static function requireAuth() {
        if (!isAuthenticated()) {
            redirect('auth', 'login');
        }
    }

    /**
     * Requerir un rol específico
     */
    public static function requireRole($role) {
        self::requireAuth();
        if (!hasRole($role)) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta página';
            redirect('dashboard', 'index');
        }
    }
}
?>
