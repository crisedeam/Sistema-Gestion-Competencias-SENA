<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1ABC9C',
                        'primary-dark': '#148F77',
                        'primary-hover': '#48C9B0',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary-dark via-primary to-primary-dark min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white p-4 rounded-full shadow-lg mb-4">
                <i class="fas fa-user-plus text-5xl text-primary"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Crear Cuenta</h1>
            <p class="text-white text-opacity-90">Sistema de Gestión Académica SENA</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <form method="POST" action="" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-primary mr-2"></i>Nombre
                        </label>
                        <input type="text" name="nombre" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Juan">
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-primary mr-2"></i>Apellido
                        </label>
                        <input type="text" name="apellido" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="Pérez">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-primary mr-2"></i>Correo Electrónico
                    </label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                           placeholder="correo@ejemplo.com">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone text-primary mr-2"></i>Teléfono
                        </label>
                        <input type="tel" name="telefono" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="3001234567">
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag text-primary mr-2"></i>Rol
                        </label>
                        <select name="rol" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">
                            <option value="">Seleccione un rol</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                </div>

                <!-- Especialidad (solo para instructores) -->
                <div id="especialidadField" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-certificate text-primary mr-2"></i>Especialidad
                    </label>
                    <input type="text" name="especialidad" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                           placeholder="Ej: Programación, Diseño, Electrónica">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-primary mr-2"></i>Contraseña
                        </label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="••••••••">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-primary mr-2"></i>Confirmar Contraseña
                        </label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start">
                    <input type="checkbox" required class="w-4 h-4 mt-1 text-primary border-gray-300 rounded focus:ring-primary">
                    <label class="ml-2 text-sm text-gray-600">
                        Acepto los <a href="#" class="text-primary hover:text-primary-dark font-medium">términos y condiciones</a> 
                        y la <a href="#" class="text-primary hover:text-primary-dark font-medium">política de privacidad</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-primary to-primary-hover text-white py-3 rounded-lg font-semibold hover:from-primary-hover hover:to-primary transform hover:scale-[1.02] transition-all shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Crear Cuenta
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">¿Ya tienes una cuenta? 
                    <a href="login.php" class="text-primary hover:text-primary-dark font-semibold">Inicia sesión aquí</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-white text-opacity-90 text-sm">
            <p>&copy; 2026 SENA. Todos los derechos reservados.</p>
        </div>
    </div>

    <script>
        // Mostrar campo de especialidad solo para instructores
        document.querySelector('select[name="rol"]').addEventListener('change', function() {
            const especialidadField = document.getElementById('especialidadField');
            if (this.value === 'instructor') {
                especialidadField.style.display = 'block';
                document.querySelector('input[name="especialidad"]').required = true;
            } else {
                especialidadField.style.display = 'none';
                document.querySelector('input[name="especialidad"]').required = false;
            }
        });
    </script>
</body>
</html>
