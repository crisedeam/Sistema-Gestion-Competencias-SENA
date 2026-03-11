<?php
require_once __DIR__ . '/Model.php';

class CompetenciaPrograma extends Model {
    
    // Configuración de la tabla (relación muchos a muchos)
    protected static $table = 'COMPETENCIA_PROGRAMA';
    protected static $primaryKey = ['PROGRAMA_prog_id', 'COMPETENCIA_comp_id']; // Clave compuesta
    protected static $columns = ['PROGRAMA_prog_id', 'COMPETENCIA_comp_id'];
    
    // Propiedades
    private $programaCodigo;
    private $competenciaId;

    public function __construct($programaCodigo, $competenciaId) {
        $this->programaCodigo = $programaCodigo;
        $this->competenciaId = $competenciaId;
    }

    // Getters
    public function getProgramaCodigo() { return $this->programaCodigo; }
    public function getCompetenciaId() { return $this->competenciaId; }
    
    // Método para obtener un ID compuesto (para compatibilidad con vistas)
    public function getId() { 
        return $this->programaCodigo . '-' . $this->competenciaId; 
    }

    // Setters
    public function setProgramaCodigo($programaCodigo) { $this->programaCodigo = $programaCodigo; }
    public function setCompetenciaId($competenciaId) { $this->competenciaId = $competenciaId; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['PROGRAMA_prog_id'],
            $data['COMPETENCIA_comp_id']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'PROGRAMA_prog_id' => $this->programaCodigo,
            'COMPETENCIA_comp_id' => $this->competenciaId
        ];
    }

    /**
     * Validar datos de la relación
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->programaCodigo)) {
            $errors[] = 'El código del programa es obligatorio';
        }
        
        if (empty($this->competenciaId)) {
            $errors[] = 'El ID de la competencia es obligatorio';
        }
        
        // Verificar si la relación ya existe
        if (self::relationExists($this->programaCodigo, $this->competenciaId)) {
            $errors[] = 'Esta relación ya existe. El programa ya tiene asignada esta competencia.';
        }
        
        return $errors;
    }

    /**
     * Verificar si una relación existe (clave compuesta)
     */
    public static function relationExists($programaCodigo, $competenciaId) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table . " 
                WHERE PROGRAMA_prog_id = ? AND COMPETENCIA_comp_id = ?";
        $result = $db->select($sql, [$programaCodigo, $competenciaId]);
        return $result && $result[0]['count'] > 0;
    }

    /**
     * Sobrescribir exists() de Model para manejar ID compuesto
     */
    public static function exists($id) {
        $parts = explode('-', $id);
        if (count($parts) != 2) {
            return false;
        }
        return self::relationExists($parts[0], $parts[1]);
    }

    /**
     * Guardar nueva relación (sobrescrito para manejar clave compuesta)
     */
    public static function save($model) {
        $errors = $model->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $db = Database::getInstance();
        $data = $model->toArray();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        return $db->insert($sql, array_values($data));
    }

    /**
     * Actualizar relación (sobrescrito para manejar clave compuesta)
     */
    public static function update($model) {
        // En una tabla con clave compuesta, no hay UPDATE tradicional
        // Se debe eliminar la relación anterior y crear una nueva
        // Esto se maneja en el controlador
        throw new Exception('No se puede actualizar directamente una relación con clave compuesta. Use delete y save.');
    }

    /**
     * Obtener todos los registros (sobrescrito para manejar clave compuesta)
     */
    public static function all() {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " ORDER BY PROGRAMA_prog_id, COMPETENCIA_comp_id";
        $result = $db->select($sql);
        
        $items = [];
        foreach ($result as $row) {
            $items[] = static::fromArray($row);
        }
        return $items;
    }

    /**
     * Buscar por ID compuesto
     */
    public static function find($id) {
        // El ID es compuesto: programaCodigo-competenciaId
        $parts = explode('-', $id);
        if (count($parts) != 2) {
            return null;
        }
        
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " 
                WHERE PROGRAMA_prog_id = ? AND COMPETENCIA_comp_id = ?";
        $result = $db->selectOne($sql, [$parts[0], $parts[1]]);
        
        if ($result) {
            return static::fromArray($result);
        }
        return null;
    }

    /**
     * Eliminar relación con clave compuesta
     */
    public static function delete($id) {
        // El ID es compuesto: programaCodigo-competenciaId
        $parts = explode('-', $id);
        if (count($parts) != 2) {
            throw new Exception('ID de relación inválido');
        }
        
        return static::deleteByKeys($parts[0], $parts[1]);
    }

    /**
     * Eliminar por claves individuales
     */
    public static function deleteByKeys($programaCodigo, $competenciaId) {
        $db = Database::getInstance();
        $sql = "DELETE FROM " . static::$table . " 
                WHERE PROGRAMA_prog_id = ? AND COMPETENCIA_comp_id = ?";
        return $db->delete($sql, [$programaCodigo, $competenciaId]);
    }

    /**
     * Buscar relaciones por programa
     */
    public static function searchByPrograma($programaCodigo) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE PROGRAMA_prog_id = ? ORDER BY COMPETENCIA_comp_id";
        $result = $db->select($sql, [$programaCodigo]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Buscar relaciones por competencia
     */
    public static function searchByCompetencia($competenciaId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE COMPETENCIA_comp_id = ? ORDER BY PROGRAMA_prog_id";
        $result = $db->select($sql, [$competenciaId]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Búsqueda con JOIN para incluir nombres de programa y competencia
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT cp.* 
                FROM COMPETENCIA_PROGRAMA cp
                LEFT JOIN PROGRAMA p ON cp.PROGRAMA_prog_id = p.prog_codigo
                LEFT JOIN COMPETENCIA c ON cp.COMPETENCIA_comp_id = c.comp_id
                WHERE p.prog_codigo LIKE ? 
                   OR p.prog_denominacion LIKE ? 
                   OR c.comp_nombre_corto LIKE ?
                   OR c.comp_nombre_unidad_competencia LIKE ?
                ORDER BY cp.PROGRAMA_prog_id, cp.COMPETENCIA_comp_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Verificar si la relación tiene dependencias
     */
    public static function hasDependencies($programaCodigo, $competenciaId) {
        $db = Database::getInstance();
        
        // Verificar INSTRUCTOR_COMPETENCIA
        $instructores = $db->select(
            "SELECT COUNT(*) as count FROM INSTRUCTOR_COMPETENCIA 
             WHERE COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = ? 
             AND COMPETENCIA_PROGRAMA_PROGRAMA_prog_id = ?",
            [$competenciaId, $programaCodigo]
        );
        $countInstructores = (int)$instructores[0]['count'];
        
        if ($countInstructores > 0) {
            return "{$countInstructores} instructor(es)";
        }
        
        return 0;
    }

    /**
     * Eliminar todas las relaciones de un programa
     */
    public static function deleteByPrograma($programaCodigo) {
        $db = Database::getInstance();
        $sql = "DELETE FROM " . static::$table . " WHERE PROGRAMA_prog_id = ?";
        return $db->delete($sql, [$programaCodigo]);
    }

    /**
     * Obtener competencias de un programa con información completa
     */
    public static function getCompetenciasByPrograma($programaCodigo) {
        $db = Database::getInstance();
        $sql = "SELECT c.comp_id as id, c.comp_nombre_corto as nombre_corto, 
                       c.comp_nombre_unidad_competencia as nombre_unidad, c.comp_horas as horas
                FROM COMPETENCIA c
                INNER JOIN COMPETENCIA_PROGRAMA cp ON c.comp_id = cp.COMPETENCIA_comp_id
                WHERE cp.PROGRAMA_prog_id = ?
                ORDER BY c.comp_nombre_corto";
        
        return $db->select($sql, [$programaCodigo]);
    }

    /**
     * Paginación (sobrescrito para manejar clave compuesta)
     */
    public static function paginate($page = 1, $perPage = 10, $searchTerm = '') {
        $db = Database::getInstance();
        
        // Si hay término de búsqueda, usar el método search
        if (!empty($searchTerm)) {
            $allItems = static::search($searchTerm);
        } else {
            $allItems = static::all();
        }
        
        $total = count($allItems);
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));
        $offset = ($page - 1) * $perPage;
        
        $items = array_slice($allItems, $offset, $perPage);
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>
