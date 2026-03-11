<?php
$pageTitle = 'Nueva Asignación';

// Recuperar datos del formulario si hay errores
$datosFormulario = $_SESSION['datos_formulario'] ?? [];
unset($_SESSION['datos_formulario']);

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-calendar-plus text-primary mr-2"></i>
            Crear Nueva Asignación
        </h2>
        <p class="text-gray-600 mt-2">Complete la información básica y programe las clases en el calendario</p>
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

    <?php
    $esEdicion = false;
    require_once __DIR__ . '/form.php';
    ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/layout.php';
?>
