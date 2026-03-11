<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Coordinacion.php';
require_once __DIR__ . '/../models/CentroFormacion.php';

class CoordinacionController extends Controller {
    
    protected $controllerName = 'coordinacion';

    function __construct() {
        // Verificar autenticación
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
        // Si es centro de formación, usar su ID automáticamente
        if (hasRole('centro')) {
            $centroId = currentUserId();
            $centro = CentroFormacion::find($centroId);
            $centros = [$centro];
        } else {
            $centros = CentroFormacion::all();
        }
        
        $this->loadView('views/coordinacion/create.php', ['centros' => $centros]);
    }

    /**
     * Guardar nueva coordinación
     */
    function save() {
        try {
            // Si es centro de formación, usar su ID automáticamente
            if (hasRole('centro')) {
                $centroId = currentUserId();
            } else {
                $this->validatePost(['CENTRO_FORMACION_cent_id']);
                $centroId = $this->getPost('CENTRO_FORMACION_cent_id');
            }
            
            $this->validatePost(['coord_descripcion', 'coord_nombre_coordinador', 'coord_correo', 'coord_password']);
            
            $coordinacion = new Coordinacion(
                null,
                $this->getPost('coord_descripcion'),
                $centroId,
                $this->getPost('coord_nombre_coordinador'),
                $this->getPost('coord_correo')
            );
            
            Coordinacion::save($coordinacion, $this->getPost('coord_password'));
            $this->redirectWithMessage('Coordinación creada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear la coordinación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar listado de coordinaciones
     */
    function show() {
        $page = (int)$this->getGet('page', 1);
        $search = $this->getGet('search', '');
        
        // Si es centro de formación, filtrar solo sus coordinaciones
        if (hasRole('centro')) {
            $centroId = currentUserId();
            $pagination = Coordinacion::paginateByCentro($centroId, $page, 10, $search);
        } else {
            $pagination = Coordinacion::paginate($page, 10, $search);
        }
        
        $this->loadView('views/coordinacion/index.php', ['pagination' => $pagination]);
    }

    /**
     * Mostrar formulario de actualización
     */
    function updateshow() {
        try {
            $this->validateGet(['id']);
            
            $coordinacion = Coordinacion::find($this->getGet('id'));
            if (!$coordinacion) {
                $this->redirectWithMessage('Coordinación no encontrada', 'error');
            }
            
            $centros = CentroFormacion::all();
            $this->loadView('views/coordinacion/edit.php', [
                'coordinacion' => $coordinacion,
                'centros' => $centros
            ]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar coordinación existente
     */
    function update() {
        try {
            $this->validatePost(['coord_id', 'coord_descripcion', 'CENTRO_FORMACION_cent_id', 'coord_nombre_coordinador', 'coord_correo']);
            
            $coordinacion = new Coordinacion(
                $this->getPost('coord_id'),
                $this->getPost('coord_descripcion'),
                $this->getPost('CENTRO_FORMACION_cent_id'),
                $this->getPost('coord_nombre_coordinador'),
                $this->getPost('coord_correo')
            );
            
            Coordinacion::update($coordinacion);
            $this->redirectWithMessage('Coordinación actualizada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar la coordinación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Eliminar coordinación
     */
    function delete() {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');
            
            // Verificar dependencias
            $countFichas = Coordinacion::hasDependencies($id);
            if ($countFichas > 0) {
                $this->redirectWithMessage(
                    "No se puede eliminar la coordinación porque tiene {$countFichas} ficha(s) asociada(s). Primero debe eliminar las fichas.",
                    'error'
                );
            }
            
            Coordinacion::delete($id);
            $this->redirectWithMessage('Coordinación eliminada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar la coordinación: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar coordinación
     */
    function search() {
        $this->show();
    }
}
?>
