<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Ambiente.php';
require_once __DIR__ . '/../models/Sede.php';

class AmbienteController extends Controller {
    
    
    function __construct() {
        AuthController::requireAuth();
    }

    protected $controllerName = 'ambiente';

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
        $sedes = Sede::all();
        $this->loadView('views/ambiente/create.php', ['sedes' => $sedes]);
    }

    /**
     * Guardar nuevo ambiente
     */
    function save() {
        try {
            $this->validatePost(['amb_id', 'amb_nombre', 'sede_id']);
            
            $ambiente = new Ambiente(
                $this->getPost('amb_id'),
                $this->getPost('amb_nombre'),
                $this->getPost('sede_id')
            );
            Ambiente::save($ambiente);
            $this->redirectWithMessage('Ambiente creado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear el ambiente: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de ambientes
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        $pagination = Ambiente::paginate($page, 10, $search);
        $this->loadView('views/ambiente/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $ambiente = Ambiente::find($this->getGet('id'));
            if (!$ambiente) {
                $this->redirectWithMessage('Ambiente no encontrado', 'error');
            }
            
            $sedes = Sede::all();
            $this->loadView('views/ambiente/edit.php', [
                'ambiente' => $ambiente,
                'sedes' => $sedes
            ]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar ambiente existente
     */
    function update() {
        try {
            $this->validatePost(['amb_id', 'amb_nombre', 'sede_id']);
            
            $ambiente = new Ambiente(
                $this->getPost('amb_id'),
                $this->getPost('amb_nombre'),
                $this->getPost('sede_id')
            );
            Ambiente::update($ambiente);
            $this->redirectWithMessage('Ambiente actualizado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar el ambiente: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar ambiente
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $countAsignaciones = Ambiente::hasDependencies($id);
            if ($countAsignaciones > 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar el ambiente porque tiene {$countAsignaciones} asignación(es) asociada(s). Primero debe eliminar las asignaciones.",
                    'error'
                );
            }
            
            Ambiente::delete($id);
            $this->redirectWithMessage('Ambiente eliminado exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar el ambiente: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar ambiente
     */
    function search() {
        $this->show();
    }

    /**
     * Obtener ambientes por sede (API JSON)
     */
    function getAmbientesPorSede() {
        header('Content-Type: application/json');
        
        try {
            $sedeId = $this->getGet('sede_id');
            
            if (!$sedeId) {
                echo json_encode(['success' => false, 'message' => 'ID de sede no proporcionado']);
                return;
            }
            
            $ambientes = Ambiente::getBySede($sedeId);
            
            $result = [];
            foreach ($ambientes as $ambiente) {
                $result[] = [
                    'id' => $ambiente->getId(),
                    'nombre' => $ambiente->getNombre()
                ];
            }
            
            echo json_encode(['success' => true, 'ambientes' => $result]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
