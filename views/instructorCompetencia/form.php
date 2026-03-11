<?php 

// Determinar si es edición o creación
$isEdit = isset($relacion) && $relacion !== null;
$pageTitle = $isEdit ? "Editar Asignación de Competencia" : "Asignar Competencia a Instructor";
$baseParams = isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : [];
$action = $isEdit ? url('instructorCompetencia', 'update', $baseParams) : url('instructorCompetencia', 'save', $baseParams);
$buttonText = $isEdit ? "Actualizar Asignación" : "Guardar Asignación";
$buttonIcon = $isEdit ? "fa-save" : "fa-plus";

// Cargar datos necesarios
if (!isset($instructores)) {
    require_once __DIR__ . '/../../models/Instructor.php';
    $instructores = Instructor::all();
}

if (!isset($programas)) {
    require_once __DIR__ . '/../../models/Programa.php';
    $programas = Programa::all();
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
                <input type="hidden" name="inscomp_id" value="<?php echo htmlspecialchars($relacion->getId()); ?>">
            <?php endif; ?>
            
            <!-- Instructor -->
            <div>
                <label for="INSTRUCTOR_inst_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user text-primary mr-2"></i>Instructor
                    <span class="text-red-500">*</span>
                </label>
                <select id="INSTRUCTOR_inst_id" 
                        name="INSTRUCTOR_inst_id" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Seleccione un instructor</option>
                    <?php if (isset($instructores) && count($instructores) > 0): ?>
                        <?php foreach ($instructores as $instructor): ?>
                            <option value="<?php echo htmlspecialchars($instructor->getId()); ?>"
                                    <?php echo ($isEdit && $instructor->getId() == $relacion->getInstructorId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($instructor->getNombres() . ' ' . $instructor->getApellidos()); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Programa y Competencia -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="COMPETENCIA_PROGRAMA_PROGRAMA_prog_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book text-primary mr-2"></i>Programa
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="COMPETENCIA_PROGRAMA_PROGRAMA_prog_id" 
                            name="COMPETENCIA_PROGRAMA_PROGRAMA_prog_id" 
                            required
                            onchange="cargarCompetencias()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione un programa</option>
                        <?php if (isset($programas) && count($programas) > 0): ?>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?php echo htmlspecialchars($programa->getCodigo()); ?>"
                                        <?php echo ($isEdit && $programa->getCodigo() == $relacion->getProgramaId()) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($programa->getCodigo() . ' - ' . $programa->getDenominacion()); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-certificate text-primary mr-2"></i>Competencia
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id" 
                            name="COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Seleccione primero un programa</option>
                    </select>
                </div>
            </div>

            <!-- Fecha de Vigencia -->
            <div>
                <label for="inscomp_vigencia" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar text-primary mr-2"></i>Fecha de Vigencia
                    <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       id="inscomp_vigencia" 
                       name="inscomp_vigencia" 
                       value="<?php echo $isEdit ? htmlspecialchars($relacion->getVigencia()) : ''; ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">Fecha hasta la cual el instructor está certificado para dictar esta competencia</p>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a href="<?php echo url('instructorCompetencia', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>" 
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

<script>
async function cargarCompetencias() {
    const programaId = document.getElementById('COMPETENCIA_PROGRAMA_PROGRAMA_prog_id').value;
    const competenciaSelect = document.getElementById('COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id');
    <?php if ($isEdit): ?>
    const competenciaActual = '<?php echo $relacion->getCompetenciaId(); ?>';
    <?php endif; ?>
    
    competenciaSelect.innerHTML = '<option value="">Cargando...</option>';
    
    if (!programaId) {
        competenciaSelect.innerHTML = '<option value="">Seleccione primero un programa</option>';
        return;
    }
    
    try {
        const response = await fetch(`<?php echo url('instructorCompetencia', 'getCompetenciasByPrograma'); ?>&prog_id=${programaId}`);
        const data = await response.json();
        
        if (data.success && data.competencias.length > 0) {
            competenciaSelect.innerHTML = '<option value="">Seleccione una competencia</option>';
            data.competencias.forEach(comp => {
                const option = document.createElement('option');
                option.value = comp.id;
                option.textContent = comp.nombre_corto + ' - ' + comp.nombre_unidad;
                <?php if ($isEdit): ?>
                if (comp.id == competenciaActual) {
                    option.selected = true;
                }
                <?php endif; ?>
                competenciaSelect.appendChild(option);
            });
        } else {
            competenciaSelect.innerHTML = '<option value="">No hay competencias para este programa</option>';
        }
    } catch (error) {
        console.error('Error:', error);
        competenciaSelect.innerHTML = '<option value="">Error al cargar competencias</option>';
    }
}

// Cargar competencias al cargar la página (solo en edición)
<?php if ($isEdit): ?>
document.addEventListener('DOMContentLoaded', function() {
    cargarCompetencias();
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/layout.php';
?>
