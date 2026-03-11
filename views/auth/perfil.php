<?php 
$pageTitle = "Mi Perfil";

ob_start();
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header del Perfil -->
        <div class="bg-gradient-to-r from-primary to-primary-hover px-8 py-12 text-white">
            <div class="flex items-center space-x-6">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-user text-5xl text-primary"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold">
                        <?php 
                        if (isset($persona)) {
                            if ($persona->tipo === 'centro') {
                                echo htmlspecialchars($persona->nombre);
                            } elseif ($persona->tipo === 'coordinador') {
                                echo htmlspecialchars($persona->nombre_coordinador);
                            } else {
                                echo htmlspecialchars($persona->nombres . ' ' . $persona->apellidos);
                            }
                        } else {
                            echo 'Usuario';
                        }
                        ?>
                    </h2>
                    <p class="text-white text-opacity-90 mt-1">
                        <?php 
                        if (isset($persona)) {
                            if ($persona->tipo === 'centro') echo 'Centro de Formación';
                            elseif ($persona->tipo === 'coordinador') echo 'Coordinador Académico';
                            else echo 'Instructor';
                        }
                        ?>
                    </p>
                    <p class="text-sm text-white text-opacity-80 mt-2">
                        <i class="fas fa-envelope mr-2"></i><?php echo isset($persona) ? htmlspecialchars($persona->correo) : ''; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Contenido del Perfil -->
        <div class="p-8">
            <?php if (isset($persona)): ?>
            <form method="POST" action="<?php echo url('perfil', 'actualizar'); ?>" class="space-y-6">
                <?php if ($persona->tipo === 'centro'): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nombre del Centro -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Centro <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="<?php echo htmlspecialchars($persona->nombre); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="correo" 
                                   value="<?php echo htmlspecialchars($persona->correo); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                <?php elseif ($persona->tipo === 'coordinador'): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nombre del Coordinador -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre_coordinador" 
                                   value="<?php echo htmlspecialchars($persona->nombre_coordinador); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="correo" 
                                   value="<?php echo htmlspecialchars($persona->correo); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombres -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombres" 
                                   value="<?php echo htmlspecialchars($persona->nombres); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="apellidos" 
                                   value="<?php echo htmlspecialchars($persona->apellidos); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="correo" 
                                   value="<?php echo htmlspecialchars($persona->correo); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="telefono" 
                                   value="<?php echo htmlspecialchars($persona->telefono ?? ''); ?>" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Información Adicional -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-info-circle text-primary mr-2"></i>Información Adicional
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-id-badge w-6 text-primary"></i>
                            <span class="ml-2">ID: <?php echo htmlspecialchars($persona->id); ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-user-tag w-6 text-primary"></i>
                            <span class="ml-2">Rol: <?php 
                                if ($persona->tipo === 'centro') echo 'Centro de Formación';
                                elseif ($persona->tipo === 'coordinador') echo 'Coordinador';
                                else echo 'Instructor';
                            ?></span>
                        </div>
                        <?php if (isset($persona->centro_nombre)): ?>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-university w-6 text-primary"></i>
                            <span class="ml-2">Centro: <?php echo htmlspecialchars($persona->centro_nombre); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($persona->tipo === 'coordinador' && isset($persona->descripcion)): ?>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-briefcase w-6 text-primary"></i>
                            <span class="ml-2">Coordinación: <?php echo htmlspecialchars($persona->descripcion); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-lock text-primary mr-2"></i>Cambiar Contraseña
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Deja estos campos vacíos si no deseas cambiar tu contraseña
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                            <input type="password" 
                                   name="current_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                            <input type="password" 
                                   name="password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                            <input type="password" 
                                   name="confirm_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="<?php echo url('dashboard', 'index'); ?>" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition shadow-lg">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <p class="text-gray-600">No se pudo cargar la información del perfil</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas (solo para instructores) -->
    <?php if (isset($persona) && $persona->tipo === 'instructor'): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Asignaciones</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        require_once __DIR__ . '/../../Database.php';
                        $db = Database::getInstance();
                        $result = $db->selectOne("SELECT COUNT(*) as total FROM ASIGNACION WHERE INSTRUCTOR_inst_id = ?", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tasks text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Fichas</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        $result = $db->selectOne("SELECT COUNT(DISTINCT FICHA_fich_id) as total FROM ASIGNACION WHERE INSTRUCTOR_inst_id = ?", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-id-card text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Competencias</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        $result = $db->selectOne("SELECT COUNT(DISTINCT COMPETENCIA_comp_id) as total FROM ASIGNACION WHERE INSTRUCTOR_inst_id = ?", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-2xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tarjetas de Estadísticas (solo para coordinadores) -->
    <?php if (isset($persona) && $persona->tipo === 'coordinador'): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Fichas a Cargo</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        require_once __DIR__ . '/../../Database.php';
                        $db = Database::getInstance();
                        $result = $db->selectOne("SELECT COUNT(*) as total FROM FICHA WHERE COORDINACION_coord_id = ?", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-id-card text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Instructores</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        $result = $db->selectOne("SELECT COUNT(DISTINCT INSTRUCTOR_inst_id_lider) as total FROM FICHA WHERE COORDINACION_coord_id = ?", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-2xl text-primary"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Asignaciones</p>
                    <p class="text-3xl font-bold text-primary mt-2">
                        <?php
                        $result = $db->selectOne("
                            SELECT COUNT(*) as total 
                            FROM ASIGNACION a
                            INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
                            WHERE f.COORDINACION_coord_id = ?
                        ", [$persona->id]);
                        echo $result['total'] ?? 0;
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-2xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
