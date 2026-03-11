<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/CompetenciaPrograma.php';
require_once __DIR__ . '/../models/Programa.php';
require_once __DIR__ . '/../models/Competencia.php';

class CompetenciaProgramaController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'competenciaPrograma';

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
        $programas = Programa::all();
        $competencias = Competencia::all();
        $this->loadView('views/competenciaPrograma/create.php', [
            'programas' => $programas,
            'competencias' => $competencias
        ]);
    }

    /**
     * Guardar nueva relación
     */
    function save() {
        try {
            $this->validatePost(['PROGRAMA_prog_codigo', 'COMPETENCIA_comp_id']);
            
            $relacion = new CompetenciaPrograma(
                $this->getPost('PROGRAMA_prog_codigo'),
                $this->getPost('COMPETENCIA_comp_id')
            );
            
            CompetenciaPrograma::save($relacion);
            $this->redirectWithMessage('Relación competencia-programa creada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear la relación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de relaciones
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = CompetenciaPrograma::paginate($page, 10, $search);
        $this->loadView('views/competenciaPrograma/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $relacion = CompetenciaPrograma::find($this->getGet('id'));
            if (!$relacion) {
                $this->redirectWithMessage('Relación no encontrada', 'error');
            }
            
            $programas = Programa::all();
            $competencias = Competencia::all();
            $this->loadView('views/competenciaPrograma/edit.php', [
                'relacion' => $relacion,
                'programas' => $programas,
                'competencias' => $competencias
            ]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar relación existente
     */
    function update() {
        try {
            $this->validatePost(['compprog_id', 'PROGRAMA_prog_codigo', 'COMPETENCIA_comp_id']);
            
            // Obtener los valores anteriores del ID compuesto
            $id = $this->getPost('compprog_id');
            $parts = explode('-', $id);
            
            // Eliminar la relación anterior
            if (count($parts) == 2) {
                CompetenciaPrograma::deleteByKeys($parts[0], $parts[1]);
            }
            
            // Crear la nueva relación
            $relacion = new CompetenciaPrograma(
                $this->getPost('PROGRAMA_prog_codigo'),
                $this->getPost('COMPETENCIA_comp_id')
            );
            
            CompetenciaPrograma::save($relacion);
            $this->redirectWithMessage('Relación competencia-programa actualizada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar la relación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar relación
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            $parts = explode('-', $id);
            
            if (count($parts) != 2) {
                throw new Exception('ID de relación inválido');
            }
            
            // Verificar dependencias
            $dependencies = CompetenciaPrograma::hasDependencies($parts[0], $parts[1]);
            if ($dependencies !== 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar la relación porque está asignada a {$dependencies}. Primero debe eliminar las asignaciones.",
                    'error'
                );
            }
            
            CompetenciaPrograma::delete($id);
            $this->redirectWithMessage('Relación competencia-programa eliminada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar la relación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar relación
     */
    function search() {
        $this->show();
    }

    /**
     * API: Obtener competencias de un programa (JSON)
     */
    function getCompetenciasByPrograma() {
        header('Content-Type: application/json');
        
        try {
            $programaCodigo = $this->getGet('prog_id');
            
            if (!$programaCodigo) {
                echo json_encode(['success' => false, 'message' => 'Código de programa no proporcionado']);
                return;
            }
            
            $competencias = CompetenciaPrograma::getCompetenciasByPrograma($programaCodigo);
            echo json_encode(['success' => true, 'competencias' => $competencias]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
