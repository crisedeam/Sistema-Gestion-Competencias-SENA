<?php
require_once __DIR__ . '/Model.php';

class Asignacion extends Model {
    
    protected static $table = 'ASIGNACION';
    protected static $primaryKey = 'ASIG_ID';
    protected static $columns = [
        'ASIG_ID',
        'INSTRUCTOR_inst_id',
        'FICHA_fich_id',
        'AMBIENTE_amb_id',
        'COMPETENCIA_comp_id',
        'asig_fecha_ini',
        'asig_fecha_fin'
    ];
    
    private $id;
    private $instructorId;
    private $fichaId;
    private $ambienteId;
    private $competenciaId;
    private $fechaInicio;
    private $fechaFin;

    public function __construct($id, $instructorId, $fichaId, $ambienteId, $competenciaId, $fechaInicio, $fechaFin) {
        $this->id = $id;
        $this->instructorId = $instructorId;
        $this->fichaId = $fichaId;
        $this->ambienteId = $ambienteId;
        $this->competenciaId = $competenciaId;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getInstructorId() { return $this->instructorId; }
    public function getFichaId() { return $this->fichaId; }
    public function getAmbienteId() { return $this->ambienteId; }
    public function getCompetenciaId() { return $this->competenciaId; }
    public function getFechaInicio() { return $this->fechaInicio; }
    public function getFechaFin() { return $this->fechaFin; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setInstructorId($instructorId) { $this->instructorId = $instructorId; }
    public function setFichaId($fichaId) { $this->fichaId = $fichaId; }
    public function setAmbienteId($ambienteId) { $this->ambienteId = $ambienteId; }
    public function setCompetenciaId($competenciaId) { $this->competenciaId = $competenciaId; }
    public function setFechaInicio($fechaInicio) { $this->fechaInicio = $fechaInicio; }
    public function setFechaFin($fechaFin) { $this->fechaFin = $fechaFin; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['ASIG_ID'] ?? null,
            $data['INSTRUCTOR_inst_id'],
            $data['FICHA_fich_id'],
            $data['AMBIENTE_amb_id'],
            $data['COMPETENCIA_comp_id'],
            $data['asig_fecha_ini'],
            $data['asig_fecha_fin']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'ASIG_ID' => $this->id,
            'INSTRUCTOR_inst_id' => $this->instructorId,
            'FICHA_fich_id' => $this->fichaId,
            'AMBIENTE_amb_id' => $this->ambienteId,
            'COMPETENCIA_comp_id' => $this->competenciaId,
            'asig_fecha_ini' => $this->fechaInicio,
            'asig_fecha_fin' => $this->fechaFin
        ];
    }

    /**
     * Validar datos de la asignación
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->instructorId)) {
            $errors[] = 'El instructor es obligatorio';
        }
        
        if (empty($this->fichaId)) {
            $errors[] = 'La ficha es obligatoria';
        }
        
        if (empty($this->ambienteId)) {
            $errors[] = 'El ambiente es obligatorio';
        }
        
        if (empty($this->competenciaId)) {
            $errors[] = 'La competencia es obligatoria';
        }
        
        if (empty($this->fechaInicio)) {
            $errors[] = 'La fecha de inicio es obligatoria';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaInicio)) {
            $errors[] = 'La fecha de inicio debe tener formato YYYY-MM-DD';
        }
        
        if (empty($this->fechaFin)) {
            $errors[] = 'La fecha de fin es obligatoria';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaFin)) {
            $errors[] = 'La fecha de fin debe tener formato YYYY-MM-DD';
        }
        
        // Validar que fecha fin sea posterior a fecha inicio
        if (!empty($this->fechaInicio) && !empty($this->fechaFin)) {
            if (strtotime($this->fechaFin) < strtotime($this->fechaInicio)) {
                $errors[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }
        
        return $errors;
    }

    /**
     * Buscar asignaciones con información de relaciones (JOIN)
     */
    public static function search($term = '') {
        $db = Database::getInstance();
        
        $sql = "SELECT a.ASIG_ID, a.INSTRUCTOR_inst_id, a.FICHA_fich_id, a.AMBIENTE_amb_id, 
                       a.COMPETENCIA_comp_id, a.asig_fecha_ini, a.asig_fecha_fin,
                       i.inst_nombres, i.inst_apellidos,
                       f.fich_id,
                       amb.amb_nombre,
                       c.comp_nombre_corto
                FROM ASIGNACION a
                LEFT JOIN INSTRUCTOR i ON a.INSTRUCTOR_inst_id = i.inst_id
                LEFT JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
                LEFT JOIN AMBIENTE amb ON a.AMBIENTE_amb_id = amb.amb_id
                LEFT JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
                WHERE a.ASIG_ID LIKE ? 
                   OR i.inst_nombres LIKE ?
                   OR i.inst_apellidos LIKE ?
                   OR f.fich_id LIKE ?
                   OR amb.amb_nombre LIKE ?
                   OR c.comp_nombre_corto LIKE ?
                ORDER BY a.ASIG_ID DESC";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $asignaciones = [];
        foreach ($result as $row) {
            $asignaciones[] = self::fromArray($row);
        }
        return $asignaciones;
    }

    /**
     * Obtener asignaciones por ficha
     */
    public static function searchByFicha($fichaId) {
        $db = Database::getInstance();
        $sql = "SELECT ASIG_ID, INSTRUCTOR_inst_id, FICHA_fich_id, AMBIENTE_amb_id, COMPETENCIA_comp_id, asig_fecha_ini, asig_fecha_fin 
                FROM ASIGNACION
                WHERE FICHA_fich_id = ?
                ORDER BY asig_fecha_ini";
        $result = $db->select($sql, [$fichaId]);
        
        $asignaciones = [];
        foreach ($result as $row) {
            $asignaciones[] = self::fromArray($row);
        }
        return $asignaciones;
    }

    /**
     * Obtener asignaciones por instructor
     */
    public static function getByInstructor($instructorId) {
        $db = Database::getInstance();
        $sql = "SELECT ASIG_ID, INSTRUCTOR_inst_id, FICHA_fich_id, AMBIENTE_amb_id, COMPETENCIA_comp_id, asig_fecha_ini, asig_fecha_fin 
                FROM ASIGNACION
                WHERE INSTRUCTOR_inst_id = ?
                ORDER BY asig_fecha_ini DESC";
        $result = $db->select($sql, [$instructorId]);
        
        $asignaciones = [];
        foreach ($result as $row) {
            $asignaciones[] = self::fromArray($row);
        }
        return $asignaciones;
    }

    /**
     * Obtener asignaciones por coordinación (a través de fichas)
     */
    public static function getByCoordinacion($coordinacionId) {
        $db = Database::getInstance();
        $sql = "SELECT a.ASIG_ID, a.INSTRUCTOR_inst_id, a.FICHA_fich_id, a.AMBIENTE_amb_id, 
                       a.COMPETENCIA_comp_id, a.asig_fecha_ini, a.asig_fecha_fin 
                FROM ASIGNACION a
                INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
                WHERE f.COORDINACION_coord_id = ?
                ORDER BY a.ASIG_ID DESC";
        $result = $db->select($sql, [$coordinacionId]);
        
        $asignaciones = [];
        foreach ($result as $row) {
            $asignaciones[] = self::fromArray($row);
        }
        return $asignaciones;
    }

    /**
     * Verificar dependencias antes de eliminar
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $dependencies = [];
        
        // Verificar detalles de asignación
        $result = $db->selectOne("SELECT COUNT(*) as count FROM DETALLE_ASIGNACION WHERE ASIGNACION_ASIG_ID = ?", [$id]);
        if ($result && $result['count'] > 0) {
            $dependencies['detalles'] = (int)$result['count'];
        }
        
        return $dependencies;
    }

    /**
     * Override save para manejar auto-increment
     */
    public static function save($model) {
        $db = Database::getInstance();
        $data = $model->toArray();
        
        // Remover el ID para que sea auto-increment
        unset($data[static::$primaryKey]);
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        return $db->insert($sql, array_values($data));
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>
