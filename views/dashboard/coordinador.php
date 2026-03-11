<!-- Stats Cards para Coordinador -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Instructores</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['instructores']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Fichas Activas</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['fichas']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-id-card text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Asignaciones</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['asignaciones']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-alt text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Ambientes</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['ambientes']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-door-open text-2xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Asignaciones Recientes -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-primary bg-opacity-10 px-6 py-4 border-b border-primary border-opacity-20">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock mr-2 text-primary"></i>Asignaciones Recientes
            </h3>
        </div>
        <div class="p-6">
            <?php if (count($data['asignaciones_recientes']) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($data['asignaciones_recientes'] as $asig): 
                    $diasPasados = floor((time() - strtotime($asig['asig_fecha_ini'])) / 86400);
                    $tiempoTexto = $diasPasados == 0 ? 'Hoy' : ($diasPasados == 1 ? 'Ayer' : "Hace $diasPasados días");
                ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800"><?php echo e($asig['inst_nombres'] . ' ' . $asig['inst_apellidos']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo e($asig['comp_nombre_corto']); ?> - Ficha <?php echo $asig['FICHA_fich_id']; ?></p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500"><?php echo $tiempoTexto; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">No hay asignaciones recientes</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ambientes Disponibles -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-primary bg-opacity-10 px-6 py-4 border-b border-primary border-opacity-20">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-door-open mr-2 text-primary"></i>Ambientes Ahora
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <?php foreach (array_slice($data['ambientes_disponibilidad'], 0, 6) as $amb): ?>
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <span class="font-medium text-gray-800"><?php echo e($amb['nombre']); ?></span>
                    <span class="px-3 py-1 <?php echo $amb['disponible'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs font-semibold rounded-full">
                        <?php echo $amb['disponible'] ? 'Disponible' : 'Ocupado'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
