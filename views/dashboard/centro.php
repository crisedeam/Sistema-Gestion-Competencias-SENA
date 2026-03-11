<!-- Stats Cards para Centro de Formación -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Coordinaciones</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['coordinaciones']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-users-cog text-2xl text-primary"></i>
            </div>
        </div>
    </div>

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
                <p class="text-sm text-gray-600 mb-1">Programas</p>
                <p class="text-4xl font-bold text-primary"><?php echo $data['stats']['programas']; ?></p>
            </div>
            <div class="w-14 h-14 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                <i class="fas fa-book text-2xl text-primary"></i>
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

<!-- Mensaje de bienvenida -->
<div class="bg-white rounded-xl shadow-md p-8 text-center">
    <i class="fas fa-university text-6xl text-primary mb-4"></i>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Panel de Administración</h2>
    <p class="text-gray-600">Gestiona todos los aspectos del centro de formación desde el menú lateral</p>
</div>
