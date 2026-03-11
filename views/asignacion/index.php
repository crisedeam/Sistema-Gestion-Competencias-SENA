<?php
$pageTitle = 'Asignaciones';

// Configuración de paginación
$itemsPorPagina = 10;
$paginaActual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filtrar por búsqueda
$asignacionesFiltradas = isset($listaAsignaciones) ? $listaAsignaciones : [];
if (!empty($busqueda)) {
    $asignacionesFiltradas = array_filter($asignacionesFiltradas, function ($asignacion) use ($busqueda) {
        // Obtener información adicional para búsqueda
        require_once __DIR__ . '/../../models/Instructor.php';
        require_once __DIR__ . '/../../models/Ficha.php';
        require_once __DIR__ . '/../../models/Ambiente.php';
        require_once __DIR__ . '/../../models/Competencia.php';

        $instructor = Instructor::find($asignacion->getInstructorId());
        $ficha = Ficha::find($asignacion->getFichaId());
        $ambiente = Ambiente::find($asignacion->getAmbienteId());
        $competencia = Competencia::find($asignacion->getCompetenciaId());

        return stripos($asignacion->getId(), $busqueda) !== false ||
            ($instructor && (stripos($instructor->getNombres(), $busqueda) !== false ||
                stripos($instructor->getApellidos(), $busqueda) !== false)) ||
            ($ficha && stripos($ficha->getId(), $busqueda) !== false) ||
            ($ambiente && stripos($ambiente->getNombre(), $busqueda) !== false) ||
            ($competencia && stripos($competencia->getNombreCorto(), $busqueda) !== false);
    });
}

// Calcular paginación
$totalItems = count($asignacionesFiltradas);
$totalPaginas = ceil($totalItems / $itemsPorPagina);
$paginaActual = max(1, min($paginaActual, $totalPaginas));
$offset = ($paginaActual - 1) * $itemsPorPagina;
$asignacionesPaginadas = array_slice($asignacionesFiltradas, $offset, $itemsPorPagina);

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
                <i class="fas fa-tasks text-primary mr-2"></i>
                Gestión de Asignaciones
            </h2>
            <a href="<?php echo url('asignacion', 'register', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>"
                class="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Nueva Asignación
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <form method="GET" class="flex items-center space-x-4">
            <input type="hidden" name="controller" value="asignacion">
            <input type="hidden" name="action" value="show">
            <?php if (isset($_GET['coor_id'])): ?>
                <input type="hidden" name="coor_id" value="<?php echo $_GET['coor_id']; ?>">
            <?php endif; ?>
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($busqueda); ?>"
                        placeholder="Buscar por ID, instructor, ficha, ambiente o competencia..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                </div>
            </div>
            <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-lg transition">
                Buscar
            </button>
            <?php if (!empty($busqueda)): ?>
                <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    Limpiar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabla de asignaciones -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Instructor</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Ficha</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Ambiente</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Competencia</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Fecha Fin</th>
                        <th class="px-6 py-3 text-center text-sm font-medium uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    if (!empty($asignacionesPaginadas)):
                        foreach ($asignacionesPaginadas as $asignacion):
                            // Obtener información adicional
                            require_once __DIR__ . '/../../models/Instructor.php';
                            require_once __DIR__ . '/../../models/Ficha.php';
                            require_once __DIR__ . '/../../models/Ambiente.php';
                            require_once __DIR__ . '/../../models/Competencia.php';
                            require_once __DIR__ . '/../../models/DetalleAsignacion.php';

                            $instructor = Instructor::find($asignacion->getInstructorId());
                            $ficha = Ficha::find($asignacion->getFichaId());
                            $ambiente = Ambiente::find($asignacion->getAmbienteId());
                            $competencia = Competencia::find($asignacion->getCompetenciaId());
                            $detalles = DetalleAsignacion::searchByAsignacion($asignacion->getId());
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($asignacion->getId()); ?>
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
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                        <?php echo $ficha ? htmlspecialchars($ficha->getId()) : 'N/A'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo $ambiente ? htmlspecialchars($ambiente->getNombre()) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo $competencia ? htmlspecialchars($competencia->getNombreCorto()) : 'N/A'; ?>
                                    <?php if ($competencia): ?>
                                        <div class="text-xs text-gray-500"><?php echo $competencia->getHoras(); ?> horas</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($asignacion->getFechaInicio()))); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($asignacion->getFechaFin()))); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="<?php echo url('asignacion', 'detalles', isset($_GET['coor_id']) ? ['id' => $asignacion->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $asignacion->getId()]); ?>"
                                        class="text-blue-600 hover:text-blue-900 mx-2" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('asignacion', 'updateshow', isset($_GET['coor_id']) ? ['id' => $asignacion->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $asignacion->getId()]); ?>"
                                        class="text-primary hover:text-primary-dark mx-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url('asignacion', 'historialAuditoria', isset($_GET['coor_id']) ? ['id' => $asignacion->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $asignacion->getId()]); ?>"
                                        class="text-orange-600 hover:text-orange-900 mx-2" title="Ver historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                        onclick="showDeleteModal('<?php echo url('asignacion', 'delete', isset($_GET['coor_id']) ? ['id' => $asignacion->getId(), 'coor_id' => $_GET['coor_id']] : ['id' => $asignacion->getId()]); ?>', '¿Está seguro de eliminar la asignación #<?php echo htmlspecialchars($asignacion->getId()); ?>? Se eliminarán también todos los horarios asociados.')"
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
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                <p class="text-lg">
                                    <?php echo !empty($busqueda) ? 'No se encontraron resultados' : 'No hay asignaciones registradas'; ?>
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
                            <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['page' => $paginaActual - 1, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $paginaActual - 1, 'search' => $busqueda]); ?>"
                                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Anterior
                            </a>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $paginaActual - 2);
                        $fin = min($totalPaginas, $paginaActual + 2);

                        for ($i = $inicio; $i <= $fin; $i++):
                            ?>
                            <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['page' => $i, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $i, 'search' => $busqueda]); ?>"
                                class="px-3 py-2 text-sm border rounded-lg transition <?php echo $i == $paginaActual ? 'bg-primary text-white border-primary' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['page' => $paginaActual + 1, 'search' => $busqueda, 'coor_id' => $_GET['coor_id']] : ['page' => $paginaActual + 1, 'search' => $busqueda]); ?>"
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