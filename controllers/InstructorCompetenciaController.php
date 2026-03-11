<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/InstructorCompetencia.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Controlador InstructorCompetenciaController
 * Gestiona las operaciones CRUD para la relación instructor-competencia
 */
class InstructorCompetenciaController extends Controller
{


    function __construct()
    {
        AuthController::requireAuth();
    }

    protected $controllerName = 'instructorCompetencia';

    /**
     * Página de inicio - redirige a show
     */
    function index()
    {
        $this->show();
    }

    /**
     * Mostrar formulario de creación
     */
    function register()
    {
        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Programa.php';

        // Si es coordinador, filtrar instructores por centro de formación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../models/Coordinacion.php';
            $coordinacion = Coordinacion::searchById($coor_id);
            if ($coordinacion) {
                $instructores = Instructor::getByCentroFormacion($coordinacion->getCentroFormacionId());
            } else {
                $instructores = Instructor::all();
            }
        } else {
            $instructores = Instructor::all();
        }

        $programas = Programa::all();
        require_once('views/instructorCompetencia/create.php');
    }

    /**
     * Guardar nueva relación instructor-competencia
     */
    function save()
    {
        try {
            $relacion = new InstructorCompetencia(
                null,
                $_POST['INSTRUCTOR_inst_id'],
                $_POST['COMPETENCIA_PROGRAMA_PROGRAMA_prog_id'],
                $_POST['COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id'],
                $_POST['inscomp_vigencia']
            );

            InstructorCompetencia::save($relacion);

            $_SESSION['mensaje'] = 'Competencia asignada al instructor exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al asignar la competencia: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        $roleParam = isset($_GET['role']) ? '&role=' . $_GET['role'] : '';
        $coorParam = isset($_GET['coor_id']) ? '&coor_id=' . $_GET['coor_id'] : '';
        header('Location: index.php?c=instructorCompetencia&a=show' . $roleParam . $coorParam);
        exit;
    }

    /**
     * Mostrar listado paginado de relaciones
     */
    function show()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Si es coordinador, filtrar por centro de formación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../models/Coordinacion.php';
            $coordinacion = Coordinacion::searchById($coor_id);
            if ($coordinacion) {
                $listaRelaciones = InstructorCompetencia::getByCentroFormacion($coordinacion->getCentroFormacionId());

                // Aplicar búsqueda si existe
                if (!empty($search)) {
                    $listaRelaciones = $this->filtrarRelaciones($listaRelaciones, $search);
                }

                // Paginación manual
                $pagination = $this->paginarManual($listaRelaciones, $page, 10);
            } else {
                $pagination = InstructorCompetencia::paginate($page, 10, $search);
            }
        } else {
            $pagination = InstructorCompetencia::paginate($page, 10, $search);
        }

        require_once('views/instructorCompetencia/index.php');
    }

    /**
     * Mostrar formulario de edición
     */
    function updateshow()
    {
        $id = $_GET['id'];
        $relacion = InstructorCompetencia::searchById($id);

        if (!$relacion) {
            $_SESSION['mensaje'] = 'Relación no encontrada';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=instructorCompetencia&a=show');
            exit;
        }

        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Programa.php';

        // Si es coordinador, filtrar instructores por centro de formación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../models/Coordinacion.php';
            $coordinacion = Coordinacion::searchById($coor_id);
            if ($coordinacion) {
                $instructores = Instructor::getByCentroFormacion($coordinacion->getCentroFormacionId());
            } else {
                $instructores = Instructor::all();
            }
        } else {
            $instructores = Instructor::all();
        }

        $programas = Programa::all();
        require_once('views/instructorCompetencia/edit.php');
    }

    /**
     * Actualizar relación existente
     */
    function update()
    {
        try {
            $relacion = new InstructorCompetencia(
                $_POST['inscomp_id'],
                $_POST['INSTRUCTOR_inst_id'],
                $_POST['COMPETENCIA_PROGRAMA_PROGRAMA_prog_id'],
                $_POST['COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id'],
                $_POST['inscomp_vigencia']
            );

            InstructorCompetencia::update($relacion);

            $_SESSION['mensaje'] = 'Asignación de competencia actualizada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al actualizar la asignación: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        $roleParam = isset($_GET['role']) ? '&role=' . $_GET['role'] : '';
        $coorParam = isset($_GET['coor_id']) ? '&coor_id=' . $_GET['coor_id'] : '';
        header('Location: index.php?c=instructorCompetencia&a=show' . $roleParam . $coorParam);
        exit;
    }

    /**
     * Eliminar relación
     */
    function delete()
    {
        try {
            $id = $_GET['id'];

            // Verificar dependencias
            $dependencies = InstructorCompetencia::hasDependencies($id);

            if (!empty($dependencies)) {
                $messages = [];
                if (isset($dependencies['asignaciones'])) {
                    $messages[] = 'tiene ' . $dependencies['asignaciones'] . ' asignación(es)';
                }

                $message = 'No se puede eliminar esta relación porque el instructor ' . implode(', ', $messages) . ' con esta competencia.';

                $_SESSION['mensaje'] = $message;
                $_SESSION['tipo_mensaje'] = 'error';
            } else {
                InstructorCompetencia::delete($id);
                $_SESSION['mensaje'] = 'Relación instructor-competencia eliminada exitosamente';
                $_SESSION['tipo_mensaje'] = 'success';
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al eliminar la relación: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        $roleParam = isset($_GET['role']) ? '&role=' . $_GET['role'] : '';
        $coorParam = isset($_GET['coor_id']) ? '&coor_id=' . $_GET['coor_id'] : '';
        header('Location: index.php?c=instructorCompetencia&a=show' . $roleParam . $coorParam);
        exit;
    }

    /**
     * Buscar relaciones
     */
    function search()
    {
        $search = !empty($_POST['instcomp_id']) ? $_POST['instcomp_id'] : '';
        $pagination = InstructorCompetencia::paginate(1, 10, $search);
        require_once('views/instructorCompetencia/index.php');
    }

    /**
     * API: Obtener competencias de un programa (JSON)
     */
    function getCompetenciasByPrograma()
    {
        header('Content-Type: application/json');

        try {
            $programaCodigo = $_GET['prog_id'] ?? null;

            if (!$programaCodigo) {
                echo json_encode(['success' => false, 'message' => 'Código de programa no proporcionado']);
                return;
            }

            require_once __DIR__ . '/../models/CompetenciaPrograma.php';
            $competencias = CompetenciaPrograma::getCompetenciasByPrograma($programaCodigo);

            echo json_encode(['success' => true, 'competencias' => $competencias]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Helper: Filtrar relaciones por término de búsqueda
     * @param array $relaciones Array de relaciones
     * @param string $search Término de búsqueda
     * @return array Relaciones filtradas
     */
    private function filtrarRelaciones($relaciones, $search)
    {
        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Programa.php';
        require_once __DIR__ . '/../models/Competencia.php';

        return array_filter($relaciones, function ($relacion) use ($search) {
            $instructor = Instructor::searchById($relacion->getInstructorId());
            $programa = Programa::searchById($relacion->getProgramaId());
            $competencia = Competencia::searchById($relacion->getCompetenciaId());

            $nombreInstructor = $instructor ? $instructor->getNombres() . ' ' . $instructor->getApellidos() : '';
            $nombrePrograma = $programa ? $programa->getDenominacion() : '';
            $nombreCompetencia = $competencia ? $competencia->getNombreCorto() : '';

            return stripos($relacion->getId(), $search) !== false ||
                stripos($nombreInstructor, $search) !== false ||
                stripos($nombrePrograma, $search) !== false ||
                stripos($nombreCompetencia, $search) !== false;
        });
    }

    /**
     * Helper: Paginar array manualmente
     * @param array $items Array de items
     * @param int $page Página actual
     * @param int $perPage Items por página
     * @return array Array con datos de paginación
     */
    private function paginarManual($items, $page, $perPage)
    {
        $totalItems = count($items);
        $totalPaginas = ceil($totalItems / $perPage);
        $page = max(1, min($page, $totalPaginas > 0 ? $totalPaginas : 1));
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($items, $offset, $perPage),
            'current_page' => $page,
            'total_pages' => $totalPaginas,
            'total_items' => $totalItems,
            'items_per_page' => $perPage
        ];
    }

    /**
     * Página de error
     */
    function error()
    {
        require_once('views/error.php');
    }
}
?>