<?php
// Obtener información del usuario autenticado
$userRole = currentRole();
$userId = currentUserId();
$userName = currentUserName();

// Mantener compatibilidad con vistas antiguas
// Las vistas antiguas usan formato ?c=controller&a=action
// Necesitamos mantener esta compatibilidad temporalmente
if (!function_exists('oldUrl')) {
    function oldUrl($controller, $action, $params = [])
    {
        $url = 'index.php?c=' . $controller . '&a=' . $action;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }
}

// Definir módulos según el rol
$menuItems = [];

if ($userRole === 'centro') {
    $menuItems = [
        ['name' => 'Dashboard', 'icon' => 'home', 'url' => url('dashboard', 'index')],

        ['section' => 'Gestión'],
        ['name' => 'Sedes', 'icon' => 'building', 'url' => url('sede', 'show')],
        ['name' => 'Ambientes', 'icon' => 'door-open', 'url' => url('ambiente', 'show')],
        ['name' => 'Programas', 'icon' => 'graduation-cap', 'url' => url('programa', 'show')],
        ['name' => 'Instructores', 'icon' => 'chalkboard-teacher', 'url' => url('instructor', 'show')],
        ['name' => 'Competencias', 'icon' => 'book', 'url' => url('competencia', 'show')],
        ['name' => 'Coordinación', 'icon' => 'users-cog', 'url' => url('coordinacion', 'show')],
    ];
} elseif ($userRole === 'coordinador') {
    $menuItems = [
        ['name' => 'Dashboard', 'icon' => 'home', 'url' => url('dashboard', 'index')],

        ['section' => 'Gestión Académica'],
        ['name' => 'Competencias x Programa', 'icon' => 'link', 'url' => url('competenciaPrograma', 'show')],
        ['name' => 'Fichas', 'icon' => 'id-card', 'url' => url('ficha', 'show')],
        ['name' => 'Instructor x Competencia', 'icon' => 'user-check', 'url' => url('instructorCompetencia', 'show')],
        ['name' => 'Asignaciones', 'icon' => 'calendar-alt', 'url' => url('asignacion', 'show')],
    ];
} else { // instructor
    $menuItems = [
        ['name' => 'Dashboard', 'icon' => 'home', 'url' => url('dashboard', 'index')],

        ['section' => 'Mis Asignaciones'],
        ['name' => 'Ver Asignaciones', 'icon' => 'tasks', 'url' => url('instructor', 'misAsignaciones')],
        ['name' => 'Mis Horarios', 'icon' => 'calendar-week', 'url' => url('instructor', 'misHorarios')],
    ];
}

// Obtener la URL actual para resaltar el menú activo
$currentPath = $_SERVER['REQUEST_URI'];
// Soportar ambos formatos: controller/action y c/a
$currentController = $_GET['controller'] ?? $_GET['c'] ?? '';
$currentAction = $_GET['action'] ?? $_GET['a'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Sistema de Gestión'; ?> - SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- FullCalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js'></script>
    <!-- Calendario Component -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/views/assets/calendario-styles.css">
    <script src="<?php echo BASE_PATH; ?>/views/assets/calendario-component.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/views/assets/horarios-styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo PRIMARY_COLOR; ?>',
                        'primary-dark': '<?php echo PRIMARY_DARK; ?>',
                        'primary-hover': '<?php echo PRIMARY_HOVER; ?>',
                    }
                }
            }
        }
    </script>
    <style>
        .nav-tooltip {
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 0.75rem;
            padding: 0.5rem 1rem;
            background-color:
                <?php echo PRIMARY_DARK; ?>
            ;
            color: white;
            border-radius: 0.5rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s, visibility 0.2s;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            font-size: 0.875rem;
        }

        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color:
                <?php echo PRIMARY_DARK; ?>
            ;
        }

        .sidebar-collapsed .nav-item:hover .nav-tooltip {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-collapsed .nav-item {
            justify-content: center !important;
        }

        .sidebar-collapsed .nav-item i {
            margin: 0 !important;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(26, 188, 156, 0.3);
            border-radius: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(26, 188, 156, 0.5);
        }

        .sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(26, 188, 156, 0.3) transparent;
        }

        /* Estilos mejorados para menú activo */
        .nav-item.active {
            background: linear-gradient(135deg, rgba(26, 188, 156, 0.9), rgba(22, 160, 133, 0.9));
            box-shadow: 0 4px 12px rgba(26, 188, 156, 0.3);
            border-left: 4px solid rgba(255, 255, 255, 0.8);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid #f9fafb;
        }

        .nav-item:hover:not(.active) {
            background: rgba(26, 188, 156, 0.1);
            border-left: 2px solid rgba(26, 188, 156, 0.5);
        }

        .nav-item {
            position: relative;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-primary-dark transform transition-all duration-300 ease-in-out lg:relative -translate-x-full lg:translate-x-0">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div
                    class="flex flex-col items-center justify-center h-20 px-6 bg-primary-dark border-b border-primary">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-graduation-cap text-2xl text-white"></i>
                        <span class="text-white font-bold text-lg sidebar-text">ProgSENA</span>
                    </div>
                    <span class="text-primary-hover text-xs mt-1 sidebar-text">Gestión de Competencias</span>
                    <button onclick="toggleSidebar()"
                        class="lg:hidden text-white hover:text-primary-hover absolute right-4 top-4">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- User info section removed as requested -->

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 sidebar-nav">
                    <?php foreach ($menuItems as $item): ?>
                        <?php if (isset($item['section'])): ?>
                            <!-- Sección -->
                            <div class="px-4 py-2 mt-4 mb-2 first:mt-0">
                                <span
                                    class="text-primary-hover text-xs font-semibold uppercase sidebar-text"><?php echo $item['section']; ?></span>
                            </div>
                        <?php else: ?>
                            <!-- Item de menú -->
                            <?php
                            // Mejorar la detección del menú activo
                            $isActive = false;
                            
                            // Extraer el controlador y acción de la URL del item
                            $itemUrl = $item['url'];
                            $itemController = '';
                            $itemAction = '';
                            
                            // Soportar ambos formatos: controller= y c=
                            if (preg_match('/controller=([^&]+)/', $itemUrl, $matches)) {
                                $itemController = $matches[1];
                            } elseif (preg_match('/c=([^&]+)/', $itemUrl, $matches)) {
                                $itemController = $matches[1];
                            }
                            
                            // Soportar ambos formatos: action= y a=
                            if (preg_match('/action=([^&]+)/', $itemUrl, $matches)) {
                                $itemAction = $matches[1];
                            } elseif (preg_match('/a=([^&]+)/', $itemUrl, $matches)) {
                                $itemAction = $matches[1];
                            }
                            
                            // Lógica de detección mejorada
                            if ($itemController && $currentController && $itemController === $currentController) {
                                // Casos especiales para instructor (mantener lógica específica)
                                if ($userRole === 'instructor' && $itemController === 'instructor') {
                                    // Solo marcar activo si la acción coincide exactamente
                                    $isActive = ($itemAction === $currentAction);
                                } else {
                                    // Para otros roles: si el controlador coincide y estamos en acciones CRUD, marcar como activo
                                    $crudActions = ['show', 'create', 'edit', 'save', 'update', 'delete', 'register', 'updateshow', 'search', 'index'];
                                    if (in_array($currentAction, $crudActions)) {
                                        $isActive = true;
                                    } else {
                                        // Si hay acción específica, debe coincidir exactamente
                                        if ($itemAction && $currentAction) {
                                            $isActive = ($itemAction === $currentAction);
                                        } else {
                                            // Si no hay acción específica, considerar activo
                                            $isActive = true;
                                        }
                                    }
                                }
                            }
                            ?>
                            <a href="<?php echo $item['url']; ?>"
                                class="nav-item flex items-center px-4 py-3 mb-2 text-white hover:bg-primary rounded-lg transition-colors relative <?php echo $isActive ? 'active bg-primary shadow-lg' : ''; ?>">
                                <i class="fas fa-<?php echo $item['icon']; ?> w-5 <?php echo $isActive ? 'text-white' : ''; ?>"></i>
                                <span class="ml-3 sidebar-text <?php echo $isActive ? 'font-semibold' : ''; ?>"><?php echo $item['name']; ?></span>
                                <span class="nav-tooltip"><?php echo $item['name']; ?></span>
                                <?php if ($isActive): ?>
                                    <div class="absolute right-3">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-80"></div>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Opciones Comunes moved to header dropdown -->
                </nav>
            </div>
        </aside>

        <!-- Overlay para móvil -->
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" onclick="toggleSidebar()">
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-white shadow-md h-16">
                <div class="h-full px-6 flex items-center justify-between">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4">
                        <button onclick="toggleSidebar()" class="text-gray-600 hover:text-primary transition">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4 relative">
                        <button onclick="toggleUserDropdown()"
                            class="flex items-center space-x-3 text-gray-700 hover:text-primary transition focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold leading-tight"><?php echo e($userName); ?></p>
                                <p class="text-xs text-gray-500 capitalize">
                                    <?php
                                    if ($userRole === 'centro')
                                        echo 'Centro de Formación';
                                    elseif ($userRole === 'coordinador')
                                        echo 'Coordinador';
                                    else
                                        echo 'Instructor';
                                    ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdown"
                            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 hidden py-2 z-50">
                            <a href="<?php echo url('perfil', 'ver'); ?>"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition">
                                <i class="fas fa-user-circle w-5 mr-2"></i>Mi Perfil
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="<?php echo url('auth', 'logout'); ?>"
                                class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <i class="fas fa-sign-out-alt w-5 mr-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Mensajes de sesión -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div
                        class="mb-6 p-4 rounded-lg <?php echo $_SESSION['tipo_mensaje'] === 'error' ? 'bg-red-50 border-l-4 border-red-500 text-red-700' : 'bg-green-50 border-l-4 border-green-500 text-green-700'; ?> alert-message">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i
                                    class="fas fa-<?php echo $_SESSION['tipo_mensaje'] === 'error' ? 'exclamation-circle' : 'check-circle'; ?> mr-2"></i>
                                <span><?php echo e($_SESSION['mensaje']); ?></span>
                            </div>
                            <button onclick="closeAlert(this.parentElement.parentElement)"
                                class="text-current hover:opacity-75">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-700 alert-message">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <span><?php echo e($_SESSION['error']); ?></span>
                            </div>
                            <button onclick="closeAlert(this.parentElement.parentElement)"
                                class="text-current hover:opacity-75">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php
                // Hacer disponible $userRole para las vistas antiguas
                // Esto mantiene compatibilidad con el código existente
                $userRole = currentRole();
                echo $content ?? '';
                ?>
            </main>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">¿Confirmar eliminación?</h3>
                <p id="deleteMessage" class="text-gray-600 text-center mb-6">
                    ¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.
                </p>
                <div class="flex space-x-3">
                    <button onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button id="confirmDeleteBtn"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteUrl = '';
        let sidebarCollapsed = false;

        function showDeleteModal(url, message = '¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.') {
            deleteUrl = url;
            document.getElementById('deleteMessage').textContent = message;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            deleteUrl = '';
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('userDropdown');
            const button = dropdown.previousElementSibling;
            if (dropdown && button) {
                if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            } else {
                sidebarCollapsed = !sidebarCollapsed;

                if (sidebarCollapsed) {
                    sidebar.classList.add('w-20', 'sidebar-collapsed');
                    sidebar.classList.remove('w-64');
                    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.add('hidden'));
                    document.getElementById('user-info-section').classList.add('px-2');
                    document.getElementById('user-info-section').classList.remove('px-6');
                } else {
                    sidebar.classList.remove('w-20', 'sidebar-collapsed');
                    sidebar.classList.add('w-64');
                    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));
                    document.getElementById('user-info-section').classList.remove('px-2');
                    document.getElementById('user-info-section').classList.add('px-6');
                }
            }
        }

        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (window.innerWidth >= 1024) {
                overlay.classList.add('hidden');
                if (!sidebarCollapsed) {
                    sidebar.classList.remove('-translate-x-full');
                }
            } else {
                sidebar.classList.add('-translate-x-full');
                if (sidebarCollapsed) {
                    sidebarCollapsed = false;
                    sidebar.classList.remove('w-20', 'sidebar-collapsed');
                    sidebar.classList.add('w-64');
                    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));
                    document.getElementById('user-info-section').classList.remove('px-2');
                    document.getElementById('user-info-section').classList.add('px-6');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const alertMessages = document.querySelectorAll('.alert-message');
            alertMessages.forEach(function (alert) {
                setTimeout(function () {
                    closeAlert(alert);
                }, 5000);
            });
        });

        function closeAlert(alertElement) {
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateY(-10px)';
            alertElement.style.transition = 'all 0.3s ease-out';

            setTimeout(function () {
                alertElement.remove();
            }, 300);
        }
    </script>
</body>

</html>