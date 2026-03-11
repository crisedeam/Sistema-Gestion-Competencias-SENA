<?php
require_once __DIR__ . '/Model.php';

class CentroFormacion extends Model {
    
    // Configuración de la tabla
    protected static $table = 'CENTRO_FORMACION';
    protected static $primaryKey = 'cent_id';
    protected static $columns = ['cent_id', 'cent_nombre', 'cent_correo', 'cent_password'];
    
    // Propiedades
    private $id;
    private $nombre;
    private $correo;
    private $password;

    public function __construct($id, $nombre, $correo = null, $password = null) {
        $this->id = $id;
        $this->nombre = trim($nombre);
        $this->correo = $correo ? trim($correo) : null;
        $this->password = $password;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getPassword() {
        return $this->password;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = trim($nombre);
    }

    public function setCorreo($correo) {
        $this->correo = trim($correo);
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['cent_id'], 
            $data['cent_nombre'],
            $data['cent_correo'] ?? null,
            $data['cent_password'] ?? null
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'cent_id' => $this->id,
            'cent_nombre' => $this->nombre,
            'cent_correo' => $this->correo,
            'cent_password' => $this->password
        ];
    }

    /**
     * Validar datos del centro
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->nombre)) {
            $errors[] = 'El nombre del centro es obligatorio';
        }
        
        if (strlen($this->nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->nombre) > 200) {
            $errors[] = 'El nombre no puede exceder 200 caracteres';
        }

        if (!empty($this->correo) && !filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido';
        }
        
        return $errors;
    }

    /**
     * Buscar centro por correo
     */
    public static function findByEmail($correo) {
        $db = Database::getInstance();
        $result = $db->select("SELECT * FROM " . self::$table . " WHERE cent_correo = ?", [$correo]);
        
        if (empty($result)) {
            return null;
        }
        
        return self::fromArray($result[0]);
    }

    /**
     * Verificar si el centro tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        
        // Verificar coordinaciones
        $coordinaciones = $db->select("SELECT COUNT(*) as count FROM COORDINACION WHERE CENTRO_FORMACION_cent_id = ?", [$id]);
        $countCoordinaciones = (int)$coordinaciones[0]['count'];
        
        // Verificar instructores
        $instructores = $db->select("SELECT COUNT(*) as count FROM INSTRUCTOR WHERE CENTRO_FORMACION_cent_id = ?", [$id]);
        $countInstructores = (int)$instructores[0]['count'];
        
        $total = $countCoordinaciones + $countInstructores;
        
        if ($total > 0) {
            $detalles = [];
            if ($countCoordinaciones > 0) {
                $detalles[] = "{$countCoordinaciones} coordinación(es)";
            }
            if ($countInstructores > 0) {
                $detalles[] = "{$countInstructores} instructor(es)";
            }
            
            return [
                'count' => $total,
                'message' => 'No se puede eliminar el centro porque tiene ' . implode(' y ', $detalles) . ' asociado(s)'
            ];
        }
        
        return ['count' => 0, 'message' => ''];
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }

    /**
     * Obtener estadísticas del dashboard para un centro
     */
    public static function getDashboardStats($centroId) {
        $db = Database::getInstance();
        
        $stats = [];
        
        // Total de coordinaciones
        $result = $db->select("
            SELECT COUNT(*) as total 
            FROM COORDINACION 
            WHERE CENTRO_FORMACION_cent_id = ?
        ", [$centroId]);
        $stats['coordinaciones'] = $result[0]['total'] ?? 0;
        
        // Total de instructores
        $result = $db->select("
            SELECT COUNT(*) as total 
            FROM INSTRUCTOR 
            WHERE CENTRO_FORMACION_cent_id = ?
        ", [$centroId]);
        $stats['instructores'] = $result[0]['total'] ?? 0;
        
        // Total de programas
        $result = $db->select("SELECT COUNT(*) as total FROM PROGRAMA");
        $stats['programas'] = $result[0]['total'] ?? 0;
        
        // Total de ambientes
        $result = $db->select("SELECT COUNT(*) as total FROM AMBIENTE");
        $stats['ambientes'] = $result[0]['total'] ?? 0;
        
        return $stats;
    }
}
?>
