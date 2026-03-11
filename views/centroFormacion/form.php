<?php 

// Determinar si es edición o creación
$isEdit = isset($centro) && $centro !== null;
$pageTitle = $isEdit ? "Editar Centro de Formación" : "Nuevo Centro de Formación";
$action = $isEdit ? "index.php?c=centroFormacion&a=update" : "index.php?c=centroFormacion&a=save";
$buttonText = $isEdit ? "Actualizar Centro" : "Guardar Centro";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-university'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="cent_id" value="<?php echo htmlspecialchars($centro->getId()); ?>">
            <?php endif; ?>
            
            <div>
                <label for="cent_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-primary mr-2"></i>Nombre del Centro
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="cent_nombre" 
                       name="cent_nombre" 
                       value="<?php echo $isEdit ? htmlspecialchars($centro->getNombre()) : ''; ?>"
                       required 
                       minlength="3"
                       maxlength="200"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Ej: Centro de Biotecnología Agropecuaria">
                <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 200</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="index.php?c=centroFormacion&a=show&role=<?php echo $userRole; ?>" 
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
