<?php
require_once __DIR__ . '/Model.php';

class Programa extends Model {
    
    // Configuración de la tabla
    protected static $table = 'PROGRAMA';
    protected static $primaryKey = 'prog_codigo';
    protected static $columns = ['prog_codigo', 'prog_denominacion', 'TIT_PROGRAMA_titpro_id', 'prog_tipo'];
    
    // Propiedades
    private $codigo;
    private $denominacion;
    private $tipo;
    private $tituloId;

    public function __construct($codigo, $denominacion, $tipo, $tituloId) {
        $this->codigo = $codigo;
        $this->denominacion = trim($denominacion);
        $this->tipo = trim($tipo);
        $this->tituloId = $tituloId;
    }

    // Getters
    public function getCodigo() { return $this->codigo; }
    public function getDenominacion() { return $this->denominacion; }
    public function getTipo() { return $this->tipo; }
    public function getTituloId() { return $this->tituloId; }

    // Setters
    public function setCodigo($codigo) { $this->codigo = $codigo; }
    public function setDenominacion($denominacion) { $this->denominacion = trim($denominacion); }
    public function setTipo($tipo) { $this->tipo = trim($tipo); }
    public function setTituloId($tituloId) { $this->tituloId = $tituloId; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['prog_codigo'],
            $data['prog_denominacion'],
            $data['prog_tipo'],
            $data['TIT_PROGRAMA_titpro_id']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'prog_codigo' => $this->codigo,
            'prog_denominacion' => $this->denominacion,
            'TIT_PROGRAMA_titpro_id' => $this->tituloId,
            'prog_tipo' => $this->tipo
        ];
    }

    /**
     * Validar datos del programa
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->codigo)) {
            $errors[] = 'El código del programa es obligatorio';
        }
        
        if (!is_numeric($this->codigo)) {
            $errors[] = 'El código debe ser numérico';
        }
        
        if (empty($this->denominacion)) {
            $errors[] = 'La denominación es obligatoria';
        }
        
        if (strlen($this->denominacion) < 3) {
            $errors[] = 'La denominación debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->denominacion) > 100) {
            $errors[] = 'La denominación no puede exceder 100 caracteres';
        }
        
        if (empty($this->tipo)) {
            $errors[] = 'El tipo de programa es obligatorio';
        }
        
        $tiposValidos = ['Técnico', 'Tecnólogo', 'Especialización'];
        if (!in_array($this->tipo, $tiposValidos)) {
            $errors[] = 'El tipo de programa no es válido';
        }
        
        if (empty($this->tituloId)) {
            $errors[] = 'El título del programa es obligatorio';
        }
        
        return $errors;
    }

    /**
     * Buscar programas por término (incluye búsqueda por título)
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT p.* 
                FROM PROGRAMA p
                LEFT JOIN TITULO_PROGRAMA t ON p.TIT_PROGRAMA_titpro_id = t.titpro_id
                WHERE p.prog_codigo LIKE ? 
                   OR p.prog_denominacion LIKE ? 
                   OR p.prog_tipo LIKE ?
                   OR t.titpro_nombre LIKE ?
                ORDER BY p.prog_codigo";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $programas = [];
        foreach ($result as $row) {
            $programas[] = static::fromArray($row);
        }
        return $programas;
    }

    /**
     * Verificar si el programa tiene dependencias
     */
    public static function hasDependencies($codigo) {
        $db = Database::getInstance();
        
        // Verificar fichas
        $fichas = $db->select("SELECT COUNT(*) as count FROM FICHA WHERE PROGRAMA_prog_id = ?", [$codigo]);
        $countFichas = (int)$fichas[0]['count'];
        
        // Verificar competencias
        $competencias = $db->select("SELECT COUNT(*) as count FROM COMPETENCIA_PROGRAMA WHERE PROGRAMA_prog_id = ?", [$codigo]);
        $countCompetencias = (int)$competencias[0]['count'];
        
        if ($countFichas > 0 || $countCompetencias > 0) {
            $mensaje = [];
            if ($countFichas > 0) {
                $mensaje[] = "{$countFichas} ficha(s)";
            }
            if ($countCompetencias > 0) {
                $mensaje[] = "{$countCompetencias} competencia(s)";
            }
            return implode(' y ', $mensaje);
        }
        
        return 0;
    }

    /**
     * Obtener programas por título
     */
    public static function getByTitulo($tituloId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE TIT_PROGRAMA_titpro_id = ? ORDER BY prog_denominacion";
        $result = $db->select($sql, [$tituloId]);
        
        $programas = [];
        foreach ($result as $row) {
            $programas[] = static::fromArray($row);
        }
        return $programas;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($codigo) {
        return self::find($codigo);
    }
}
?>