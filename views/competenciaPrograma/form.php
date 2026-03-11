<?php 

// Determinar si es edición o creación
$isEdit = isset($relacion) && $relacion !== null;
$pageTitle = $isEdit ? "Editar Relación Competencia-Programa" : "Nueva Relación Competencia-Programa";
$action = $isEdit ? url('competenciaPrograma', 'update') : url('competenciaPrograma', 'save');
$buttonText = $isEdit ? "Actualizar Relación" : "Guardar Relación";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Cargar programas y competencias si no están disponibles
if (!isset($programas)) {
    require_once __DIR__ . '/../../models/Programa.php';
    $programas = Programa::all();
}
if (!isset($competencias)) {
    require_once __DIR__ . '/../../models/Competencia.php';
    $competencias = Competencia::all();
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-link'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="compprog_id" value="<?php echo htmlspecialchars($relacion->getId()); ?>">
            <?php endif; ?>
            
            <!-- Fila única: Programa y Competencia (dos columnas) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="PROGRAMA_prog_codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap text-primary mr-2"></i>Programa
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="PROGRAMA_prog_codigo" 
                            name="PROGRAMA_prog_codigo" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un programa</option>
                        <?php foreach ($programas as $programa): ?>
                            <option value="<?php echo htmlspecialchars($programa->getCodigo()); ?>"
                                    <?php echo ($isEdit && $programa->getCodigo() == $relacion->getProgramaCodigo()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($programa->getCodigo() . ' - ' . $programa->getDenominacion()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="COMPETENCIA_comp_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book text-primary mr-2"></i>Competencia
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="COMPETENCIA_comp_id" 
                            name="COMPETENCIA_comp_id" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione una competencia</option>
                        <?php foreach ($competencias as $competencia): ?>
                            <option value="<?php echo htmlspecialchars($competencia->getId()); ?>"
                                    <?php echo ($isEdit && $competencia->getId() == $relacion->getCompetenciaId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($competencia->getNombreCorto() . ' - ' . substr($competencia->getNombreUnidad(), 0, 50) . '...'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (!$isEdit): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Esta relación asocia una competencia específica a un programa de formación. Asegúrese de que la competencia sea relevante para el programa seleccionado.
                    </p>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('competenciaPrograma', 'show'); ?>" 
                   class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                    <i class="fas <?php echo $buttonIcon; ?> mr-2"></i><?php echo $buttonText; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/layout.php';
?>
