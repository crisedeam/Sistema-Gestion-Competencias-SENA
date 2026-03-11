<?php
$pageTitle = 'Editar Asignación';

// Recuperar datos del formulario si hay errores
$datosFormulario = $_SESSION['datos_formulario'] ?? [];
unset($_SESSION['datos_formulario']);

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit text-primary mr-2"></i>
            Editar Asignación
        </h2>
        <p class="text-gray-600 mt-2">Modifique el ambiente y los horarios de la asignación</p>
    </div>

    <!-- Mostrar mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div
            class="alert-message mb-6 p-4 rounded-lg flex items-center justify-between <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <div class="flex items-center">
                <i
                    class="fas <?php echo $_SESSION['tipo_mensaje'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-3"></i>
                <span><?php echo htmlspecialchars($_SESSION['mensaje']); ?></span>
            </div>
            <button onclick="closeAlert(this.parentElement)" class="ml-4 hover:opacity-75 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($asignacion)): ?>
        <?php
        // Definir variables necesarias para form.php
        $ficha = null;
        if (isset($fichas)) {
            foreach ($fichas as $f) {
                if ($f->getId() == $asignacion->getFichaId()) {
                    $ficha = $f;
                    break;
                }
            }
        }

        $competencia = null;
        if (isset($competencias)) {
            foreach ($competencias as $c) {
                if ($c->getId() == $asignacion->getCompetenciaId()) {
                    $competencia = $c;
                    break;
                }
            }
        }

        $instructor = null;
        if (isset($instructores)) {
            foreach ($instructores as $i) {
                if ($i->getId() == $asignacion->getInstructorId()) {
                    $instructor = $i;
                    break;
                }
            }
        }

        $esEdicion = true;
        require_once __DIR__ . '/form.php';
        ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-3"></i>
                <p class="text-gray-600">No se encontró la asignación solicitada</p>
                <a href="<?php echo url('asignacion', 'show', isset($_GET['coor_id']) ? ['coor_id' => $_GET['coor_id']] : []); ?>"
                    class="inline-block mt-4 text-primary hover:text-primary-dark">
                    Volver al listado
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
