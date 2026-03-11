<?php
$pageTitle = 'Gestión de Fichas';

// Obtener datos de paginación del controlador
$fichasPaginadas = $pagination['data'] ?? [];
$paginaActual = $pagination['current_page'] ?? 1;
$totalPaginas = $pagination['total_pages'] ?? 1;
$totalItems = $pagination['total_items'] ?? 0;
$itemsPorPagina = $pagination['items_per_page'] ?? 10;
$offset = ($paginaActual - 1) * $itemsPorPagina;
$busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';

// Cargar modelos necesarios para mostrar información
require_once __DIR__ . '/../../models/Programa.php';
require_once __DIR__ . '/../../models/Instructor.php';

ob_start();
?>

<div class="space-y-6">
    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div
            class="alert-message p-4 rounded-lg flex items-center justify-between <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <div class="flex items-center">
                <i
                    class="fas <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-3"></i>
                <span><?php echo $_SESSION['mensaje']; ?></span>
            </div>
            <button onclick="closeAlert(this.parentElement)" class="ml-4 hover:opacity-75 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Header con botón de crear -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-id-card text-primary mr-2"></i>
                Gestión de Fichas
            </h2>
            <a href="<?php echo url('ficha', 'register'); ?>"
                class="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Nueva Ficha
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <form method="GET" class="flex items-center space-x-4">
            <input type="hidden" name="controller" value="ficha">
            <input type="hidden" name="action" value="show">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($busqueda); ?>"
                        placeholder="Buscar por ID, programa, instructor o jornada..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>
            </div>
            <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-lg transition">
                Buscar
            </button>
            <?php if (!empty($busqueda)): ?>
                <a href="<?php echo url('ficha', 'show'); ?>"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    Limpiar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabla de fichas -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">ID Ficha</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Programa</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Instructor Líder
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Jornada</th>
                        <th class="px-6 py-3 text-center text-sm font-medium uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    if (!empty($fichasPaginadas)):
                        foreach ($fichasPaginadas as $ficha):
                            $programa = Programa::find($ficha->getProgramaCodigo());
                            $instructor = Instructor::find($ficha->getInstructorLiderId());
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($ficha->getId()); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo $programa ? htmlspecialchars($programa->getDenominacion()) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php
                                    if ($instructor) {
                                        echo htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos());
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    <?php
                                    switch ($ficha->getJornada()) {
                                        case 'Mañana':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Tarde':
                                            echo 'bg-orange-100 text-orange-800';
                                            break;
                                        case 'Noche':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                        <?php echo htmlspecialchars($ficha->getJornada()); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="<?php echo url('ficha', 'updateshow', isset($_GET['coor_id']) ? ['id' => $ficha->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $ficha->getId()]); ?>"
                                        class="text-primary hover:text-primary-dark mx-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                        onclick="showDeleteModal('<?php echo url('ficha', 'delete', isset($_GET['coor_id']) ? ['id' => $ficha->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $ficha->getId()]); ?>', '¿Está seguro de eliminar la ficha <?php echo htmlspecialchars($ficha->getId()); ?>?')"
                                        class="text-red-600 hover:text-red-900 mx-2" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                <p class="text-lg">
                                    <?php echo !empty($busqueda) ? 'No se encontraron resultados' : 'No hay fichas registradas'; ?>
                                </p>
                                <?php if (!empty($busqueda)): ?>
                                    <p class="text-sm mt-2">Intenta con otros términos de búsqueda</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando <?php echo $offset + 1; ?> a <?php echo min($offset + $itemsPorPagina, $totalItems); ?>
                        de <?php echo $totalItems; ?> resultados
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($paginaActual > 1): ?>
                            <a href="<?php echo url('ficha', 'show', isset($_GET['coor_id']) ? ['page' => $paginaActual - 1, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $paginaActual - 1, 'search' => $busqueda]); ?>"
                                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Anterior
                            </a>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $paginaActual - 2);
                        $fin = min($totalPaginas, $paginaActual + 2);

                        for ($i = $inicio; $i <= $fin; $i++):
                            ?>
                            <a href="<?php echo url('ficha', 'show', isset($_GET['coor_id']) ? ['page' => $i, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $i, 'search' => $busqueda]); ?>"
                                class="px-3 py-2 text-sm border rounded-lg transition <?php echo $i == $paginaActual ? 'bg-primary text-white border-primary' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a href="<?php echo url('ficha', 'show', isset($_GET['coor_id']) ? ['page' => $paginaActual + 1, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $paginaActual + 1, 'search' => $busqueda]); ?>"
                                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Siguiente
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>