<?php
/**
 * Formulario unificado para crear y editar asignaciones
 * 
 * Variables esperadas:
 * - $esEdicion: boolean - true si es edición, false si es creación
 * - $asignacion: objeto Asignacion (solo en edición)
 * - $ficha, $competencia, $instructor: objetos (solo en edición)
 * - $fichas, $ambientes, $instructores, $competencias: arrays
 * - $userRole: string
 * - $datosFormulario: array (datos del formulario en caso de error)
 */

$esEdicion = $esEdicion ?? false;
$baseParams = isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : [];
$actionUrl = $esEdicion
    ? url('asignacion', 'update', $baseParams)
    : url('asignacion', 'save', $baseParams);
?>

<form action="<?php echo $actionUrl; ?>" method="POST" id="formAsignacion">

    <?php if ($esEdicion): ?>
        <input type="hidden" name="asig_id" value="<?php echo htmlspecialchars($asignacion->getId()); ?>">
        <input type="hidden" name="fich_id" value="<?php echo htmlspecialchars($asignacion->getFichaId()); ?>">
        <input type="hidden" name="comp_id" value="<?php echo htmlspecialchars($asignacion->getCompetenciaId()); ?>">
        <input type="hidden" name="inst_id" value="<?php echo htmlspecialchars($asignacion->getInstructorId()); ?>">
    <?php endif; ?>

    <!-- Formulario compacto arriba -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información Básica</h3>

        <?php if ($esEdicion): ?>
            <!-- MODO EDICIÓN: Fila 1 con 3 campos solo lectura -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Ficha (solo lectura) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ficha</label>
                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        <?php echo htmlspecialchars($ficha ? $ficha->getId() . ' - ' . $ficha->getJornada() : $asignacion->getFichaId()); ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">No se puede modificar</p>
                </div>

                <!-- Competencia (solo lectura) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Competencia</label>
                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        <?php echo htmlspecialchars($competencia ? $competencia->getNombreCorto() : 'Competencia'); ?>
                    </div>
                    <div class="mt-1 text-xs text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="horasCompetencia">Requiere <?php echo $competencia ? $competencia->getHoras() : 0; ?>
                            horas</span>
                    </div>
                </div>

                <!-- Instructor (solo lectura) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructor</label>
                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        <?php echo htmlspecialchars($instructor ? $instructor->getNombres() . ' ' . $instructor->getApellidos() : 'Instructor'); ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">No se puede modificar</p>
                </div>
            </div>

            <!-- Fila 2: Sede, Ambiente y Fecha Inicio (editables) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Sede (editable) -->
                <div>
                    <label for="sede_id_edit" class="block text-sm font-medium text-gray-700 mb-2">
                        Sede <span class="text-red-500">*</span>
                    </label>
                    <select id="sede_id_edit" name="sede_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione una sede</option>
                        <?php
                        require_once __DIR__ . '/../../models/Sede.php';
                        $sedes = Sede::all();
                        // Obtener la sede del ambiente actual
                        $ambiente_actual = null;
                        if (isset($ambientes)) {
                            foreach ($ambientes as $amb) {
                                if ($amb->getId() == $asignacion->getAmbienteId()) {
                                    $ambiente_actual = $amb;
                                    break;
                                }
                            }
                        }
                        $sede_actual_id = $ambiente_actual ? $ambiente_actual->getSedeId() : null;
                        
                        if (count($sedes) > 0):
                            foreach ($sedes as $sede):
                                $selected = ($sede->getId() == $sede_actual_id) ? 'selected' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($sede->getId()); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($sede->getNombre()); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Ambiente (editable) -->
                <div>
                    <label for="amb_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Ambiente <span class="text-red-500">*</span>
                    </label>
                    <select id="amb_id" name="amb_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un ambiente</option>
                        <?php
                        if (isset($ambientes) && count($ambientes) > 0):
                            foreach ($ambientes as $ambiente):
                                $selected = ($ambiente->getId() == $asignacion->getAmbienteId()) ? 'selected' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($ambiente->getId()); ?>" 
                                        data-sede="<?php echo htmlspecialchars($ambiente->getSedeId()); ?>"
                                        <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($ambiente->getNombre()); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Fecha Inicio (editable) -->
                <div>
                    <label for="asig_fecha_ini" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="asig_fecha_ini" name="asig_fecha_ini" required
                        value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($asignacion->getFechaInicio()))); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <div class="mt-1 text-xs text-gray-500">
                        La fecha fin se calcula automáticamente
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- MODO CREACIÓN: Fila 1 con Sede, Ambiente, Ficha -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Sede -->
                <div>
                    <label for="sede_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sede <span class="text-red-500">*</span>
                    </label>
                    <select id="sede_id" name="sede_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione una sede</option>
                        <?php
                        require_once __DIR__ . '/../../models/Sede.php';
                        $sedes = Sede::all();
                        if (count($sedes) > 0):
                            foreach ($sedes as $sede):
                                ?>
                                <option value="<?php echo htmlspecialchars($sede->getId()); ?>">
                                    <?php echo htmlspecialchars($sede->getNombre()); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>
                </div>

                <!-- Ambiente -->
                <div>
                    <label for="amb_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Ambiente <span class="text-red-500">*</span>
                    </label>
                    <select id="amb_id" name="amb_id" required disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <option value="">Seleccione primero una sede</option>
                    </select>
                </div>

                <!-- Ficha -->
                <div>
                    <label for="fich_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Ficha <span class="text-red-500">*</span>
                    </label>
                    <select id="fich_id" name="fich_id" required disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <option value="">Seleccione primero un ambiente</option>
                        <?php
                        if (isset($fichas) && count($fichas) > 0):
                            foreach ($fichas as $ficha):
                                ?>
                                <option value="<?php echo htmlspecialchars($ficha->getId()); ?>"
                                    data-jornada="<?php echo htmlspecialchars($ficha->getJornada()); ?>">
                                    <?php echo htmlspecialchars($ficha->getId() . ' - ' . $ficha->getJornada()); ?>
                                </option>
                            <?php endforeach; endif; ?>
                    </select>
                    <div id="infoJornada" class="mt-1 text-xs text-blue-600 hidden">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="jornadaTexto"></span>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Competencia, Instructor, Fecha Inicio -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Competencia -->
                <div>
                    <label for="comp_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Competencia <span class="text-red-500">*</span>
                    </label>
                    <select id="comp_id" name="comp_id" required disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <option value="">Seleccione primero una ficha</option>
                    </select>
                    <div id="infoCompetencia" class="mt-1 text-xs text-blue-600 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="horasCompetencia"></span>
                    </div>
                    <div id="mensajeCompetencias" class="mt-1 text-xs text-gray-500 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="textoMensaje"></span>
                    </div>
                </div>

                <!-- Instructor -->
                <div>
                    <label for="inst_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Instructor <span class="text-red-500">*</span>
                    </label>
                    <select id="inst_id" name="inst_id" required disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <option value="">Seleccione primero una competencia</option>
                    </select>
                    <div id="mensajeInstructores" class="mt-1 text-xs text-gray-500 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="textoMensajeInst"></span>
                    </div>
                </div>

                <!-- Fecha Inicio -->
                <div>
                    <label for="asig_fecha_ini" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Inicio <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="asig_fecha_ini" name="asig_fecha_ini" required disabled
                        value="<?php echo htmlspecialchars($datosFormulario['asig_fecha_ini'] ?? ''); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                    <div class="mt-1 text-xs text-gray-500">
                        La fecha fin se calcula automáticamente
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Calendario con ancho completo -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <?php echo $esEdicion ? 'Reprogramar Clases' : 'Programar Clases'; ?>
            </h3>
        </div>

        <!-- Calendario FullCalendar -->
        <div id="calendario" class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- El calendario se genera con FullCalendar -->
        </div>

        <!-- Leyenda de colores -->
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-xs space-y-2">
                <div class="font-medium text-gray-700 mb-2">Leyenda de colores:</div>
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg shadow-sm"
                            style="background-color: #fef3c7; border-left: 3px solid #f59e0b;"></span>
                        <span class="text-gray-600 font-medium">Instructor</span>
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg shadow-sm"
                            style="background-color: #dbeafe; border-left: 3px solid #3b82f6;"></span>
                        <span class="text-gray-600 font-medium">Ambiente</span>
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg shadow-sm"
                            style="background-color: #d1fae5; border-left: 3px solid #10b981;"></span>
                        <span
                            class="text-gray-600 font-medium"><?php echo $esEdicion ? 'Esta asignación' : 'Nueva asignación'; ?></span>
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg shadow-sm"
                            style="background-color: #e9d5ff; border-left: 3px solid #a855f7;"></span>
                        <span class="text-gray-600 font-medium">Instructor + Ambiente</span>
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-gray-300 text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Cuando un día tiene más de 3 asignaciones, se muestran indicadores compactos (puntos con números).
                    Haz clic en el día para ver los detalles.
                </div>
            </div>
        </div>

        <!-- Resumen de horas dentro del calendario -->
        <div id="resumenHoras" class="mt-4 p-4 bg-blue-50 rounded-lg <?php echo $esEdicion ? '' : 'hidden'; ?>">
            <h4 class="font-medium text-blue-800 mb-2">Progreso de Horas</h4>
            <div class="text-sm text-blue-700">
                <div class="flex justify-between mb-2">
                    <span>Horas programadas:</span>
                    <span id="horasProgreso" class="font-medium">
                        <span id="horasProgramadas">0</span>/<span
                            id="horasRequeridas"><?php echo $esEdicion && isset($competencia) ? $competencia->getHoras() : '0'; ?></span>
                    </span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
                    <div id="barraProgreso" class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        style="width: 0%"></div>
                </div>
                <div id="estadoProgreso" class="mt-2 text-xs"></div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>"
                class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Asignación
            </button>
        </div>
    </div>

    <!-- Campo oculto para los horarios -->
    <input type="hidden" name="horarios" id="horariosData" value="">
</form>


<!-- Modal para agregar horario -->
<div id="modalHorario" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Programar Clase</h3>
            <div id="fechaSeleccionada" class="text-sm text-gray-600 mb-4"></div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hora Inicio</label>
                    <select id="horaInicio" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <?php for ($h = 6; $h <= 22; $h++): ?>
                            <option value="<?php echo sprintf('%02d:00', $h); ?>"><?php echo sprintf('%02d:00', $h); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hora Fin</label>
                    <select id="horaFin" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <?php for ($h = 7; $h <= 23; $h++): ?>
                            <option value="<?php echo sprintf('%02d:00', $h); ?>"><?php echo sprintf('%02d:00', $h); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cerrarModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" onclick="guardarHorario()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del día -->
<div id="modalDetallesDia" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[75vh] overflow-y-auto">
            <div id="detallesDiaContenido"></div>
            <div class="mt-6 flex justify-end">
                <button type="button" onclick="cerrarModalDetalles()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de alerta personalizado -->
<div id="modalAlerta" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0">
                    <i id="iconoAlerta" class="fas fa-exclamation-circle text-3xl text-red-500"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2" id="tituloAlerta">Alerta</h3>
                    <p class="text-sm text-gray-600" id="mensajeAlerta"></p>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="cerrarModalAlerta()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación personalizado -->
<div id="modalConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-question-circle text-3xl text-blue-500"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Confirmación</h3>
                    <p class="text-sm text-gray-600" id="mensajeConfirmacion"></p>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cerrarModalConfirmacion(false)"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" onclick="cerrarModalConfirmacion(true)"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/form_script.php'; ?>