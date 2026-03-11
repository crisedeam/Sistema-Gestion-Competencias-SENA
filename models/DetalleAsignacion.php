<?php
require_once __DIR__ . '/Model.php';
    
class DetalleAsignacion extends Model {
    
    protected static $table = 'DETALLE_ASIGNACION';
    protected static $primaryKey = 'detasig_id';
    protected static $columns = [
        'detasig_id',
        'ASIGNACION_ASIG_ID',
        'detasig_fecha',
        'detasig_hora_ini',
        'detasig_hora_fin'
    ];
    
    private $id;
    private $asignacionId;
    private $fecha;
    private $horaInicio;
    private $horaFin;

    public function __construct($id, $asignacionId, $fecha, $horaInicio, $horaFin) {
        $this->id = $id;
        $this->asignacionId = $asignacionId;
        $this->fecha = $fecha;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getAsignacionId() { return $this->asignacionId; }
    public function getFecha() { return $this->fecha; }
    public function getHoraInicio() { return $this->horaInicio; }
    public function getHoraFin() { return $this->horaFin; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setAsignacionId($asignacionId) { $this->asignacionId = $asignacionId; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function setHoraInicio($horaInicio) { $this->horaInicio = $horaInicio; }
    public function setHoraFin($horaFin) { $this->horaFin = $horaFin; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['detasig_id'] ?? null,
            $data['ASIGNACION_ASIG_ID'],
            $data['detasig_fecha'],
            $data['detasig_hora_ini'],
            $data['detasig_hora_fin']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'detasig_id' => $this->id,
            'ASIGNACION_ASIG_ID' => $this->asignacionId,
            'detasig_fecha' => $this->fecha,
            'detasig_hora_ini' => $this->horaInicio,
            'detasig_hora_fin' => $this->horaFin
        ];
    }

    /**
     * Validar datos del detalle de asignación
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->asignacionId)) {
            $errors[] = 'La asignación es obligatoria';
        }
        
        if (empty($this->fecha)) {
            $errors[] = 'La fecha es obligatoria';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fecha)) {
            $errors[] = 'La fecha debe tener formato YYYY-MM-DD';
        }
        
        if (empty($this->horaInicio)) {
            $errors[] = 'La hora de inicio es obligatoria';
        }
        
        if (empty($this->horaFin)) {
            $errors[] = 'La hora de fin es obligatoria';
        }
        
        // Validar que hora fin sea posterior a hora inicio
        if (!empty($this->horaInicio) && !empty($this->horaFin)) {
            if ($this->horaFin <= $this->horaInicio) {
                $errors[] = 'La hora de fin debe ser posterior a la hora de inicio';
            }
        }
        
        return $errors;
    }

    /**
     * Obtener detalles por asignación
     */
    public static function searchByAsignacion($asignacionId) {
        $db = Database::getInstance();
        $sql = "SELECT detasig_id, ASIGNACION_ASIG_ID, detasig_fecha, detasig_hora_ini, detasig_hora_fin 
                FROM DETALLE_ASIGNACION WHERE ASIGNACION_ASIG_ID = ? ORDER BY detasig_fecha, detasig_hora_ini";
        $result = $db->select($sql, [$asignacionId]);
        
        $detalles = [];
        foreach ($result as $row) {
            $detalles[] = self::fromArray($row);
        }
        return $detalles;
    }

    /**
     * Eliminar todos los detalles de una asignación
     */
    public static function deleteByAsignacion($asignacionId) {
        $db = Database::getInstance();
        $sql = "DELETE FROM DETALLE_ASIGNACION WHERE ASIGNACION_ASIG_ID = ?";
        return $db->delete($sql, [$asignacionId]);
    }

    /**
     * Calcular horas totales de un conjunto de detalles
     */
    public static function calcularHorasTotales($detalles) {
        $totalHoras = 0;
        foreach ($detalles as $detalle) {
            $inicio = new DateTime($detalle->getHoraInicio());
            $fin = new DateTime($detalle->getHoraFin());
            $diferencia = $fin->diff($inicio);
            $totalHoras += $diferencia->h + ($diferencia->i / 60);
        }
        return $totalHoras;
    }

    /**
     * Obtener nombre del día en español
     */
    public static function getNombreDiaEspanol($fecha) {
        $dias = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        $diaIngles = date('l', strtotime($fecha));
        return $dias[$diaIngles] ?? 'Desconocido';
    }

    /**
     * Validar que no haya conflictos de horarios en la misma fecha
     */
    public static function validarConflictoHorarios($asignacionId, $fecha, $horaInicio, $horaFin, $detalleId = null) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as conflictos FROM DETALLE_ASIGNACION 
                WHERE ASIGNACION_ASIG_ID = ? AND detasig_fecha = ? 
                AND ((detasig_hora_ini < ? AND detasig_hora_fin > ?) 
                     OR (detasig_hora_ini < ? AND detasig_hora_fin > ?)
                     OR (detasig_hora_ini >= ? AND detasig_hora_fin <= ?))";
        
        $params = [$asignacionId, $fecha, $horaFin, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin];
        
        if ($detalleId) {
            $sql .= " AND detasig_id != ?";
            $params[] = $detalleId;
        }
        
        $result = $db->selectOne($sql, $params);
        return $result['conflictos'] > 0;
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
