<?php 
$pageTitle = "Mis Asignaciones";
$userRole = $_GET['role'] ?? 'instructor';
$instructorId = $_GET['inst_id'] ?? 1;

// Incluir dependencias necesarias
require_once __DIR__ . '/../../Database.php';
require_once __DIR__ . '/../../models/Competencia.php';
require_once __DIR__ . '/../../models/Ficha.php';
require_once __DIR__ . '/../../models/Ambiente.php';

ob_start();

// Calcular estadísticas
$totalAsignaciones = isset($asignaciones) ? count($asignaciones) : 0;
$horasSemanales = 0;
$fichasUnicas = [];
$asignacionesActivas = 0;

if (isset($asignaciones)) {
    foreach ($asignaciones as $asignacion) {
        $fechaActual = date('Y-m-d');
        $fechaInicio = $asignacion->getFechaInicio();
        $fechaFin = $asignacion->getFechaFin();
        
        if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
            $asignacionesActivas++;
        }
        
        $competencia = Competencia::searchById($asignacion->getCompetenciaId());
        if ($competencia) {
            $horasSemanales += ($competencia->getHoras() / 4);
        }
        
        if (!in_array($asignacion->getFichaId(), $fichasUnicas)) {
            $fichasUnicas[] = $asignacion->getFichaId();
        }
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-tasks text-primary mr-2"></i>
            Mis Asignaciones
        </h2>
        <p class="text-gray-600 mt-2">Gestiona y visualiza tus asignaciones actuales</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Asignaciones Activas</p>
                    <p class="text-4xl font-bold text-primary"><?php echo $asignacionesActivas; ?></p>
                    <p class="text-xs text-gray-500 mt-1">De <?php echo $totalAsignaciones; ?> totales</p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-tasks text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Horas Semanales</p>
                    <p class="text-4xl font-bold text-primary"><?php echo number_format($horasSemanales, 0); ?></p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Fichas Asignadas</p>
                    <p class="text-4xl font-bold text-primary"><?php echo count($fichasUnicas); ?></p>
                </div>
                <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-id-card text-2xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Asignaciones -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-primary text-white">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-list mr-2"></i>Listado de Asignaciones
            </h3>
        </div>

        <?php if (isset($asignaciones) && count($asignaciones) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Competencia
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Ficha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Ambiente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Jornada
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Fecha Inicio
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Fecha Fin
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Horas
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($asignaciones as $asignacion): ?>
                    <?php
                        $ficha = Ficha::searchById($asignacion->getFichaId());
                        $ambiente = Ambiente::searchById($asignacion->getAmbienteId());
                        $competencia = Competencia::searchById($asignacion->getCompetenciaId());
                        
                        $fechaActual = date('Y-m-d');
                        $fechaInicio = $asignacion->getFechaInicio();
                        $fechaFin = $asignacion->getFechaFin();
                        
                        if ($fechaActual < $fechaInicio) {
                            $estado = 'Programada';
                            $claseEstado = 'bg-blue-100 text-blue-800';
                            $iconoEstado = 'fa-clock';
                        } elseif ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
                            $estado = 'En Curso';
                            $claseEstado = 'bg-green-100 text-green-800';
                            $iconoEstado = 'fa-play-circle';
                        } else {
                            $estado = 'Finalizada';
                            $claseEstado = 'bg-gray-100 text-gray-800';
                            $iconoEstado = 'fa-check-circle';
                        }
                    ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo $competencia ? htmlspecialchars($competencia->getNombreCorto()) : 'N/A'; ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo $competencia ? htmlspecialchars($competencia->getNombreUnidad()) : ''; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                <?php echo $ficha ? htmlspecialchars($ficha->getId()) : 'N/A'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <i class="fas fa-door-open text-primary mr-1"></i>
                            <?php echo $ambiente ? htmlspecialchars($ambiente->getNombre()) : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <i class="fas fa-sun text-yellow-500 mr-1"></i>
                            <?php echo $ficha ? htmlspecialchars($ficha->getJornada()) : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <i class="fas fa-calendar text-primary mr-1"></i>
                            <?php echo date('d/m/Y', strtotime($asignacion->getFechaInicio())); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <i class="fas fa-calendar-check text-primary mr-1"></i>
                            <?php echo date('d/m/Y', strtotime($asignacion->getFechaFin())); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <i class="fas fa-clock text-primary mr-1"></i>
                            <?php echo $competencia ? $competencia->getHoras() . 'h' : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 <?php echo $claseEstado; ?> text-xs font-semibold rounded-full">
                                <i class="fas <?php echo $iconoEstado; ?> mr-1"></i>
                                <?php echo $estado; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="<?php echo url('asignacion', 'detalles', ['id' => $asignacion->getId(), 'inst_id' => $instructorId, 'role' => $userRole]); ?>" 
                               class="text-primary hover:text-primary-dark transition" title="Ver horarios">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No tienes asignaciones registradas</p>
            <p class="text-gray-400 text-sm mt-2">Las asignaciones aparecerán aquí cuando el coordinador las cree</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
