<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Programa.php';
require_once __DIR__ . '/../models/TituloPrograma.php';

class ProgramaController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'programa';

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
        $titulos = TituloPrograma::all();
        $this->loadView('views/programa/create.php', ['titulos' => $titulos]);
    }

    /**
     * Guardar nuevo programa
     */
    function save() {
        try {
            $this->validatePost(['prog_codigo', 'prog_denominacion', 'prog_tipo', 'titpro_id']);
            
            $programa = new Programa(
                $this->getPost('prog_codigo'),
                $this->getPost('prog_denominacion'),
                $this->getPost('prog_tipo'),
                $this->getPost('titpro_id')
            );
            
            Programa::save($programa);
            $this->redirectWithMessage('Programa creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear el programa: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de programas
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = Programa::paginate($page, 10, $search);
        $this->loadView('views/programa/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $programa = Programa::find($this->getGet('id'));
            if (!$programa) {
                $this->redirectWithMessage('Programa no encontrado', 'error');
            }
            
            $titulos = TituloPrograma::all();
            $this->loadView('views/programa/edit.php', [
                'programa' => $programa,
                'titulos' => $titulos
            ]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar programa existente
     */
    function update() {
        try {
            $this->validatePost(['prog_codigo', 'prog_denominacion', 'prog_tipo', 'titpro_id']);
            
            $programa = new Programa(
                $this->getPost('prog_codigo'),
                $this->getPost('prog_denominacion'),
                $this->getPost('prog_tipo'),
                $this->getPost('titpro_id')
            );
            
            Programa::update($programa);
            $this->redirectWithMessage('Programa actualizado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar el programa: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar programa
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $codigo = $this->getGet('id');
            
            // Verificar dependencias
            $dependencies = Programa::hasDependencies($codigo);
            if ($dependencies !== 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar el programa porque tiene {$dependencies} asociado(s). Primero debe eliminar las relaciones.",
                    'error'
                );
            }
            
            Programa::delete($codigo);
            $this->redirectWithMessage('Programa eliminado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar el programa: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar programa
     */
    function search() {
        $this->show();
    }
}
?>
