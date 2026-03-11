<?php 

$isEdit = isset($ficha) && $ficha !== null;
$pageTitle = $isEdit ? "Editar Ficha" : "Nueva Ficha";
$action = $isEdit ? url('ficha', 'update') : url('ficha', 'save');
$buttonText = $isEdit ? "Actualizar" : "Guardar";

ob_start();
?>

<div class="space-y-6">
    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert-message p-4 rounded-lg flex items-center justify-between <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-3"></i>
                <span><?php echo $_SESSION['mensaje']; ?></span>
            </div>
            <button onclick="closeAlert(this.parentElement)" class="ml-4 hover:opacity-75 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'id-card'; ?> text-primary mr-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="<?php echo $action; ?><?php echo isset($_GET['coor_id']) ? '&coor_id=' . $_GET['coor_id'] : ''; ?>" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Primera fila: ID Ficha y Jornada -->
                <div>
                    <label for="fich_id" class="block text-sm font-medium text-gray-700 mb-2">
                        ID Ficha <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="fich_id" 
                           name="fich_id" 
                           value="<?php echo $isEdit ? htmlspecialchars($ficha->getId()) : ''; ?>"
                           <?php echo $isEdit ? 'readonly' : 'required'; ?>
                           placeholder="Ej: 2558963"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent <?php echo $isEdit ? 'bg-gray-100 cursor-not-allowed' : ''; ?>">
                </div>

                <div>
                    <label for="fich_jornada" class="block text-sm font-medium text-gray-700 mb-2">
                        Jornada <span class="text-red-500">*</span>
                    </label>
                    <select id="fich_jornada" 
                            name="fich_jornada" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione una jornada</option>
                        <option value="Mañana" <?php echo ($isEdit && $ficha->getJornada() == 'Mañana') ? 'selected' : ''; ?>>Mañana</option>
                        <option value="Tarde" <?php echo ($isEdit && $ficha->getJornada() == 'Tarde') ? 'selected' : ''; ?>>Tarde</option>
                        <option value="Noche" <?php echo ($isEdit && $ficha->getJornada() == 'Noche') ? 'selected' : ''; ?>>Noche</option>
                        <option value="Mixta" <?php echo ($isEdit && $ficha->getJornada() == 'Mixta') ? 'selected' : ''; ?>>Mixta</option>
                    </select>
                </div>

                <!-- Segunda fila: Programa -->
                <div class="md:col-span-2">
                    <label for="prog_codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        Programa <span class="text-red-500">*</span>
                    </label>
                    <select id="prog_codigo" 
                            name="prog_codigo" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un programa</option>
                        <?php 
                        if (isset($programas) && count($programas) > 0):
                            foreach ($programas as $programa): 
                                $selected = ($isEdit && $programa->getCodigo() == $ficha->getProgramaCodigo()) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($programa->getCodigo()); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($programa->getCodigo() . ' - ' . $programa->getDenominacion()); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- Tercera fila: Instructor Líder y Coordinación -->
                <div>
                    <label for="inst_id_lider" class="block text-sm font-medium text-gray-700 mb-2">
                        Instructor Líder <span class="text-red-500">*</span>
                    </label>
                    <select id="inst_id_lider" 
                            name="inst_id_lider" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un instructor</option>
                        <?php 
                        if (isset($instructores) && count($instructores) > 0):
                            foreach ($instructores as $instructor): 
                                $selected = ($isEdit && $instructor->getId() == $ficha->getInstructorLiderId()) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($instructor->getId()); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <div>
                    <label for="coord_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Coordinación <span class="text-red-500">*</span>
                    </label>
                    <?php if (currentRole() === 'coordinador'): ?>
                        <!-- Para coordinadores: mostrar su coordinación como texto fijo -->
                        <?php 
                        $coordActual = null;
                        if (isset($coordinacion)) {
                            $coordActual = $coordinacion;
                        } elseif (isset($todasCoordinaciones)) {
                            foreach ($todasCoordinaciones as $coord) {
                                if ($coord->getId() == currentUserId()) {
                                    $coordActual = ['coord_id' => $coord->getId(), 'coord_descripcion' => $coord->getDescripcion()];
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                            <i class="fas fa-users-cog mr-2 text-primary"></i>
                            <?php echo $coordActual ? htmlspecialchars($coordActual['coord_descripcion']) : 'Coordinación no encontrada'; ?>
                        </div>
                        <input type="hidden" name="coord_id" value="<?php echo $coordActual ? $coordActual['coord_id'] : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se asigna automáticamente su coordinación
                        </p>
                    <?php else: ?>
                        <!-- Para otros roles: selector normal -->
                        <select id="coord_id" 
                                name="coord_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Seleccione una coordinación</option>
                            <?php 
                            if (isset($todasCoordinaciones) && count($todasCoordinaciones) > 0):
                                foreach ($todasCoordinaciones as $coordItem): 
                                    $selected = '';
                                    if ($isEdit && $ficha->getCoordinacionId() == $coordItem->getId()) {
                                        $selected = 'selected';
                                    } elseif (!$isEdit && isset($coordinacion) && $coordinacion['coord_id'] == $coordItem->getId()) {
                                        $selected = 'selected';
                                    }
                            ?>
                                <option value="<?php echo htmlspecialchars($coordItem->getId()); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($coordItem->getDescripcion()); ?>
                                </option>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Cuarta fila: Fechas -->
                <div>
                    <label for="fich_fecha_ini_lectiva" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Inicio Lectiva <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="fich_fecha_ini_lectiva" 
                           name="fich_fecha_ini_lectiva" 
                           value="<?php echo $isEdit ? htmlspecialchars($ficha->getFechaIniLectiva()) : ''; ?>"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label for="fich_fecha_fin_lectiva" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Fin Lectiva <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="fich_fecha_fin_lectiva" 
                           name="fich_fecha_fin_lectiva" 
                           value="<?php echo $isEdit ? htmlspecialchars($ficha->getFechaFinLectiva()) : ''; ?>"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t">
                <a href="<?php echo url('ficha', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>" 
                   class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                    <i class="fas fa-save mr-2"></i><?php echo $buttonText; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/layout.php';
?>
