<?php
require_once __DIR__ . '/Model.php';

class Sede extends Model {
    
    // Configuración de la tabla
    protected static $table = 'SEDE';
    protected static $primaryKey = 'sede_id';
    protected static $columns = ['sede_id', 'sede_nombre'];
    
    // Propiedades
    private $id;
    private $nombre;

    public function __construct($id, $nombre) {
        $this->id = $id;
        $this->nombre = trim($nombre);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = trim($nombre); }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self($data['sede_id'], $data['sede_nombre']);
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'sede_id' => $this->id,
            'sede_nombre' => $this->nombre
        ];
    }

    /**
     * Validar datos de la sede
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->nombre)) {
            $errors[] = 'El nombre de la sede es obligatorio';
        }
        
        if (strlen($this->nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->nombre) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        return $errors;
    }

    /**
     * Verificar si la sede tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $ambientes = $db->select("SELECT COUNT(*) as count FROM AMBIENTE WHERE SEDE_sede_id = ?", [$id]);
        return $ambientes[0]['count'] > 0 ? (int)$ambientes[0]['count'] : 0;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>