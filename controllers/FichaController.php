<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/Ficha.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';

class FichaController extends Controller
{


    function __construct()
    {
        AuthController::requireAuth();
    }

    protected $controllerName = 'ficha';

    function index()
    {
        $this->show();
    }

    function register()
    {
        require_once __DIR__ . '/../models/Programa.php';
        require_once __DIR__ . '/../models/Instructor.php';

        $programas = Programa::all();

        // Obtener coordinación y filtrar instructores
        $coordinacion = null;
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);

        require_once __DIR__ . '/../models/Coordinacion.php';
        $todasCoordinaciones = Coordinacion::all();

        if ($coor_id) {
            $coord = Coordinacion::find($coor_id);
            if ($coord) {
                require_once __DIR__ . '/../Database.php';
                $db = Database::getInstance();
                $resultado = $db->selectOne(
                    "SELECT coord_id, coord_descripcion, coord_nombre_coordinador FROM COORDINACION WHERE coord_id = ?",
                    [$coor_id]
                );
                if ($resultado) {
                    $coordinacion = $resultado;
                }

                $instructores = Instructor::getByCentroFormacion($coord->getCentroFormacionId());
            } else {
                $instructores = Instructor::all();
            }
        } else {
            $instructores = Instructor::all();
        }

        $this->loadView('views/ficha/create.php', [
            'programas' => $programas,
            'instructores' => $instructores,
            'coordinacion' => $coordinacion,
            'todasCoordinaciones' => $todasCoordinaciones
        ]);
    }

    function save()
    {
        try {
            // Si es coordinador, usar su coordinación automáticamente
            $coordinacionId = currentRole() === 'coordinador' ? currentUserId() : $_POST['coord_id'];
            
            $ficha = new Ficha(
                $_POST['fich_id'],
                $_POST['prog_codigo'],
                $_POST['inst_id_lider'],
                $_POST['fich_jornada'],
                $coordinacionId,
                $_POST['fich_fecha_ini_lectiva'],
                $_POST['fich_fecha_fin_lectiva']
            );

            // Validar duplicados antes de guardar
            if (Ficha::exists($_POST['fich_id'])) {
                throw new Exception('El número de ficha ya existe. Por favor, use un número diferente.');
            }

            Ficha::save($ficha);
            $this->redirectWithMessage('Ficha creada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al crear la ficha: ' . $e->getMessage(), 'error', 'register');
        }
    }

    function show()
    {
        $page = (int) $this->getGet('page', 1);
        $search = $this->getGet('search', '');

        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);

        // Si hay un coor_id, filtrar fichas por su coordinación
        if ($coor_id) {
            $listaFichas = Ficha::getByCoordinacion($coor_id);

            // Aplicar búsqueda si existe
            if (!empty($search)) {
                $listaFichas = $this->filtrarFichas($listaFichas, $search);
            }

            // Paginación manual
            $pagination = $this->paginarManual($listaFichas, $page, 10);
        } else {
            $pagination = Ficha::paginate($page, 10, $search);
        }

        $this->loadView('views/ficha/index.php', ['pagination' => $pagination]);
    }

    function updateshow()
    {
        try {
            $this->validateGet(['id']);

            $ficha = Ficha::find($this->getGet('id'));
            if (!$ficha) {
                $this->redirectWithMessage('Ficha no encontrada', 'error');
            }

            require_once __DIR__ . '/../models/Programa.php';
            require_once __DIR__ . '/../models/Instructor.php';

            $programas = Programa::all();

            // Obtener coordinación y filtrar instructores
            $coordinacion = null;
            $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);

            require_once __DIR__ . '/../models/Coordinacion.php';
            $todasCoordinaciones = Coordinacion::all();

            if ($coor_id) {
                $coord = Coordinacion::find($coor_id);
                if ($coord) {
                    require_once __DIR__ . '/../Database.php';
                    $db = Database::getInstance();
                    $resultado = $db->selectOne(
                        "SELECT coord_id, coord_descripcion, coord_nombre_coordinador FROM COORDINACION WHERE coord_id = ?",
                        [$coor_id]
                    );
                    if ($resultado) {
                        $coordinacion = $resultado;
                    }

                    $instructores = Instructor::getByCentroFormacion($coord->getCentroFormacionId());
                } else {
                    $instructores = Instructor::all();
                }
            } else {
                $instructores = Instructor::all();
            }

            $this->loadView('views/ficha/edit.php', [
                'ficha' => $ficha,
                'programas' => $programas,
                'instructores' => $instructores,
                'coordinacion' => $coordinacion,
                'todasCoordinaciones' => $todasCoordinaciones
            ]);
        } catch (Exception $e) {
            $this->redirectWithMessage($e->getMessage(), 'error');
        }
    }

    function update()
    {
        try {
            // Si es coordinador, usar su coordinación automáticamente
            $coordinacionId = currentRole() === 'coordinador' ? currentUserId() : $_POST['coord_id'];
            
            $ficha = new Ficha(
                $_POST['fich_id'],
                $_POST['prog_codigo'],
                $_POST['inst_id_lider'],
                $_POST['fich_jornada'],
                $coordinacionId,
                $_POST['fich_fecha_ini_lectiva'],
                $_POST['fich_fecha_fin_lectiva']
            );

            Ficha::update($ficha);
            $this->redirectWithMessage('Ficha actualizada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al actualizar la ficha: ' . $e->getMessage(), 'error');
        }
    }

    function delete()
    {
        try {
            $this->validateGet(['id']);
            $id = $this->getGet('id');

            // Verificar dependencias
            $dependencies = Ficha::hasDependencies($id);

            if (!empty($dependencies)) {
                $messages = [];
                if (isset($dependencies['asignaciones'])) {
                    $messages[] = 'tiene ' . $dependencies['asignaciones'] . ' asignación(es)';
                }

                $message = 'No se puede eliminar la ficha porque ' . implode(', ', $messages) . '. Primero debe eliminar las asignaciones.';

                $this->redirectWithMessage($message, 'error');
            }

            Ficha::delete($id);
            $this->redirectWithMessage('Ficha eliminada exitosamente');
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al eliminar la ficha: ' . $e->getMessage(), 'error');
        }
    }

    function search()
    {
        $search = $this->getPost('fich_id', '');
        $pagination = Ficha::paginate(1, 10, $search);
        $this->loadView('views/ficha/index.php', ['pagination' => $pagination]);
    }

    /**
     * Helper: Filtrar fichas por término de búsqueda
     */
    private function filtrarFichas($fichas, $search)
    {
        require_once __DIR__ . '/../models/Programa.php';
        require_once __DIR__ . '/../models/Instructor.php';

        return array_filter($fichas, function ($ficha) use ($search) {
            $programa = Programa::find($ficha->getProgramaCodigo());
            $instructor = Instructor::find($ficha->getInstructorLiderId());

            return stripos($ficha->getId(), $search) !== false ||
                stripos($ficha->getJornada(), $search) !== false ||
                ($programa && stripos($programa->getDenominacion(), $search) !== false) ||
                ($instructor && (stripos($instructor->getNombres(), $search) !== false ||
                    stripos($instructor->getApellidos(), $search) !== false));
        });
    }

    /**
     * Helper: Paginar array manualmente
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

    function error()
    {
        require_once('views/error.php');
    }
}
?>