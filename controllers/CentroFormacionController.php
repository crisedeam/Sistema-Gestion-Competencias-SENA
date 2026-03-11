<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/CentroFormacion.php';

class CentroFormacionController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'centroFormacion';

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
        $this->loadView('views/centroFormacion/create.php');
    }

    /**
     * Guardar nuevo centro
     */
    function save() {
        try {
            $this->validatePost(['cent_nombre']);
            
            $centro = new CentroFormacion(null, $this->getPost('cent_nombre'));
            CentroFormacion::save($centro);
            $this->redirectWithMessage('Centro de formación creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear el centro: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de centros
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = CentroFormacion::paginate($page, 10, $search);
        $this->loadView('views/centroFormacion/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $centro = CentroFormacion::find($this->getGet('id'));
            if (!$centro) {
                $this->redirectWithMessage('Centro no encontrado', 'error');
            }
            
            $this->loadView('views/centroFormacion/edit.php', ['centro' => $centro]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar centro existente
     */
    function update() {
        try {
            $this->validatePost(['cent_id', 'cent_nombre']);
            
            $centro = new CentroFormacion($this->getPost('cent_id'), $this->getPost('cent_nombre'));
            CentroFormacion::update($centro);
            $this->redirectWithMessage('Centro de formación actualizado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar el centro: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar centro
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $dependencies = CentroFormacion::hasDependencies($id);
            if ($dependencies['count'] > 0) {
                $this->redirectWithMessage($dependencies['message'], 'error');
            }
            
            CentroFormacion::delete($id);
            $this->redirectWithMessage('Centro de formación eliminado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar el centro: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar centro
     */
    function search() {
        $this->show();
    }
}
?>
