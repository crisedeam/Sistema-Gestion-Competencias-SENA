<?php 

// Determinar si es edición o creación
$isEdit = isset($ambiente) && $ambiente !== null;
$pageTitle = $isEdit ? "Editar Ambiente" : "Nuevo Ambiente";
$action = $isEdit ? url('ambiente', 'update') : url('ambiente', 'save');
$buttonText = $isEdit ? "Actualizar Ambiente" : "Guardar Ambiente";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Cargar sedes si no están disponibles
if (!isset($sedes)) {
    require_once __DIR__ . '/../../models/Sede.php';
    $sedes = Sede::all();
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-door-open'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <div>
                <label for="amb_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-hashtag text-primary mr-2"></i>ID del Ambiente
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="amb_id" 
                       name="amb_id" 
                       value="<?php echo $isEdit ? htmlspecialchars($ambiente->getId()) : ''; ?>"
                       <?php echo $isEdit ? 'readonly' : 'required'; ?>
                       maxlength="10"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent <?php echo $isEdit ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                       placeholder="Ej: LAB301">
                <?php if (!$isEdit): ?>
                    <p class="mt-1 text-sm text-gray-500">Máximo 10 caracteres</p>
                <?php endif; ?>
            </div>

            <div>
                <label for="amb_nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-primary mr-2"></i>Nombre del Ambiente
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="amb_nombre" 
                       name="amb_nombre" 
                       value="<?php echo $isEdit ? htmlspecialchars($ambiente->getNombre()) : ''; ?>"
                       required 
                       minlength="3"
                       maxlength="100"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="Ej: Laboratorio 301">
                <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 100</p>
            </div>

            <div>
                <label for="sede_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building text-primary mr-2"></i>Sede
                    <span class="text-red-500">*</span>
                </label>
                <select id="sede_id" 
                        name="sede_id" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Seleccione una sede</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?php echo $sede->getId(); ?>"
                                <?php echo ($isEdit && $sede->getId() == $ambiente->getSedeId()) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sede->getNombre()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="<?php echo url('ambiente', 'show'); ?>" 
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
