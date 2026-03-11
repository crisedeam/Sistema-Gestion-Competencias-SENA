<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Sede.php';

class SedeController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'sede';
    
    /**
     * Página de inicio
     */
    function index() {
        $this->loadView('views/sede/index.php');
    }

    /**
     * Mostrar formulario de registro
     */
    function register() {
        $this->loadView('views/sede/create.php');
    }

    /**
     * Guardar nueva sede
     */
    function save() {
        try {
            $this->validatePost(['sede_nombre']);
            
            $sede = new Sede(null, $this->getPost('sede_nombre'));
            Sede::save($sede);
            $this->redirectWithMessage('Sede creada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear la sede: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de sedes
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = Sede::paginate($page, 10, $search);
        $this->loadView('views/sede/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $sede = Sede::find($this->getGet('id'));
            if (!$sede) {
                $this->redirectWithMessage('Sede no encontrada', 'error');
            }
            
            $this->loadView('views/sede/edit.php', ['sede' => $sede]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar sede existente
     */
    function update() {
        try {
            $this->validatePost(['sede_id', 'sede_nombre']);
            
            $sede = new Sede($this->getPost('sede_id'), $this->getPost('sede_nombre'));
            Sede::update($sede);
            $this->redirectWithMessage('Sede actualizada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar la sede: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar sede
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $countAmbientes = Sede::hasDependencies($id);
            if ($countAmbientes > 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar la sede porque tiene {$countAmbientes} ambiente(s) asociado(s). Primero debe eliminar los ambientes.",
                    'error'
                );
            }
            
            Sede::delete($id);
            $this->redirectWithMessage('Sede eliminada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar la sede: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar sede
     */
    function search() {
        $this->show(); // Reutilizar el método show que ya maneja búsqueda
    }
}
?>
