<?php 

// Determinar si es edición o creación
$isEdit = isset($instructor) && $instructor !== null;
$pageTitle = $isEdit ? "Editar Instructor" : "Nuevo Instructor";
$action = $isEdit ? url('instructor', 'update') : url('instructor', 'save');
$buttonText = $isEdit ? "Actualizar Instructor" : "Guardar Instructor";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Verificar si el usuario es un centro de formación
$esCentro = hasRole('centro');

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-chalkboard-teacher'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?>" method="POST" class="space-y-6">
            <?php if ($isEdit): ?>
                <input type="hidden" name="inst_id" value="<?php echo htmlspecialchars($instructor->getId()); ?>">
            <?php endif; ?>
            
            <!-- Primera fila: Nombres y Apellidos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="inst_nombres" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-primary mr-2"></i>Nombres
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="inst_nombres" 
                           name="inst_nombres" 
                           value="<?php echo $isEdit ? htmlspecialchars($instructor->getNombres()) : ''; ?>"
                           required 
                           minlength="2"
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: Juan Carlos">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 2 caracteres, máximo 45</p>
                </div>

                <div>
                    <label for="inst_apellidos" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-primary mr-2"></i>Apellidos
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="inst_apellidos" 
                           name="inst_apellidos" 
                           value="<?php echo $isEdit ? htmlspecialchars($instructor->getApellidos()) : ''; ?>"
                           required 
                           minlength="2"
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: Pérez García">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 2 caracteres, máximo 45</p>
                </div>
            </div>

            <!-- Segunda fila: Correo y Teléfono -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="inst_correo" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-primary mr-2"></i>Correo Electrónico
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="inst_correo" 
                           name="inst_correo" 
                           value="<?php echo $isEdit ? htmlspecialchars($instructor->getCorreo()) : ''; ?>"
                           required 
                           maxlength="45"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: juan.perez@sena.edu.co">
                    <p class="mt-1 text-sm text-gray-500">Máximo 45 caracteres</p>
                </div>

                <div>
                    <label for="inst_telefono" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone text-primary mr-2"></i>Teléfono
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           id="inst_telefono" 
                           name="inst_telefono" 
                           value="<?php echo $isEdit ? htmlspecialchars($instructor->getTelefono()) : ''; ?>"
                           required 
                           pattern="[0-9]{10}"
                           maxlength="10"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ej: 3001234567">
                    <p class="mt-1 text-sm text-gray-500">Ingrese 10 dígitos sin espacios</p>
                </div>
            </div>

            <!-- Tercera fila: Contraseña o mensaje informativo -->
            <?php if (!$isEdit): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="inst_password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-primary mr-2"></i>Contraseña
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="inst_password" 
                               name="inst_password" 
                               required 
                               minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Mínimo 6 caracteres">
                        <p class="mt-1 text-sm text-gray-500">Mínimo 6 caracteres</p>
                    </div>

                    <?php if ($esCentro): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-university text-primary mr-2"></i>Centro de Formación
                        </label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars(currentUserName()); ?>"
                               readonly
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                        <input type="hidden" name="cent_id" value="<?php echo currentUserId(); ?>">
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        La contraseña no se puede modificar desde aquí. Para cambiarla, use la opción de perfil.
                    </p>
                </div>

                <?php if ($esCentro): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-university text-blue-500 mr-2"></i>
                        <strong>Centro de Formación:</strong> <?php echo htmlspecialchars(currentUserName()); ?>
                    </p>
                    <input type="hidden" name="cent_id" value="<?php echo currentUserId(); ?>">
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('instructor', 'show'); ?>" 
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
