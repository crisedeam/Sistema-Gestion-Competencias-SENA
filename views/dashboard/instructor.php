<!-- Stats Cards para Instructor -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Mis Asignaciones</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['asignaciones']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-tasks text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Horas Totales</p>
                <p class="text-4xl font-bold text-primary"><?php echo number_format($data['stats']['horas_semanales'], 0); ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-2xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Próxima Clase</p>
                <?php if ($data['proxima_clase']): 
                    $fechaClase = $data['proxima_clase']['detasig_fecha'];
                    $fechaHoy = date('Y-m-d');
                    $diaTexto = $fechaClase == $fechaHoy ? 'Hoy' : formatDate($fechaClase, 'd/m');
                    $hora = formatTime($data['proxima_clase']['detasig_hora_ini']);
                ?>
                <p class="text-lg font-bold text-primary"><?php echo $diaTexto . ' ' . $hora; ?></p>
                <?php else: ?>
                <p class="text-lg font-bold text-gray-400">Sin clases</p>
                <?php endif; ?>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-2xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<!-- Horario de Hoy -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="bg-primary bg-opacity-10 px-6 py-4 border-b border-primary border-opacity-20">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-calendar-day mr-2 text-primary"></i>Mi Horario de Hoy
        </h3>
    </div>
    <div class="p-6">
        <?php if (count($data['horario_hoy']) > 0): ?>
        <div class="space-y-4">
            <?php foreach ($data['horario_hoy'] as $clase): 
                $horaInicio = formatTime($clase['detasig_hora_ini']);
                $horaFin = formatTime($clase['detasig_hora_fin']);
            ?>
            <div class="border-l-4 border-primary bg-primary bg-opacity-10 p-4 rounded-r-lg hover:bg-opacity-20 transition">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800"><?php echo e($clase['comp_nombre_corto']); ?></p>
                        <p class="text-sm text-gray-600 mt-1">Ficha <?php echo $clase['FICHA_fich_id']; ?> - <?php echo e($clase['amb_nombre']); ?></p>
                    </div>
                    <span class="text-sm font-medium text-primary"><?php echo $horaInicio . ' - ' . $horaFin; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No tienes clases programadas para hoy</p>
            <p class="text-gray-400 text-sm mt-2">¡Disfruta tu día libre!</p>
        </div>
        <?php endif; ?>
    </div>
</div>
