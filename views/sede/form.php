<?php 

// Determinar si es edición o creación
$isEdit = isset($sede) && $sede !== null;
$pageTitle = $isEdit ? "Editar Sede" : "Nueva Sede";
$action = $isEdit ? url('sede', 'update') : url('sede', 'save');
$buttonText = $isEdit ? "Actualizar Sede" : "Guardar Sede";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-building'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6" id="sedeForm">
            <?php if ($isEdit): ?>
                <input type="hidden" name="sede_id" value="<?php echo htmlspecialchars($sede->getId()); ?>">
            <?php endif; ?>
            
            <div>
                <label for="sede_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-primary mr-2"></i>Nombre de la Sede
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="sede_nombre" 
                       name="sede_nombre" 
                       value="<?php echo $isEdit ? htmlspecialchars($sede->getNombre()) : ''; ?>"
                       required 
                       minlength="3"
                       maxlength="100"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Ej: Sede Centro">
                <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 100</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="<?php echo url('sede', 'show'); ?>" 
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
