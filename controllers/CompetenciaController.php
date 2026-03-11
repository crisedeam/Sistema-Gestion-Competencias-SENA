<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Competencia.php';

class CompetenciaController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'competencia';

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
        $this->loadView('views/competencia/create.php');
    }

    /**
     * Guardar nueva competencia
     */
    function save() {
        try {
            $this->validatePost(['comp_nombre_corto', 'comp_nombre_unidad', 'comp_horas']);
            
            $competencia = new Competencia(
                null,
                $this->getPost('comp_nombre_corto'),
                $this->getPost('comp_nombre_unidad'),
                $this->getPost('comp_horas')
            );
            
            Competencia::save($competencia);
            $this->redirectWithMessage('Competencia creada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear la competencia: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de competencias
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = Competencia::paginate($page, 10, $search);
        $this->loadView('views/competencia/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $competencia = Competencia::find($this->getGet('id'));
            if (!$competencia) {
                $this->redirectWithMessage('Competencia no encontrada', 'error');
            }
            
            $this->loadView('views/competencia/edit.php', ['competencia' => $competencia]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar competencia existente
     */
    function update() {
        try {
            $this->validatePost(['comp_id', 'comp_nombre_corto', 'comp_nombre_unidad', 'comp_horas']);
            
            $competencia = new Competencia(
                $this->getPost('comp_id'),
                $this->getPost('comp_nombre_corto'),
                $this->getPost('comp_nombre_unidad'),
                $this->getPost('comp_horas')
            );
            
            Competencia::update($competencia);
            $this->redirectWithMessage('Competencia actualizada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar la competencia: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar competencia
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $dependencies = Competencia::hasDependencies($id);
            if ($dependencies !== 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar la competencia porque está asociada a {$dependencies}. Primero debe eliminar las relaciones.",
                    'error'
                );
            }
            
            Competencia::delete($id);
            $this->redirectWithMessage('Competencia eliminada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar la competencia: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar competencia
     */
    function search() {
        $this->show();
    }
}
?>
