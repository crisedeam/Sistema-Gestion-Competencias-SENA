<?php 
$pageTitle = "Dashboard";
ob_start();
?>

<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-primary to-primary-hover rounded-xl shadow-md p-8 text-white">
        <div>
            <h1 class="text-3xl font-bold mb-2">
                ¡Bienvenido, <?php echo e(currentUserName()); ?>!
            </h1>
            <p class="text-white text-opacity-90">
                <?php 
                if ($data['role'] === 'coordinador') echo 'Panel de Control del Coordinador';
                elseif ($data['role'] === 'instructor') echo 'Panel de Control del Instructor';
                else echo 'Panel de Control del Centro de Formación';
                ?>
            </p>
        </div>
    </div>

    <?php if ($data['role'] === 'centro'): ?>
        <!-- Dashboard Centro de Formación -->
        <?php include 'dashboard/centro.php'; ?>
        
    <?php elseif ($data['role'] === 'coordinador'): ?>
        <!-- Dashboard Coordinador -->
        <?php include 'dashboard/coordinador.php'; ?>
        
    <?php else: ?>
        <!-- Dashboard Instructor -->
        <?php include 'dashboard/instructor.php'; ?>
        
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'layout/layout.php';
?>
