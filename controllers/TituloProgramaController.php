<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/TituloPrograma.php';

class TituloProgramaController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'tituloPrograma';

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
        $this->loadView('views/tituloPrograma/create.php');
    }

    /**
     * Guardar nuevo título
     */
    function save() {
        try {
            $this->validatePost(['titpro_nombre']);
            
            $titulo = new TituloPrograma(
                null,
                $this->getPost('titpro_nombre')
            );
            
            TituloPrograma::save($titulo);
            $this->redirectWithMessage('Título de programa creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear el título: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de títulos
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = TituloPrograma::paginate($page, 10, $search);
        $this->loadView('views/tituloPrograma/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $titulo = TituloPrograma::find($this->getGet('id'));
            if (!$titulo) {
                $this->redirectWithMessage('Título no encontrado', 'error');
            }
            
            $this->loadView('views/tituloPrograma/edit.php', ['titulo' => $titulo]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar título existente
     */
    function update() {
        try {
            $this->validatePost(['titpro_id', 'titpro_nombre']);
            
            $titulo = new TituloPrograma(
                $this->getPost('titpro_id'),
                $this->getPost('titpro_nombre')
            );
            
            TituloPrograma::update($titulo);
            $this->redirectWithMessage('Título de programa actualizado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar el título: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar título
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $countProgramas = TituloPrograma::hasDependencies($id);
            if ($countProgramas > 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar el título porque tiene {$countProgramas} programa(s) asociado(s). Primero debe eliminar o reasignar los programas.",
                    'error'
                );
            }
            
            TituloPrograma::delete($id);
            $this->redirectWithMessage('Título de programa eliminado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar el título: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar título
     */
    function search() {
        $this->show();
    }
}
?>
