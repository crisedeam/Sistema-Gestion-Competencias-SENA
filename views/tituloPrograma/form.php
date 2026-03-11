<?php 

// Determinar si es edición o creación
$isEdit = isset($titulo) && $titulo !== null;
$pageTitle = $isEdit ? "Editar Título de Programa" : "Nuevo Título de Programa";
$action = $isEdit ? "index.php?c=tituloPrograma&a=update" : "index.php?c=tituloPrograma&a=save";
$buttonText = $isEdit ? "Actualizar Título" : "Guardar Título";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-graduation-cap'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="titpro_id" value="<?php echo htmlspecialchars($titulo->getId()); ?>">
            <?php endif; ?>
            
            <div>
                <label for="titpro_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-primary mr-2"></i>Nombre del Título
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="titpro_nombre" 
                       name="titpro_nombre" 
                       value="<?php echo $isEdit ? htmlspecialchars($titulo->getNombre()) : ''; ?>"
                       required 
                       minlength="3"
                       maxlength="45"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Ej: Técnico, Tecnólogo, Especialización">
                <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 45</p>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="index.php?c=tituloPrograma&a=show&role=<?php echo $userRole; ?>" 
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
