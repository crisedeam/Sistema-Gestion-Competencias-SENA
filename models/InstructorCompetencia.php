<?php
require_once __DIR__ . '/Model.php';

/**
 * Modelo InstructorCompetencia
 * Gestiona la relación entre instructores y competencias de programas
 * Tabla: INSTRUCTOR_COMPETENCIA
 */
class InstructorCompetencia extends Model {
    
    // Configuración de la tabla
    protected static $table = 'INSTRUCTOR_COMPETENCIA';
    protected static $primaryKey = 'inscomp_id';
    protected static $columns = ['inscomp_id', 'INSTRUCTOR_inst_id', 'COMPETENCIA_PROGRAMA_PROGRAMA_prog_id', 'COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id', 'inscomp_vigencia'];
    
    // Propiedades
    private $id;
    private $instructorId;
    private $programaId;
    private $competenciaId;
    private $vigencia;

    public function __construct($id, $instructorId, $programaId, $competenciaId, $vigencia) {
        $this->id = $id;
        $this->instructorId = $instructorId;
        $this->programaId = $programaId;
        $this->competenciaId = $competenciaId;
        $this->vigencia = $vigencia;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getInstructorId() { return $this->instructorId; }
    public function getProgramaId() { return $this->programaId; }
    public function getCompetenciaId() { return $this->competenciaId; }
    public function getVigencia() { return $this->vigencia; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setInstructorId($instructorId) { $this->instructorId = $instructorId; }
    public function setProgramaId($programaId) { $this->programaId = $programaId; }
    public function setCompetenciaId($competenciaId) { $this->competenciaId = $competenciaId; }
    public function setVigencia($vigencia) { $this->vigencia = $vigencia; }

    /**
     * Crear instancia desde array de base de datos
     * @param array $data Datos de la fila de BD
     * @return InstructorCompetencia Nueva instancia
     */
    protected static function fromArray($data) {
        return new self(
            $data['inscomp_id'],
            $data['INSTRUCTOR_inst_id'],
            $data['COMPETENCIA_PROGRAMA_PROGRAMA_prog_id'],
            $data['COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id'],
            $data['inscomp_vigencia']
        );
    }

    /**
     * Convertir instancia a array para BD
     * @return array Datos del modelo
     */
    public function toArray() {
        return [
            'inscomp_id' => $this->id,
            'INSTRUCTOR_inst_id' => $this->instructorId,
            'COMPETENCIA_PROGRAMA_PROGRAMA_prog_id' => $this->programaId,
            'COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id' => $this->competenciaId,
            'inscomp_vigencia' => $this->vigencia
        ];
    }

    /**
     * Validar datos de la relación instructor-competencia
     * @return array Array de errores (vacío si no hay errores)
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->instructorId)) {
            $errors[] = 'El instructor es obligatorio';
        }
        
        if (empty($this->programaId)) {
            $errors[] = 'El programa es obligatorio';
        }
        
        if (empty($this->competenciaId)) {
            $errors[] = 'La competencia es obligatoria';
        }
        
        if (empty($this->vigencia)) {
            $errors[] = 'La fecha de vigencia es obligatoria';
        }
        
        // Validar formato de fecha
        $fecha = \DateTime::createFromFormat('Y-m-d', $this->vigencia);
        if (!$fecha || $fecha->format('Y-m-d') !== $this->vigencia) {
            $errors[] = 'El formato de fecha de vigencia no es válido';
        }
        
        // Verificar si la relación ya existe (solo en creación)
        if (empty($this->id) && self::relationExists($this->instructorId, $this->programaId, $this->competenciaId)) {
            $errors[] = 'Esta relación ya existe. El instructor ya tiene asignada esta competencia del programa.';
        }
        
        // Verificar que la competencia pertenezca al programa
        if (!empty($this->programaId) && !empty($this->competenciaId)) {
            $db = Database::getInstance();
            $result = $db->select(
                "SELECT COUNT(*) as count FROM COMPETENCIA_PROGRAMA 
                 WHERE PROGRAMA_prog_id = ? AND COMPETENCIA_comp_id = ?",
                [$this->programaId, $this->competenciaId]
            );
            
            if ($result[0]['count'] == 0) {
                $errors[] = 'La competencia seleccionada no pertenece al programa seleccionado';
            }
        }
        
        return $errors;
    }

    /**
     * Buscar relaciones por término (incluye búsqueda por instructor, programa y competencia)
     * @param string $term Término de búsqueda
     * @return array Array de instancias InstructorCompetencia
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT ic.* 
                FROM INSTRUCTOR_COMPETENCIA ic
                LEFT JOIN INSTRUCTOR i ON ic.INSTRUCTOR_inst_id = i.inst_id
                LEFT JOIN PROGRAMA p ON ic.COMPETENCIA_PROGRAMA_PROGRAMA_prog_id = p.prog_codigo
                LEFT JOIN COMPETENCIA c ON ic.COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = c.comp_id
                WHERE ic.inscomp_id LIKE ? 
                   OR i.inst_nombres LIKE ? 
                   OR i.inst_apellidos LIKE ?
                   OR p.prog_denominacion LIKE ?
                   OR c.comp_nombre_corto LIKE ?
                   OR c.comp_nombre_unidad_competencia LIKE ?
                ORDER BY ic.inscomp_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Verificar si una relación específica existe
     * @param int $instructorId ID del instructor
     * @param string $programaId Código del programa
     * @param int $competenciaId ID de la competencia
     * @return bool True si existe, false si no
     */
    public static function relationExists($instructorId, $programaId, $competenciaId) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table . " 
                WHERE INSTRUCTOR_inst_id = ? 
                AND COMPETENCIA_PROGRAMA_PROGRAMA_prog_id = ? 
                AND COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = ?";
        $result = $db->select($sql, [$instructorId, $programaId, $competenciaId]);
        return $result && $result[0]['count'] > 0;
    }

    /**
     * Verificar si la relación tiene dependencias (asignaciones)
     * @param int $id ID de la relación
     * @return array Array con información de dependencias
     */
    public static function hasDependencies($id) {
        $relacion = self::find($id);
        if (!$relacion) {
            return [];
        }
        
        $db = Database::getInstance();
        
        // Verificar asignaciones
        $asignaciones = $db->select(
            "SELECT COUNT(*) as count FROM ASIGNACION 
             WHERE INSTRUCTOR_inst_id = ? AND COMPETENCIA_comp_id = ?",
            [$relacion->getInstructorId(), $relacion->getCompetenciaId()]
        );
        
        if ($asignaciones[0]['count'] > 0) {
            return [
                'asignaciones' => (int)$asignaciones[0]['count']
            ];
        }
        
        return [];
    }

    /**
     * Obtener relaciones por instructor
     * @param int $instructorId ID del instructor
     * @return array Array de instancias InstructorCompetencia
     */
    public static function getByInstructor($instructorId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " 
                WHERE INSTRUCTOR_inst_id = ? 
                ORDER BY inscomp_vigencia DESC";
        $result = $db->select($sql, [$instructorId]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Obtener relaciones por competencia de un programa
     * @param string $programaId Código del programa
     * @param int $competenciaId ID de la competencia
     * @return array Array de instancias InstructorCompetencia
     */
    public static function getByCompetencia($programaId, $competenciaId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " 
                WHERE COMPETENCIA_PROGRAMA_PROGRAMA_prog_id = ? 
                AND COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = ?
                ORDER BY inscomp_vigencia DESC";
        $result = $db->select($sql, [$programaId, $competenciaId]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Obtener relaciones por centro de formación (a través del instructor)
     * @param int $centroId ID del centro de formación
     * @return array Array de instancias InstructorCompetencia
     */
    public static function getByCentroFormacion($centroId) {
        $db = Database::getInstance();
        $sql = "SELECT ic.* 
                FROM INSTRUCTOR_COMPETENCIA ic
                INNER JOIN INSTRUCTOR i ON ic.INSTRUCTOR_inst_id = i.inst_id
                WHERE i.CENTRO_FORMACION_cent_id = ?
                ORDER BY ic.inscomp_id";
        $result = $db->select($sql, [$centroId]);
        
        $relaciones = [];
        foreach ($result as $row) {
            $relaciones[] = static::fromArray($row);
        }
        return $relaciones;
    }

    /**
     * Eliminar todas las relaciones de un instructor
     * @param int $instructorId ID del instructor
     * @return bool True si se eliminó correctamente
     */
    public static function deleteByInstructor($instructorId) {
        $db = Database::getInstance();
        $sql = "DELETE FROM " . static::$table . " WHERE INSTRUCTOR_inst_id = ?";
        return $db->delete($sql, [$instructorId]);
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchByInstructor($instructorId) {
        return self::getByInstructor($instructorId);
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchByCompetencia($programaId, $competenciaId) {
        return self::getByCompetencia($programaId, $competenciaId);
    }
}
?>
