<?php
require_once __DIR__ . '/Model.php';

class TituloPrograma extends Model {
    
    // Configuración de la tabla
    protected static $table = 'TITULO_PROGRAMA';
    protected static $primaryKey = 'titpro_id';
    protected static $columns = ['titpro_id', 'titpro_nombre'];
    
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
        return new self(
            $data['titpro_id'],
            $data['titpro_nombre']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'titpro_id' => $this->id,
            'titpro_nombre' => $this->nombre
        ];
    }

    /**
     * Validar datos del título
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->nombre)) {
            $errors[] = 'El nombre del título es obligatorio';
        }
        
        if (strlen($this->nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->nombre) > 45) {
            $errors[] = 'El nombre no puede exceder 45 caracteres';
        }
        
        return $errors;
    }

    /**
     * Verificar si el título tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $programas = $db->select("SELECT COUNT(*) as count FROM PROGRAMA WHERE TIT_PROGRAMA_titpro_id = ?", [$id]);
        return $programas[0]['count'] > 0 ? (int)$programas[0]['count'] : 0;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>
