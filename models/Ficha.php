<?php
require_once __DIR__ . '/Model.php';

class Ficha extends Model {
    
    protected static $table = 'FICHA';
    protected static $primaryKey = 'fich_id';
    protected static $columns = [
        'fich_id',
        'PROGRAMA_prog_id',
        'INSTRUCTOR_inst_id_lider',
        'fich_jornada',
        'COORDINACION_coord_id',
        'fich_fecha_ini_lectiva',
        'fich_fecha_fin_lectiva'
    ];
    
    private $id;
    private $programaCodigo;
    private $instructorLiderId;
    private $jornada;
    private $coordinacionId;
    private $fechaIniLectiva;
    private $fechaFinLectiva;

    public function __construct($id, $programaCodigo, $instructorLiderId, $jornada, $coordinacionId, $fechaIniLectiva, $fechaFinLectiva) {
        $this->id = $id;
        $this->programaCodigo = trim($programaCodigo);
        $this->instructorLiderId = $instructorLiderId;
        $this->jornada = trim($jornada);
        $this->coordinacionId = $coordinacionId;
        $this->fechaIniLectiva = $fechaIniLectiva;
        $this->fechaFinLectiva = $fechaFinLectiva;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getProgramaCodigo() { return $this->programaCodigo; }
    public function getInstructorLiderId() { return $this->instructorLiderId; }
    public function getJornada() { return $this->jornada; }
    public function getCoordinacionId() { return $this->coordinacionId; }
    public function getFechaIniLectiva() { return $this->fechaIniLectiva; }
    public function getFechaFinLectiva() { return $this->fechaFinLectiva; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setProgramaCodigo($programaCodigo) { $this->programaCodigo = trim($programaCodigo); }
    public function setInstructorLiderId($instructorLiderId) { $this->instructorLiderId = $instructorLiderId; }
    public function setJornada($jornada) { $this->jornada = trim($jornada); }
    public function setCoordinacionId($coordinacionId) { $this->coordinacionId = $coordinacionId; }
    public function setFechaIniLectiva($fechaIniLectiva) { $this->fechaIniLectiva = $fechaIniLectiva; }
    public function setFechaFinLectiva($fechaFinLectiva) { $this->fechaFinLectiva = $fechaFinLectiva; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['fich_id'],
            $data['PROGRAMA_prog_id'],
            $data['INSTRUCTOR_inst_id_lider'],
            $data['fich_jornada'],
            $data['COORDINACION_coord_id'],
            $data['fich_fecha_ini_lectiva'],
            $data['fich_fecha_fin_lectiva']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'fich_id' => $this->id,
            'PROGRAMA_prog_id' => $this->programaCodigo,
            'INSTRUCTOR_inst_id_lider' => $this->instructorLiderId,
            'fich_jornada' => $this->jornada,
            'COORDINACION_coord_id' => $this->coordinacionId,
            'fich_fecha_ini_lectiva' => $this->fechaIniLectiva,
            'fich_fecha_fin_lectiva' => $this->fechaFinLectiva
        ];
    }

    /**
     * Validar datos de la ficha
     * @param bool $isUpdate Indica si es una actualización (para evitar validar duplicados en la misma ficha)
     */
    public function validate($isUpdate = false) {
        $errors = [];
        
        if (empty($this->id)) {
            $errors[] = 'El ID de la ficha es obligatorio';
        } else {
            // Verificar si el ID ya existe (solo para nuevas fichas)
            if (!$isUpdate && self::exists($this->id)) {
                $errors[] = 'El número de ficha ya existe. Por favor, use un número diferente.';
            }
        }
        
        if (empty($this->programaCodigo)) {
            $errors[] = 'El programa es obligatorio';
        }
        
        if (empty($this->instructorLiderId)) {
            $errors[] = 'El instructor líder es obligatorio';
        }
        
        if (empty($this->jornada)) {
            $errors[] = 'La jornada es obligatoria';
        } elseif (!in_array($this->jornada, ['Mañana', 'Tarde', 'Noche', 'Mixta'])) {
            $errors[] = 'La jornada debe ser: Mañana, Tarde, Noche o Mixta';
        }
        
        if (empty($this->coordinacionId)) {
            $errors[] = 'La coordinación es obligatoria';
        }
        
        if (empty($this->fechaIniLectiva)) {
            $errors[] = 'La fecha de inicio lectiva es obligatoria';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaIniLectiva)) {
            $errors[] = 'La fecha de inicio lectiva debe tener formato YYYY-MM-DD';
        }
        
        if (empty($this->fechaFinLectiva)) {
            $errors[] = 'La fecha de fin lectiva es obligatoria';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaFinLectiva)) {
            $errors[] = 'La fecha de fin lectiva debe tener formato YYYY-MM-DD';
        }
        
        // Validar que fecha fin sea posterior a fecha inicio
        if (!empty($this->fechaIniLectiva) && !empty($this->fechaFinLectiva)) {
            if (strtotime($this->fechaFinLectiva) < strtotime($this->fechaIniLectiva)) {
                $errors[] = 'La fecha de fin lectiva debe ser posterior a la fecha de inicio';
            }
        }
        
        // Validar que el instructor no sea líder de otra ficha en la misma jornada
        if (!empty($this->instructorLiderId) && !empty($this->jornada)) {
            if (self::instructorEsLiderEnJornada($this->instructorLiderId, $this->jornada, $this->id)) {
                $errors[] = 'El instructor ya es líder de otra ficha en la jornada ' . $this->jornada;
            }
        }
        
        return $errors;
    }

    /**
     * Buscar fichas con información de relaciones (JOIN)
     */
    public static function search($term = '') {
        $db = Database::getInstance();
        
        $sql = "SELECT f.fich_id, f.PROGRAMA_prog_id, f.INSTRUCTOR_inst_id_lider, f.fich_jornada, 
                       f.COORDINACION_coord_id, f.fich_fecha_ini_lectiva, f.fich_fecha_fin_lectiva,
                       p.prog_denominacion, 
                       i.inst_nombres, i.inst_apellidos,
                       c.coord_descripcion
                FROM FICHA f
                LEFT JOIN PROGRAMA p ON f.PROGRAMA_prog_id = p.prog_id
                LEFT JOIN INSTRUCTOR i ON f.INSTRUCTOR_inst_id_lider = i.inst_id
                LEFT JOIN COORDINACION c ON f.COORDINACION_coord_id = c.coord_id
                WHERE f.fich_id LIKE ? 
                   OR f.fich_jornada LIKE ?
                   OR p.prog_denominacion LIKE ?
                   OR i.inst_nombres LIKE ?
                   OR i.inst_apellidos LIKE ?
                   OR c.coord_descripcion LIKE ?
                ORDER BY f.fich_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $fichas = [];
        foreach ($result as $row) {
            $fichas[] = self::fromArray($row);
        }
        return $fichas;
    }

    /**
     * Obtener fichas por coordinación
     */
    public static function getByCoordinacion($coordinacionId) {
        $db = Database::getInstance();
        $sql = "SELECT fich_id, PROGRAMA_prog_id, INSTRUCTOR_inst_id_lider, fich_jornada, 
                       COORDINACION_coord_id, fich_fecha_ini_lectiva, fich_fecha_fin_lectiva 
                FROM FICHA 
                WHERE COORDINACION_coord_id = ?
                ORDER BY fich_id";
        $result = $db->select($sql, [$coordinacionId]);
        
        $fichas = [];
        foreach ($result as $row) {
            $fichas[] = self::fromArray($row);
        }
        return $fichas;
    }

    /**
     * Obtener fichas por programa
     */
    public static function getByPrograma($programaCodigo) {
        $db = Database::getInstance();
        $sql = "SELECT fich_id, PROGRAMA_prog_id, INSTRUCTOR_inst_id_lider, fich_jornada, 
                       COORDINACION_coord_id, fich_fecha_ini_lectiva, fich_fecha_fin_lectiva 
                FROM FICHA 
                WHERE PROGRAMA_prog_id = ?
                ORDER BY fich_id";
        $result = $db->select($sql, [$programaCodigo]);
        
        $fichas = [];
        foreach ($result as $row) {
            $fichas[] = self::fromArray($row);
        }
        return $fichas;
    }

    /**
     * Verificar dependencias antes de eliminar
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $dependencies = [];
        
        // Verificar asignaciones
        $result = $db->selectOne("SELECT COUNT(*) as count FROM ASIGNACION WHERE FICHA_fich_id = ?", [$id]);
        if ($result && $result['count'] > 0) {
            $dependencies['asignaciones'] = (int)$result['count'];
        }
        
        return $dependencies;
    }

    /**
     * Verificar si un instructor ya es líder de una ficha en una jornada específica
     */
    public static function instructorEsLiderEnJornada($instructorId, $jornada, $fichaIdExcluir = null) {
        $db = Database::getInstance();
        
        if ($fichaIdExcluir) {
            $sql = "SELECT COUNT(*) as count FROM FICHA 
                    WHERE INSTRUCTOR_inst_id_lider = ? 
                    AND fich_jornada = ? 
                    AND fich_id != ?";
            $result = $db->selectOne($sql, [$instructorId, $jornada, $fichaIdExcluir]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM FICHA 
                    WHERE INSTRUCTOR_inst_id_lider = ? 
                    AND fich_jornada = ?";
            $result = $db->selectOne($sql, [$instructorId, $jornada]);
        }
        
        return $result && $result['count'] > 0;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>
