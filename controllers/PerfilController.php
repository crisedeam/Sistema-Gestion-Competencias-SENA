<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CentroFormacion.php';
require_once __DIR__ . '/../models/Coordinacion.php';
require_once __DIR__ . '/../models/Instructor.php';

class PerfilController extends Controller {
    
    protected $controllerName = 'perfil';

    /**
     * Ver perfil del usuario autenticado
     */
    function ver() {
        // Verificar autenticación
        if (!isAuthenticated()) {
            redirect('auth', 'login');
        }
        
        $userRole = currentRole();
        $userId = currentUserId();
        $persona = null;
        
        if ($userRole === 'centro') {
            // Obtener datos del centro de formación
            $centro = CentroFormacion::find($userId);
            
            if ($centro) {
                $persona = (object)[
                    'id' => $centro->getId(),
                    'nombre' => $centro->getNombre(),
                    'correo' => $centro->getCorreo(),
                    'tipo' => 'centro'
                ];
            }
            
        } elseif ($userRole === 'coordinador') {
            // Obtener datos de la coordinación
            $coordinacion = Coordinacion::find($userId);
            
            if ($coordinacion) {
                $persona = (object)[
                    'id' => $coordinacion->getId(),
                    'descripcion' => $coordinacion->getDescripcion(),
                    'nombre_coordinador' => $coordinacion->getNombreCoordinador(),
                    'correo' => $coordinacion->getCorreo(),
                    'centro_formacion_id' => $coordinacion->getCentroFormacionId(),
                    'tipo' => 'coordinador'
                ];
                
                // Obtener nombre del centro de formación
                $centro = CentroFormacion::find($coordinacion->getCentroFormacionId());
                $persona->centro_nombre = $centro ? $centro->getNombre() : 'N/A';
            }
            
        } elseif ($userRole === 'instructor') {
            // Obtener datos del instructor
            $instructor = Instructor::find($userId);
            
            if ($instructor) {
                $persona = (object)[
                    'id' => $instructor->getId(),
                    'nombres' => $instructor->getNombres(),
                    'apellidos' => $instructor->getApellidos(),
                    'correo' => $instructor->getCorreo(),
                    'telefono' => $instructor->getTelefono(),
                    'centro_formacion_id' => $instructor->getCentroFormacionId(),
                    'tipo' => 'instructor'
                ];
                
                // Obtener nombre del centro de formación
                if ($instructor->getCentroFormacionId()) {
                    $centro = CentroFormacion::find($instructor->getCentroFormacionId());
                    $persona->centro_nombre = $centro ? $centro->getNombre() : 'N/A';
                }
            }
        }
        
        if (!$persona) {
            $_SESSION['error'] = 'No se pudo cargar el perfil';
            redirect('dashboard', 'index');
        }
        
        $this->loadView('views/auth/perfil.php', ['persona' => $persona]);
    }
    
    /**
     * Actualizar perfil del usuario autenticado
     */
    function actualizar() {
        try {
            // Verificar autenticación
            if (!isAuthenticated()) {
                redirect('auth', 'login');
            }
            
            $userRole = currentRole();
            $userId = currentUserId();
            
            // Si se quiere cambiar la contraseña, validar campos
            $cambiarPassword = !empty($this->getPost('password'));
            if ($cambiarPassword) {
                $this->validatePost(['current_password', 'password', 'confirm_password']);
                
                // Validar que las contraseñas coincidan
                if ($this->getPost('password') !== $this->getPost('confirm_password')) {
                    throw new Exception('Las contraseñas no coinciden');
                }
            }
            
            if ($userRole === 'centro') {
                $this->validatePost(['nombre', 'correo']);
                
                $centro = CentroFormacion::find($userId);
                if (!$centro) {
                    throw new Exception('Centro de formación no encontrado');
                }
                
                // Validar contraseña actual si se quiere cambiar
                if ($cambiarPassword) {
                    if (!password_verify($this->getPost('current_password'), $centro->getPassword())) {
                        throw new Exception('La contraseña actual es incorrecta');
                    }
                    $centro->setPassword(password_hash($this->getPost('password'), PASSWORD_DEFAULT));
                }
                
                $centro->setNombre($this->getPost('nombre'));
                $centro->setCorreo($this->getPost('correo'));
                
                CentroFormacion::update($centro);
                
                // Actualizar sesión
                $_SESSION['user_nombre'] = $centro->getNombre();
                $_SESSION['user_correo'] = $centro->getCorreo();
                
            } elseif ($userRole === 'coordinador') {
                $this->validatePost(['nombre_coordinador', 'correo']);
                
                $coordinacion = Coordinacion::find($userId);
                if (!$coordinacion) {
                    throw new Exception('Coordinación no encontrada');
                }
                
                // Validar contraseña actual si se quiere cambiar
                if ($cambiarPassword) {
                    if (!password_verify($this->getPost('current_password'), $coordinacion->getPassword())) {
                        throw new Exception('La contraseña actual es incorrecta');
                    }
                    $coordinacion->setPassword(password_hash($this->getPost('password'), PASSWORD_DEFAULT));
                }
                
                $coordinacion->setNombreCoordinador($this->getPost('nombre_coordinador'));
                $coordinacion->setCorreo($this->getPost('correo'));
                
                Coordinacion::update($coordinacion);
                
                // Actualizar sesión
                $_SESSION['user_nombre'] = $coordinacion->getNombreCoordinador();
                $_SESSION['user_correo'] = $coordinacion->getCorreo();
                
            } elseif ($userRole === 'instructor') {
                $this->validatePost(['nombres', 'apellidos', 'correo', 'telefono']);
                
                $instructor = Instructor::find($userId);
                if (!$instructor) {
                    throw new Exception('Instructor no encontrado');
                }
                
                // Validar contraseña actual si se quiere cambiar
                if ($cambiarPassword) {
                    if (!password_verify($this->getPost('current_password'), $instructor->getPassword())) {
                        throw new Exception('La contraseña actual es incorrecta');
                    }
                    $instructor->setPassword(password_hash($this->getPost('password'), PASSWORD_DEFAULT));
                }
                
                $instructor->setNombres($this->getPost('nombres'));
                $instructor->setApellidos($this->getPost('apellidos'));
                $instructor->setCorreo($this->getPost('correo'));
                $instructor->setTelefono($this->getPost('telefono'));
                
                Instructor::update($instructor);
                
                // Actualizar sesión
                $_SESSION['user_nombre'] = $instructor->getNombres() . ' ' . $instructor->getApellidos();
                $_SESSION['user_correo'] = $instructor->getCorreo();
            }
            
            $_SESSION['mensaje'] = 'Perfil actualizado correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar perfil: ' . $e->getMessage();
        }
        
        redirect('perfil', 'ver');
    }
}
?>