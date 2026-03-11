<?php
require_once __DIR__ . '/Model.php';

class Instructor extends Model {
    
    // Configuración de la tabla
    protected static $table = 'INSTRUCTOR';
    protected static $primaryKey = 'inst_id';
    protected static $columns = ['inst_id', 'inst_nombres', 'inst_apellidos', 'inst_correo', 'inst_telefono', 'CENTRO_FORMACION_cent_id'];
    
    // Propiedades
    private $id;
    private $nombres;
    private $apellidos;
    private $correo;
    private $telefono;
    private $centroFormacionId;
    private $password;

    public function __construct($id, $nombres, $apellidos, $correo, $telefono, $centroFormacionId = null, $password = null) {
        $this->id = $id;
        $this->nombres = trim($nombres);
        $this->apellidos = trim($apellidos);
        $this->correo = trim($correo);
        $this->telefono = trim($telefono);
        $this->centroFormacionId = $centroFormacionId;
        $this->password = $password;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombres() { return $this->nombres; }
    public function getApellidos() { return $this->apellidos; }
    public function getCorreo() { return $this->correo; }
    public function getTelefono() { return $this->telefono; }
    public function getCentroFormacionId() { return $this->centroFormacionId; }
    public function getPassword() { return $this->password; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNombres($nombres) { $this->nombres = trim($nombres); }
    public function setApellidos($apellidos) { $this->apellidos = trim($apellidos); }
    public function setCorreo($correo) { $this->correo = trim($correo); }
    public function setTelefono($telefono) { $this->telefono = trim($telefono); }
    public function setCentroFormacionId($centroFormacionId) { $this->centroFormacionId = $centroFormacionId; }
    public function setPassword($password) { $this->password = $password; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['inst_id'],
            $data['inst_nombres'],
            $data['inst_apellidos'],
            $data['inst_correo'],
            $data['inst_telefono'],
            $data['CENTRO_FORMACION_cent_id']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'inst_id' => $this->id,
            'inst_nombres' => $this->nombres,
            'inst_apellidos' => $this->apellidos,
            'inst_correo' => $this->correo,
            'inst_telefono' => $this->telefono,
            'CENTRO_FORMACION_cent_id' => $this->centroFormacionId
        ];
    }

    /**
     * Validar datos del instructor
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->nombres)) {
            $errors[] = 'Los nombres son obligatorios';
        }
        
        if (strlen($this->nombres) < 2) {
            $errors[] = 'Los nombres deben tener al menos 2 caracteres';
        }
        
        if (empty($this->apellidos)) {
            $errors[] = 'Los apellidos son obligatorios';
        }
        
        if (strlen($this->apellidos) < 2) {
            $errors[] = 'Los apellidos deben tener al menos 2 caracteres';
        }
        
        if (empty($this->correo)) {
            $errors[] = 'El correo es obligatorio';
        }
        
        if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo no es válido';
        }
        
        if (empty($this->telefono)) {
            $errors[] = 'El teléfono es obligatorio';
        }
        
        if (!preg_match('/^[0-9]{10}$/', $this->telefono)) {
            $errors[] = 'El teléfono debe tener 10 dígitos';
        }
        
        if (empty($this->centroFormacionId)) {
            $errors[] = 'El centro de formación es obligatorio';
        }
        
        return $errors;
    }

    /**
     * Buscar instructores por término (incluye búsqueda por centro)
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT i.* 
                FROM INSTRUCTOR i
                LEFT JOIN CENTRO_FORMACION cf ON i.CENTRO_FORMACION_cent_id = cf.cent_id
                WHERE i.inst_id LIKE ? 
                   OR i.inst_nombres LIKE ? 
                   OR i.inst_apellidos LIKE ?
                   OR i.inst_correo LIKE ?
                   OR i.inst_telefono LIKE ?
                   OR cf.cent_nombre LIKE ?
                ORDER BY i.inst_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $instructores = [];
        foreach ($result as $row) {
            $instructores[] = static::fromArray($row);
        }
        return $instructores;
    }

    /**
     * Guardar nuevo instructor con contraseña
     */
    public static function saveWithPassword($instructor, $password) {
        $errors = $instructor->validate();
        
        if (empty($password)) {
            $errors[] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $db = Database::getInstance();
        $sql = "INSERT INTO INSTRUCTOR (inst_nombres, inst_apellidos, inst_correo, inst_password, inst_telefono, CENTRO_FORMACION_cent_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $db->insert($sql, [
            $instructor->getNombres(),
            $instructor->getApellidos(),
            $instructor->getCorreo(),
            $hashedPassword,
            $instructor->getTelefono(),
            $instructor->getCentroFormacionId()
        ]);
    }

    /**
     * Verificar si el instructor tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $dependencies = [];
        
        // Verificar si es líder de alguna ficha
        $fichas = $db->select("SELECT COUNT(*) as count FROM FICHA WHERE INSTRUCTOR_inst_id_lider = ?", [$id]);
        if ($fichas[0]['count'] > 0) {
            $dependencies['fichas'] = (int)$fichas[0]['count'];
        }
        
        // Verificar si tiene asignaciones
        $asignaciones = $db->select("SELECT COUNT(*) as count FROM ASIGNACION WHERE INSTRUCTOR_inst_id = ?", [$id]);
        if ($asignaciones[0]['count'] > 0) {
            $dependencies['asignaciones'] = (int)$asignaciones[0]['count'];
        }
        
        // Verificar si tiene competencias asignadas
        $competencias = $db->select("SELECT COUNT(*) as count FROM INSTRUCTOR_COMPETENCIA WHERE INSTRUCTOR_inst_id = ?", [$id]);
        if ($competencias[0]['count'] > 0) {
            $dependencies['competencias'] = (int)$competencias[0]['count'];
        }
        
        return $dependencies;
    }

    /**
     * Obtener instructores por centro de formación
     */
    public static function getByCentroFormacion($centroFormacionId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE CENTRO_FORMACION_cent_id = ? ORDER BY inst_nombres, inst_apellidos";
        $result = $db->select($sql, [$centroFormacionId]);
        
        $instructores = [];
        foreach ($result as $row) {
            $instructores[] = static::fromArray($row);
        }
        return $instructores;
    }

    /**
     * Paginar instructores por centro de formación
     */
    public static function paginateByCentro($centroId, $page = 1, $perPage = 10, $search = '') {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        
        // Construir consulta base
        $whereClause = "WHERE i.CENTRO_FORMACION_cent_id = ?";
        $params = [$centroId];
        
        // Agregar búsqueda si existe
        if (!empty($search)) {
            $whereClause .= " AND (i.inst_id LIKE ? OR i.inst_nombres LIKE ? OR i.inst_apellidos LIKE ? OR i.inst_correo LIKE ? OR i.inst_telefono LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM INSTRUCTOR i
                     {$whereClause}";
        $countResult = $db->selectOne($countSql, $params);
        $total = $countResult['total'];
        
        // Obtener datos paginados
        $dataSql = "SELECT i.* 
                    FROM INSTRUCTOR i
                    {$whereClause}
                    ORDER BY i.inst_nombres, i.inst_apellidos
                    LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $result = $db->select($dataSql, $params);
        
        $data = [];
        foreach ($result as $row) {
            $data[] = static::fromArray($row);
        }
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'total_items' => $total,
            'items_per_page' => $perPage
        ];
    }

    /**
     * Obtener instructores disponibles para una competencia específica
     */
    public static function getInstructoresPorCompetencia($competenciaId, $centroFormacionId = null) {
        $db = Database::getInstance();
        $sql = "SELECT DISTINCT i.*
                FROM INSTRUCTOR i
                INNER JOIN INSTRUCTOR_COMPETENCIA ic ON i.inst_id = ic.INSTRUCTOR_inst_id
                WHERE ic.COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id = ?";
        
        $params = [$competenciaId];
        
        if ($centroFormacionId !== null) {
            $sql .= " AND i.CENTRO_FORMACION_cent_id = ?";
            $params[] = $centroFormacionId;
        }
        
        $sql .= " ORDER BY i.inst_nombres, i.inst_apellidos";
        
        $result = $db->select($sql, $params);
        
        $instructores = [];
        foreach ($result as $row) {
            $instructores[] = static::fromArray($row);
        }
        return $instructores;
    }

    /**
     * Login de instructor
     */
    public static function login($correo, $password) {
        $db = Database::getInstance();
        $sql = "SELECT inst_id, inst_nombres, inst_apellidos, inst_correo, inst_password, inst_telefono, CENTRO_FORMACION_cent_id 
                FROM INSTRUCTOR WHERE inst_correo = ?";
        $result = $db->selectOne($sql, [$correo]);
        
        if ($result && password_verify($password, $result['inst_password'])) {
            return new self(
                $result['inst_id'],
                $result['inst_nombres'],
                $result['inst_apellidos'],
                $result['inst_correo'],
                $result['inst_telefono'],
                $result['CENTRO_FORMACION_cent_id']
            );
        }
        return null;
    }

    /**
     * Alias para mantener compatibilidad
     */
    public static function searchById($id) {
        return self::find($id);
    }

    /**
     * Alias para mantener compatibilidad con el método save anterior
     */
    public static function save($instructor, $password = null) {
        if ($password !== null) {
            return self::saveWithPassword($instructor, $password);
        }
        return parent::save($instructor);
    }
/**
     * Obtener estadísticas del dashboard para un instructor
     */
    public static function getDashboardStats($instructorId) {
        $db = Database::getInstance();
        
        $stats = [];
        
        // Total de asignaciones
        $result = $db->select("
            SELECT COUNT(*) as total 
            FROM ASIGNACION 
            WHERE INSTRUCTOR_inst_id = ?
        ", [$instructorId]);
        $stats['asignaciones'] = $result[0]['total'] ?? 0;
        
        // Horas semanales
        $result = $db->select("
            SELECT SUM(TIME_TO_SEC(TIMEDIFF(da.detasig_hora_fin, da.detasig_hora_ini)) / 3600) as total_horas
            FROM DETALLE_ASIGNACION da
            INNER JOIN ASIGNACION a ON da.ASIGNACION_ASIG_ID = a.ASIG_ID
            WHERE a.INSTRUCTOR_inst_id = ?
        ", [$instructorId]);
        $stats['horas_semanales'] = $result[0]['total_horas'] ?? 0;
        
        return $stats;
    }

    /**
     * Obtener próxima clase de un instructor
     */
    public static function getNextClass($instructorId) {
        $db = Database::getInstance();
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        
        $result = $db->select("
            SELECT c.comp_nombre_corto, da.detasig_hora_ini, da.detasig_hora_fin, 
                   a.AMBIENTE_amb_id, a.FICHA_fich_id, da.detasig_fecha
            FROM DETALLE_ASIGNACION da
            INNER JOIN ASIGNACION a ON da.ASIGNACION_ASIG_ID = a.ASIG_ID
            INNER JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
            WHERE a.INSTRUCTOR_inst_id = ?
            AND (
                (da.detasig_fecha = ? AND da.detasig_hora_ini > ?)
                OR da.detasig_fecha > ?
            )
            ORDER BY da.detasig_fecha, da.detasig_hora_ini
            LIMIT 1
        ", [$instructorId, $fechaHoy, $horaActual, $fechaHoy]);
        
        return $result[0] ?? null;
    }

    /**
     * Obtener horario del día para un instructor
     */
    public static function getTodaySchedule($instructorId) {
        $db = Database::getInstance();
        $fechaHoy = date('Y-m-d');
        
        return $db->select("
            SELECT c.comp_nombre_corto, da.detasig_hora_ini, da.detasig_hora_fin, 
                   amb.amb_nombre, a.FICHA_fich_id
            FROM DETALLE_ASIGNACION da
            INNER JOIN ASIGNACION a ON da.ASIGNACION_ASIG_ID = a.ASIG_ID
            INNER JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
            INNER JOIN AMBIENTE amb ON a.AMBIENTE_amb_id = amb.amb_id
            WHERE a.INSTRUCTOR_inst_id = ?
            AND da.detasig_fecha = ?
            ORDER BY da.detasig_hora_ini
        ", [$instructorId, $fechaHoy]);
    }
}
?>
