<?php 

// Determinar si es edición o creación
$isEdit = isset($coordinacion) && $coordinacion !== null;
$pageTitle = $isEdit ? "Editar Coordinación" : "Nueva Coordinación";
$action = $isEdit ? url('coordinacion', 'update') : url('coordinacion', 'save');
$buttonText = $isEdit ? "Actualizar Coordinación" : "Guardar Coordinación";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Verificar si el usuario es un centro de formación
$esCentro = hasRole('centro');

// Cargar centros si no están disponibles
if (!isset($centros)) {
    require_once __DIR__ . '/../../models/CentroFormacion.php';
    if ($esCentro) {
        $centro = CentroFormacion::find(currentUserId());
        $centros = [$centro];
    } else {
        $centros = CentroFormacion::all();
    }
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-user-tie'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="coord_id" value="<?php echo htmlspecialchars($coordinacion->getId()); ?>">
            <?php endif; ?>
            
            <!-- Primera fila: Descripción y Centro -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="coord_descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-primary mr-2"></i>Descripción
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="coord_descripcion" 
                           name="coord_descripcion" 
                           value="<?php echo $isEdit ? htmlspecialchars($coordinacion->getDescripcion()) : ''; ?>"
                           required 
                           minlength="3"
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: Coordinación Académica">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 45</p>
                </div>

                <div>
                    <label for="CENTRO_FORMACION_cent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-university text-primary mr-2"></i>Centro de Formación
                        <span class="text-red-500">*</span>
                    </label>
                    <?php if ($esCentro && count($centros) == 1): ?>
                        <!-- Si es centro, mostrar solo lectura -->
                        <input type="text" 
                               value="<?php echo htmlspecialchars($centros[0]->getNombre()); ?>"
                               readonly
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                        <input type="hidden" name="CENTRO_FORMACION_cent_id" value="<?php echo $centros[0]->getId(); ?>">
                    <?php else: ?>
                        <!-- Si no es centro, mostrar select -->
                        <select id="CENTRO_FORMACION_cent_id" 
                                name="CENTRO_FORMACION_cent_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Seleccione un centro</option>
                            <?php foreach ($centros as $centro): ?>
                                <option value="<?php echo $centro->getId(); ?>"
                                        <?php echo ($isEdit && $centro->getId() == $coordinacion->getCentroFormacionId()) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($centro->getNombre()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Segunda fila: Nombre y Correo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="coord_nombre_coordinador" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-primary mr-2"></i>Nombre del Coordinador
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="coord_nombre_coordinador" 
                           name="coord_nombre_coordinador" 
                           value="<?php echo $isEdit ? htmlspecialchars($coordinacion->getNombreCoordinador()) : ''; ?>"
                           required 
                           minlength="3"
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Nombre completo del coordinador">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres, máximo 45</p>
                </div>

                <div>
                    <label for="coord_correo" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-primary mr-2"></i>Correo Electrónico
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="coord_correo" 
                           name="coord_correo" 
                           value="<?php echo $isEdit ? htmlspecialchars($coordinacion->getCorreo()) : ''; ?>"
                           required 
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="correo@sena.edu.co">
                    <p class="mt-1 text-sm text-gray-500">Máximo 45 caracteres</p>
                </div>
            </div>

            <!-- Tercera fila: Contraseña o mensaje informativo -->
            <?php if (!$isEdit): ?>
                <div>
                    <label for="coord_password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-primary mr-2"></i>Contraseña
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="coord_password" 
                           name="coord_password" 
                           required 
                           minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Mínimo 6 caracteres">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 6 caracteres</p>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        La contraseña no se puede modificar desde aquí. Para cambiarla, use la opción de perfil.
                    </p>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('coordinacion', 'show'); ?>" 
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
