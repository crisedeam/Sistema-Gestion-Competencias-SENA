<?php
require_once __DIR__ . '/Model.php';

class Ambiente extends Model {
    
    // Configuración de la tabla
    protected static $table = 'AMBIENTE';
    protected static $primaryKey = 'amb_id';
    protected static $columns = ['amb_id', 'amb_nombre', 'SEDE_sede_id'];
    
    // Propiedades
    private $id;
    private $nombre;
    private $sedeId;

    public function __construct($id, $nombre, $sedeId) {
        $this->id = $id;
        $this->nombre = trim($nombre);
        $this->sedeId = $sedeId;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getSedeId() { return $this->sedeId;  }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = trim($nombre);  }
    public function setSedeId($sedeId) { $this->sedeId = $sedeId;  }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['amb_id'],
            $data['amb_nombre'],
            $data['SEDE_sede_id']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'amb_id' => $this->id,
            'amb_nombre' => $this->nombre,
            'SEDE_sede_id' => $this->sedeId
        ];
    }

    /**
     * Validar datos del ambiente
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->id)) {
            $errors[] = 'El ID del ambiente es obligatorio';
        }
        
        if (strlen($this->id) > 10) {
            $errors[] = 'El ID no puede exceder 10 caracteres';
        }
        
        if (empty($this->nombre)) {
            $errors[] = 'El nombre del ambiente es obligatorio';
        }
        
        if (strlen($this->nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->nombre) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        if (empty($this->sedeId)) {
            $errors[] = 'La sede es obligatoria';
        }
        
        return $errors;
    }

    /**
     * Verificar si el ambiente tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $asignaciones = $db->select("SELECT COUNT(*) as count FROM ASIGNACION WHERE AMBIENTE_amb_id = ?", [$id]);
        return $asignaciones[0]['count'] > 0 ? (int)$asignaciones[0]['count'] : 0;
    }

    /**
     * Obtener ambientes por sede
     */
    public static function getBySede($sedeId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE SEDE_sede_id = ? ORDER BY amb_nombre";
        $result = $db->select($sql, [$sedeId]);
        
        $ambientes = [];
        foreach ($result as $row) {
            $ambientes[] = static::fromArray($row);
        }
        return $ambientes;
    }

    /**
     * Buscar ambientes por término (incluye búsqueda por sede)
     * Sobrescribe el método de Model para incluir JOIN con SEDE
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT a.* 
                FROM AMBIENTE a
                LEFT JOIN SEDE s ON a.SEDE_sede_id = s.sede_id
                WHERE a.amb_id LIKE ? 
                   OR a.amb_nombre LIKE ? 
                   OR s.sede_nombre LIKE ?
                ORDER BY a.amb_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm]);
        
        $ambientes = [];
        foreach ($result as $row) {
            $ambientes[] = static::fromArray($row);
        }
        return $ambientes;
    }

    /**
     * Verificar disponibilidad del ambiente
     */
    public static function verificarDisponibilidad($ambienteId, $fecha, $horaInicio, $horaFin, $asignacionIdExcluir = null) {
        $db = Database::getInstance();
        
        $sql = "SELECT a.ASIG_ID, da.deta_fecha, da.deta_hora_inicio, da.deta_hora_fin,
                       f.fich_id, c.comp_nombre_corto
                FROM ASIGNACION a
                INNER JOIN DETALLE_ASIGNACION da ON a.ASIG_ID = da.ASIGNACION_asig_id
                INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
                INNER JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
                WHERE a.AMBIENTE_amb_id = ?
                AND da.deta_fecha = ?
                AND (
                    (da.deta_hora_inicio < ? AND da.deta_hora_fin > ?)
                    OR (da.deta_hora_inicio < ? AND da.deta_hora_fin > ?)
                    OR (da.deta_hora_inicio >= ? AND da.deta_hora_fin <= ?)
                )";
        
        $params = [
            $ambienteId,
            $fecha,
            $horaFin, $horaInicio,
            $horaFin, $horaFin,
            $horaInicio, $horaFin
        ];
        
        if ($asignacionIdExcluir !== null) {
            $sql .= " AND a.ASIG_ID != ?";
            $params[] = $asignacionIdExcluir;
        }
        
        $result = $db->select($sql, $params);
        
        $conflictos = [];
        foreach ($result as $row) {
            $conflictos[] = [
                'asignacion_id' => $row['ASIG_ID'],
                'ficha' => $row['fich_id'],
                'competencia' => $row['comp_nombre_corto'],
                'fecha' => $row['deta_fecha'],
                'hora_inicio' => substr($row['deta_hora_inicio'], 0, 5),
                'hora_fin' => substr($row['deta_hora_fin'], 0, 5)
            ];
        }
        
        return [
            'disponible' => empty($conflictos),
            'conflictos' => $conflictos
        ];
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
    public static function searchBySede($sedeId) {
        return self::getBySede($sedeId);
    }
}
?>