<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Instructor.php';

class InstructorController extends Controller {
    
    protected $controllerName = 'instructor';

    function __construct() {
        AuthController::requireAuth();
    }

    /**
     * Página de inicio
     */
    function index() {
        $this->show();
    }

    /**
     * Mostrar formulario de registro
     */
    function register() {
        $this->loadView('views/instructor/create.php');
    }

    /**
     * Guardar nuevo instructor
     */
    function save() {
        try {
            // Si es centro de formación, usar su ID automáticamente
            if (hasRole('centro')) {
                $centroId = currentUserId();
            } else {
                $centroId = $this->getPost('cent_id');
            }
            
            $this->validatePost(['inst_nombres', 'inst_apellidos', 'inst_correo', 'inst_telefono', 'inst_password']);
            
            $instructor = new Instructor(
                null,
                $this->getPost('inst_nombres'),
                $this->getPost('inst_apellidos'),
                $this->getPost('inst_correo'),
                $this->getPost('inst_telefono'),
                $centroId
            );
            
            Instructor::save($instructor, $this->getPost('inst_password'));
            $this->redirectWithMessage('Instructor creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear el instructor: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de instructores
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        // Si es centro de formación, filtrar solo sus instructores
        if (hasRole('centro')) {
            $centroId = currentUserId();
            $pagination = Instructor::paginateByCentro($centroId, $page, 10, $search);
        } else {
            $pagination = Instructor::paginate($page, 10, $search);
        }
        
        $this->loadView('views/instructor/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $instructor = Instructor::find($this->getGet('id'));
            if (!$instructor) {
                $this->redirectWithMessage('Instructor no encontrado', 'error');
            }
            
            $this->loadView('views/instructor/edit.php', ['instructor' => $instructor]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar instructor existente
     */
    function update() {
        try {
            // Si es centro de formación, usar su ID automáticamente
            if (hasRole('centro')) {
                $centroId = currentUserId();
            } else {
                $centroId = $this->getPost('cent_id');
            }
            
            $this->validatePost(['inst_id', 'inst_nombres', 'inst_apellidos', 'inst_correo', 'inst_telefono']);
            
            $instructor = new Instructor(
                $this->getPost('inst_id'),
                $this->getPost('inst_nombres'),
                $this->getPost('inst_apellidos'),
                $this->getPost('inst_correo'),
                $this->getPost('inst_telefono'),
                $centroId
            );
            
            Instructor::update($instructor);
            $this->redirectWithMessage('Instructor actualizado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar el instructor: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar instructor
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            $dependencies = Instructor::hasDependencies($id);
            
            if (!empty($dependencies)) {
                $messages = [];
                if (isset($dependencies['fichas'])) {
                    $messages[] = 'es líder de ' . $dependencies['fichas'] . ' ficha(s)';
                }
                if (isset($dependencies['asignaciones'])) {
                    $messages[] = 'tiene ' . $dependencies['asignaciones'] . ' asignación(es)';
                }
                if (isset($dependencies['competencias'])) {
                    $messages[] = 'tiene ' . $dependencies['competencias'] . ' competencia(s) asignada(s)';
                }
                
                $message = 'No se puede eliminar el instructor porque ' . implode(', ', $messages) . '.';
                $this->redirectWithMessage($message, 'error');
            }
            
            Instructor::delete($id);
            $this->redirectWithMessage('Instructor eliminado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar el instructor: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar instructor
     */
    function search() {
        $this->show();
    }

    /**
     * Ver mis asignaciones (para instructores)
     */
    function misAsignaciones() {
        $instructorId = currentUserId();
        
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Asignacion.php';
        require_once __DIR__ . '/../models/Ficha.php';
        require_once __DIR__ . '/../models/Ambiente.php';
        require_once __DIR__ . '/../models/Competencia.php';
        
        $db = Database::getInstance();
        $sql = "SELECT ASIG_ID, INSTRUCTOR_inst_id, FICHA_fich_id, AMBIENTE_amb_id, COMPETENCIA_comp_id, asig_fecha_ini, asig_fecha_fin 
                FROM ASIGNACION 
                WHERE INSTRUCTOR_inst_id = ? 
                ORDER BY ASIG_ID";
        $result = $db->select($sql, [$instructorId]);
        
        $asignaciones = [];
        foreach ($result as $row) {
            $asignaciones[] = new Asignacion(
                $row['ASIG_ID'],
                $row['INSTRUCTOR_inst_id'],
                $row['FICHA_fich_id'],
                $row['AMBIENTE_amb_id'],
                $row['COMPETENCIA_comp_id'],
                $row['asig_fecha_ini'],
                $row['asig_fecha_fin']
            );
        }
        
        $this->loadView('views/instructor/mis-asignaciones.php', ['asignaciones' => $asignaciones]);
    }

    /**
     * Ver mis horarios (para instructores)
     */
    function misHorarios() {
        $instructorId = currentUserId();
        
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Asignacion.php';
        require_once __DIR__ . '/../models/DetalleAsignacion.php';
        
        $db = Database::getInstance();
        
        $sql = "SELECT a.ASIG_ID as asig_id, a.FICHA_fich_id as fich_id, a.AMBIENTE_amb_id as amb_id, 
                       a.COMPETENCIA_comp_id as comp_id, a.asig_fecha_ini, a.asig_fecha_fin,
                       d.detasig_fecha as det_fecha, d.detasig_hora_ini as det_hora_inicio, d.detasig_hora_fin as det_hora_fin
                FROM ASIGNACION a
                LEFT JOIN DETALLE_ASIGNACION d ON a.ASIG_ID = d.ASIGNACION_ASIG_ID
                WHERE a.INSTRUCTOR_inst_id = ?
                ORDER BY d.detasig_fecha, d.detasig_hora_ini";
        $horarios = $db->select($sql, [$instructorId]);
        
        $this->loadView('views/instructor/mis-horarios.php', ['horarios' => $horarios]);
    }
}
?>
