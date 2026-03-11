<?php 

// Determinar si es edición o creación
$isEdit = isset($programa) && $programa !== null;
$pageTitle = $isEdit ? "Editar Programa" : "Nuevo Programa";
$action = $isEdit ? url('programa', 'update') : url('programa', 'save');
$buttonText = $isEdit ? "Actualizar Programa" : "Guardar Programa";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Cargar títulos si no están disponibles
if (!isset($titulos)) {
    require_once __DIR__ . '/../../models/TituloPrograma.php';
    $titulos = TituloPrograma::all();
}

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
            
            <!-- Primera fila: Código y Denominación -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="prog_codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hashtag text-primary mr-2"></i>Código del Programa
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="prog_codigo" 
                           name="prog_codigo" 
                           value="<?php echo $isEdit ? htmlspecialchars($programa->getCodigo()) : ''; ?>"
                           <?php echo $isEdit ? 'readonly' : 'required'; ?>
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent <?php echo $isEdit ? 'bg-gray-100 cursor-not-allowed' : ''; ?>"
                           placeholder="Ej: 228106">
                    <?php if ($isEdit): ?>
                        <p class="mt-1 text-sm text-gray-500">El código no se puede modificar</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="prog_denominacion" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-primary mr-2"></i>Denominación del Programa
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="prog_denominacion" 
                           name="prog_denominacion" 
                           value="<?php echo $isEdit ? htmlspecialchars($programa->getDenominacion()) : ''; ?>"
                           required 
                           minlength="3"
                           maxlength="100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: Análisis y Desarrollo de Software">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 100</p>
                </div>
            </div>

            <!-- Segunda fila: Tipo y Título -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="prog_tipo" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list text-primary mr-2"></i>Tipo de Programa
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="prog_tipo" 
                            name="prog_tipo" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un tipo</option>
                        <option value="Técnico" <?php echo ($isEdit && $programa->getTipo() == 'Técnico') ? 'selected' : ''; ?>>Técnico</option>
                        <option value="Tecnólogo" <?php echo ($isEdit && $programa->getTipo() == 'Tecnólogo') ? 'selected' : ''; ?>>Tecnólogo</option>
                        <option value="Especialización" <?php echo ($isEdit && $programa->getTipo() == 'Especialización') ? 'selected' : ''; ?>>Especialización</option>
                    </select>
                </div>

                <div>
                    <label for="titpro_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-certificate text-primary mr-2"></i>Título que Otorga
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="titpro_id" 
                            name="titpro_id" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un título</option>
                        <?php foreach ($titulos as $titulo): ?>
                            <option value="<?php echo $titulo->getId(); ?>"
                                    <?php echo ($isEdit && $titulo->getId() == $programa->getTituloId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($titulo->getNombre()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('programa', 'show'); ?>" 
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
