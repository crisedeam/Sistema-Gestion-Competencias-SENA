<?php
$pageTitle = 'Historial de Auditoría';

ob_start();
?>

<div class="space-y-6">
    <!-- Header con botón de volver -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Historial de Auditoría
                </h2>
                <p class="text-gray-600 mt-2">Asignación #<?= htmlspecialchars($asignacion->getId()) ?></p>
            </div>
            <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Información de la asignación actual -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-primary text-white px-6 py-4">
            <h5 class="text-lg font-semibold mb-0">Información Actual de la Asignación</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Instructor:</strong> 
                        <?php 
                        $instructor = Instructor::find($asignacion->getInstructorId());
                        echo $instructor ? htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()) : 'N/A';
                        ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Ficha:</strong> 
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                            <?= htmlspecialchars($asignacion->getFichaId()) ?>
                        </span>
                    </p>
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Ambiente:</strong> 
                        <?php 
                        $ambiente = Ambiente::find($asignacion->getAmbienteId());
                        echo $ambiente ? htmlspecialchars($ambiente->getNombre() . ' (' . $ambiente->getId() . ')') : 'N/A';
                        ?>
                    </p>
                </div>
                <div class="space-y-3">
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Competencia:</strong> 
                        <?php 
                        $competencia = Competencia::find($asignacion->getCompetenciaId());
                        echo $competencia ? htmlspecialchars($competencia->getNombreCorto()) : 'N/A';
                        ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Fecha Inicio:</strong> 
                        <?= date('d/m/Y H:i', strtotime($asignacion->getFechaInicio())) ?>
                    </p>
                    <p class="text-gray-700">
                        <strong class="text-gray-900">Fecha Fin:</strong> 
                        <?= date('d/m/Y H:i', strtotime($asignacion->getFechaFin())) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de cambios -->
    <?php if (empty($historial)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-4 rounded-lg">
            <i class="fas fa-info-circle mr-2"></i> No hay registros de auditoría para esta asignación.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800 mb-0">
                    Historial de Cambios (<?= count($historial) ?> registros)
                </h5>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 15%;">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 10%;">Acción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 20%;">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 55%;">Detalles del Cambio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($historial as $registro): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= date('d/m/Y', strtotime($registro['audit_fecha_hora'])) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i:s', strtotime($registro['audit_fecha_hora'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $badgeClass = 'gray';
                                $icon = 'fa-question';
                                switch ($registro['audit_accion']) {
                                    case 'INSERT':
                                        $badgeClass = 'green';
                                        $icon = 'fa-plus-circle';
                                        break;
                                    case 'UPDATE':
                                        $badgeClass = 'yellow';
                                        $icon = 'fa-edit';
                                        break;
                                    case 'DELETE':
                                        $badgeClass = 'red';
                                        $icon = 'fa-trash';
                                        break;
                                }
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?= $badgeClass ?>-100 text-<?= $badgeClass ?>-800">
                                    <i class="fas <?= $icon ?> mr-1"></i> <?= $registro['audit_accion'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($registro['audit_usuario_correo']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($registro['audit_accion'] == 'INSERT'): ?>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <div class="font-semibold text-green-800 mb-2">
                                            <i class="fas fa-plus-circle mr-1"></i> Registro creado
                                        </div>
                                        <?php if ($registro['datos_nuevos']): ?>
                                            <div class="text-sm text-gray-700 space-y-1">
                                                <?php
                                                $datos = $registro['datos_nuevos'];
                                                $instructor = Instructor::find($datos['INSTRUCTOR_inst_id']);
                                                $competencia = Competencia::find($datos['COMPETENCIA_comp_id']);
                                                ?>
                                                <div>• <strong>Instructor:</strong> <?= $instructor ? htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()) : 'ID ' . $datos['INSTRUCTOR_inst_id'] ?></div>
                                                <div>• <strong>Ficha:</strong> <?= htmlspecialchars($datos['FICHA_fich_id']) ?></div>
                                                <div>• <strong>Ambiente:</strong> <?= htmlspecialchars($datos['AMBIENTE_amb_id']) ?></div>
                                                <div>• <strong>Competencia:</strong> <?= $competencia ? htmlspecialchars($competencia->getNombreCorto()) : 'ID ' . $datos['COMPETENCIA_comp_id'] ?></div>
                                                <div>• <strong>Fecha inicio:</strong> <?= date('d/m/Y H:i', strtotime($datos['asig_fecha_ini'])) ?></div>
                                                <div>• <strong>Fecha fin:</strong> <?= date('d/m/Y H:i', strtotime($datos['asig_fecha_fin'])) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                
                                <?php elseif ($registro['audit_accion'] == 'DELETE'): ?>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <div class="font-semibold text-red-800 mb-2">
                                            <i class="fas fa-trash mr-1"></i> Registro eliminado
                                        </div>
                                        <?php if ($registro['datos_anteriores']): ?>
                                            <div class="text-sm text-gray-700 space-y-1">
                                                <?php
                                                $datos = $registro['datos_anteriores'];
                                                $instructor = Instructor::find($datos['INSTRUCTOR_inst_id']);
                                                $competencia = Competencia::find($datos['COMPETENCIA_comp_id']);
                                                ?>
                                                <div>• <strong>Instructor:</strong> <?= $instructor ? htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()) : 'ID ' . $datos['INSTRUCTOR_inst_id'] ?></div>
                                                <div>• <strong>Ficha:</strong> <?= htmlspecialchars($datos['FICHA_fich_id']) ?></div>
                                                <div>• <strong>Ambiente:</strong> <?= htmlspecialchars($datos['AMBIENTE_amb_id']) ?></div>
                                                <div>• <strong>Competencia:</strong> <?= $competencia ? htmlspecialchars($competencia->getNombreCorto()) : 'ID ' . $datos['COMPETENCIA_comp_id'] ?></div>
                                                <div>• <strong>Fecha inicio:</strong> <?= date('d/m/Y H:i', strtotime($datos['asig_fecha_ini'])) ?></div>
                                                <div>• <strong>Fecha fin:</strong> <?= date('d/m/Y H:i', strtotime($datos['asig_fecha_fin'])) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                
                                <?php elseif ($registro['audit_accion'] == 'UPDATE'): ?>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="font-semibold text-yellow-800 mb-2">
                                            <i class="fas fa-edit mr-1"></i> Campos modificados:
                                        </div>
                                        <div class="text-sm">
                                            <?php
                                            $anterior = $registro['datos_anteriores'];
                                            $nuevos = $registro['datos_nuevos'];
                                            $cambios = [];
                                            
                                            foreach ($nuevos as $campo => $valorNuevo) {
                                                $valorAnterior = $anterior[$campo] ?? 'N/A';
                                                if ($valorAnterior != $valorNuevo) {
                                                    $cambios[$campo] = [
                                                        'anterior' => $valorAnterior,
                                                        'nuevo' => $valorNuevo
                                                    ];
                                                }
                                            }
                                            
                                            if (empty($cambios)): ?>
                                                <div class="text-gray-500">No se detectaron cambios</div>
                                            <?php else: ?>
                                                <div class="space-y-2">
                                                    <?php foreach ($cambios as $campo => $valores): ?>
                                                        <div class="border-l-2 border-yellow-400 pl-3">
                                                            <div class="font-semibold text-gray-900"><?= htmlspecialchars($campo) ?>:</div>
                                                            <div class="text-red-600">
                                                                <i class="fas fa-minus-circle mr-1"></i>
                                                                <?php
                                                                if ($campo == 'INSTRUCTOR_inst_id') {
                                                                    $inst = Instructor::find($valores['anterior']);
                                                                    echo $inst ? htmlspecialchars($inst->getNombres() . ' ' . $inst->getApellidos()) : 'ID ' . $valores['anterior'];
                                                                } elseif ($campo == 'COMPETENCIA_comp_id') {
                                                                    $comp = Competencia::find($valores['anterior']);
                                                                    echo $comp ? htmlspecialchars($comp->getNombreCorto()) : 'ID ' . $valores['anterior'];
                                                                } elseif (strpos($campo, 'fecha') !== false) {
                                                                    echo date('d/m/Y H:i', strtotime($valores['anterior']));
                                                                } else {
                                                                    echo htmlspecialchars($valores['anterior']);
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="text-green-600">
                                                                <i class="fas fa-plus-circle mr-1"></i>
                                                                <?php
                                                                if ($campo == 'INSTRUCTOR_inst_id') {
                                                                    $inst = Instructor::find($valores['nuevo']);
                                                                    echo $inst ? htmlspecialchars($inst->getNombres() . ' ' . $inst->getApellidos()) : 'ID ' . $valores['nuevo'];
                                                                } elseif ($campo == 'COMPETENCIA_comp_id') {
                                                                    $comp = Competencia::find($valores['nuevo']);
                                                                    echo $comp ? htmlspecialchars($comp->getNombreCorto()) : 'ID ' . $valores['nuevo'];
                                                                } elseif (strpos($campo, 'fecha') !== false) {
                                                                    echo date('d/m/Y H:i', strtotime($valores['nuevo']));
                                                                } else {
                                                                    echo htmlspecialchars($valores['nuevo']);
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
