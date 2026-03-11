<?php 

// Determinar si es edición o creación
$isEdit = isset($competencia) && $competencia !== null;
$pageTitle = $isEdit ? "Editar Competencia" : "Nueva Competencia";
$action = $isEdit ? url('competencia', 'update') : url('competencia', 'save');
$buttonText = $isEdit ? "Actualizar Competencia" : "Guardar Competencia";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-book'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="comp_id" value="<?php echo htmlspecialchars($competencia->getId()); ?>">
            <?php endif; ?>
            
            <!-- Primera fila: Nombre Corto y Horas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="comp_nombre_corto" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-primary mr-2"></i>Nombre Corto
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="comp_nombre_corto" 
                           name="comp_nombre_corto" 
                           value="<?php echo $isEdit ? htmlspecialchars($competencia->getNombreCorto()) : ''; ?>"
                           required 
                           minlength="3"
                           maxlength="30"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: Programación Web">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 30</p>
                </div>

                <div>
                    <label for="comp_horas" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-primary mr-2"></i>Horas
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="comp_horas" 
                           name="comp_horas" 
                           value="<?php echo $isEdit ? htmlspecialchars($competencia->getHoras()) : ''; ?>"
                           required 
                           min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: 200">
                    <p class="mt-1 text-sm text-gray-500">Número de horas de la competencia</p>
                </div>
            </div>

            <!-- Segunda fila: Nombre de la Unidad (ancho completo) -->
            <div>
                <label for="comp_nombre_unidad" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-alt text-primary mr-2"></i>Nombre de la Unidad de Competencia
                    <span class="text-red-500">*</span>
                </label>
                <textarea id="comp_nombre_unidad" 
                          name="comp_nombre_unidad" 
                          required 
                          minlength="10"
                          maxlength="200"
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          placeholder="Descripción completa de la competencia"><?php echo $isEdit ? htmlspecialchars($competencia->getNombreUnidad()) : ''; ?></textarea>
                <p class="mt-1 text-sm text-gray-500">Mínimo 10 caracteres, máximo 200</p>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('competencia', 'show'); ?>" 
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
