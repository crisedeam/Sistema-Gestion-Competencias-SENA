<?php
require_once __DIR__ . '/Model.php';

class Competencia extends Model {
    
    // Configuración de la tabla
    protected static $table = 'COMPETENCIA';
    protected static $primaryKey = 'comp_id';
    protected static $columns = ['comp_id', 'comp_nombre_corto', 'comp_horas', 'comp_nombre_unidad_competencia'];
    
    // Propiedades
    private $id;
    private $nombreCorto;
    private $nombreUnidad;
    private $horas;

    public function __construct($id, $nombreCorto, $nombreUnidad, $horas) {
        $this->id = $id;
        $this->nombreCorto = trim($nombreCorto);
        $this->nombreUnidad = trim($nombreUnidad);
        $this->horas = $horas;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombreCorto() { return $this->nombreCorto; }
    public function getNombreUnidad() { return $this->nombreUnidad; }
    public function getHoras() { return $this->horas; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNombreCorto($nombreCorto) { $this->nombreCorto = trim($nombreCorto); }
    public function setNombreUnidad($nombreUnidad) { $this->nombreUnidad = trim($nombreUnidad); }
    public function setHoras($horas) { $this->horas = $horas; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['comp_id'],
            $data['comp_nombre_corto'],
            $data['comp_nombre_unidad_competencia'],
            $data['comp_horas']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'comp_id' => $this->id,
            'comp_nombre_corto' => $this->nombreCorto,
            'comp_horas' => $this->horas,
            'comp_nombre_unidad_competencia' => $this->nombreUnidad
        ];
    }

    /**
     * Validar datos de la competencia
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->nombreCorto)) {
            $errors[] = 'El nombre corto es obligatorio';
        }
        
        if (strlen($this->nombreCorto) < 3) {
            $errors[] = 'El nombre corto debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->nombreCorto) > 30) {
            $errors[] = 'El nombre corto no puede exceder 30 caracteres';
        }
        
        if (empty($this->nombreUnidad)) {
            $errors[] = 'El nombre de la unidad de competencia es obligatorio';
        }
        
        if (strlen($this->nombreUnidad) < 10) {
            $errors[] = 'El nombre de la unidad debe tener al menos 10 caracteres';
        }
        
        if (strlen($this->nombreUnidad) > 200) {
            $errors[] = 'El nombre de la unidad no puede exceder 200 caracteres';
        }
        
        if (empty($this->horas) || $this->horas <= 0) {
            $errors[] = 'Las horas deben ser un número mayor a 0';
        }
        
        if (!is_numeric($this->horas)) {
            $errors[] = 'Las horas deben ser un valor numérico';
        }
        
        return $errors;
    }

    /**
     * Verificar si la competencia tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        
        // Verificar COMPETENCIA_PROGRAMA
        $programas = $db->select("SELECT COUNT(*) as count FROM COMPETENCIA_PROGRAMA WHERE COMPETENCIA_comp_id = ?", [$id]);
        $countProgramas = (int)$programas[0]['count'];
        
        // Verificar INSTRUCTOR_COMPETENCIA
        $instructores = $db->select("SELECT COUNT(*) as count FROM INSTRUCTOR_COMPETENCIA WHERE COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = ?", [$id]);
        $countInstructores = (int)$instructores[0]['count'];
        
        // Verificar ASIGNACION
        $asignaciones = $db->select("SELECT COUNT(*) as count FROM ASIGNACION WHERE COMPETENCIA_comp_id = ?", [$id]);
        $countAsignaciones = (int)$asignaciones[0]['count'];
        
        if ($countProgramas > 0 || $countInstructores > 0 || $countAsignaciones > 0) {
            $mensaje = [];
            if ($countProgramas > 0) {
                $mensaje[] = "{$countProgramas} programa(s)";
            }
            if ($countInstructores > 0) {
                $mensaje[] = "{$countInstructores} instructor(es)";
            }
            if ($countAsignaciones > 0) {
                $mensaje[] = "{$countAsignaciones} asignación(es)";
            }
            return implode(', ', $mensaje);
        }
        
        return 0;
    }

    /**
     * Obtiene las competencias del programa de una ficha que aún no han sido asignadas a esa ficha
     */
    public static function getCompetenciasDisponiblesPorFicha($fichaId) {
        $db = Database::getInstance();
        
        $sql = "SELECT DISTINCT c.comp_id, c.comp_nombre_corto, c.comp_nombre_unidad_competencia, c.comp_horas
                FROM COMPETENCIA c
                INNER JOIN COMPETENCIA_PROGRAMA cp ON c.comp_id = cp.COMPETENCIA_comp_id
                INNER JOIN FICHA f ON cp.PROGRAMA_prog_id = f.PROGRAMA_prog_id
                WHERE f.fich_id = ?
                AND c.comp_id NOT IN (
                    SELECT COMPETENCIA_comp_id 
                    FROM ASIGNACION 
                    WHERE FICHA_fich_id = ?
                )
                ORDER BY c.comp_nombre_corto";
        
        $result = $db->select($sql, [$fichaId, $fichaId]);
        
        $competencias = [];
        foreach ($result as $row) {
            $competencias[] = static::fromArray($row);
        }
        return $competencias;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }
}
?>
