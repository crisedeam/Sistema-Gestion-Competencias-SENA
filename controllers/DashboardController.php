<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/CentroFormacion.php';
require_once __DIR__ . '/../models/Coordinacion.php';
require_once __DIR__ . '/../models/Instructor.php';

class DashboardController {
    
    function __construct() {
        // Verificar que el usuario esté autenticado
        AuthController::requireAuth();
    }

    /**
     * Mostrar dashboard según el rol del usuario
     */
    function index() {
        $role = currentRole();
        $userId = currentUserId();
        
        // Obtener datos según el rol
        switch ($role) {
            case 'centro':
                $data = $this->getCentroDashboardData($userId);
                break;
            case 'coordinador':
                $data = $this->getCoordinadorDashboardData($userId);
                break;
            case 'instructor':
                $data = $this->getInstructorDashboardData($userId);
                break;
            default:
                $_SESSION['error'] = 'Rol no válido';
                redirect('auth', 'logout');
        }
        
        // Cargar vista del dashboard
        require_once('views/dashboard.php');
    }

    /**
     * Obtener datos del dashboard para Centro de Formación
     */
    private function getCentroDashboardData($centroId) {
        return [
            'role' => 'centro',
            'stats' => CentroFormacion::getDashboardStats($centroId)
        ];
    }

    /**
     * Obtener datos del dashboard para Coordinador
     */
    private function getCoordinadorDashboardData($coordinacionId) {
        return [
            'role' => 'coordinador',
            'stats' => Coordinacion::getDashboardStats($coordinacionId),
            'asignaciones_recientes' => Coordinacion::getRecentAssignments($coordinacionId),
            'ambientes_disponibilidad' => Coordinacion::getAmbientesDisponibilidad()
        ];
    }

    /**
     * Obtener datos del dashboard para Instructor
     */
    private function getInstructorDashboardData($instructorId) {
        $stats = Instructor::getDashboardStats($instructorId);
        $proximaClase = Instructor::getNextClass($instructorId);
        $horarioHoy = Instructor::getTodaySchedule($instructorId);
        
        return [
            'role' => 'instructor',
            'stats' => $stats,
            'proxima_clase' => $proximaClase,
            'horario_hoy' => $horarioHoy
        ];
    }
}
?>
