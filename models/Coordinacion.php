<?php
require_once __DIR__ . '/Model.php';

class Coordinacion extends Model {
    
    // Configuración de la tabla
    protected static $table = 'COORDINACION';
    protected static $primaryKey = 'coord_id';
    protected static $columns = ['coord_id', 'coord_descripcion', 'CENTRO_FORMACION_cent_id', 'coord_nombre_coordinador', 'coord_correo'];
    
    // Propiedades
    private $id;
    private $descripcion;
    private $centroFormacionId;
    private $nombreCoordinador;
    private $correo;
    private $password;

    public function __construct($id, $descripcion, $centroFormacionId, $nombreCoordinador, $correo, $password = null) {
        $this->id = $id;
        $this->descripcion = trim($descripcion);
        $this->centroFormacionId = $centroFormacionId;
        $this->nombreCoordinador = trim($nombreCoordinador);
        $this->correo = trim($correo);
        $this->password = $password;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getDescripcion() { return $this->descripcion; }
    public function getCentroFormacionId() { return $this->centroFormacionId; }
    public function getNombreCoordinador() { return $this->nombreCoordinador; }
    public function getCorreo() { return $this->correo; }
    public function getPassword() { return $this->password; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setDescripcion($descripcion) { $this->descripcion = trim($descripcion); }
    public function setCentroFormacionId($centroFormacionId) { $this->centroFormacionId = $centroFormacionId; }
    public function setNombreCoordinador($nombreCoordinador) { $this->nombreCoordinador = trim($nombreCoordinador); }
    public function setCorreo($correo) { $this->correo = trim($correo); }
    public function setPassword($password) { $this->password = $password; }

    /**
     * Crear instancia desde array
     */
    protected static function fromArray($data) {
        return new self(
            $data['coord_id'],
            $data['coord_descripcion'],
            $data['CENTRO_FORMACION_cent_id'],
            $data['coord_nombre_coordinador'],
            $data['coord_correo']
        );
    }

    /**
     * Convertir instancia a array
     */
    public function toArray() {
        return [
            'coord_id' => $this->id,
            'coord_descripcion' => $this->descripcion,
            'CENTRO_FORMACION_cent_id' => $this->centroFormacionId,
            'coord_nombre_coordinador' => $this->nombreCoordinador,
            'coord_correo' => $this->correo
        ];
    }

    /**
     * Validar datos de la coordinación
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->descripcion)) {
            $errors[] = 'La descripción es obligatoria';
        }
        
        if (strlen($this->descripcion) < 3) {
            $errors[] = 'La descripción debe tener al menos 3 caracteres';
        }
        
        if (strlen($this->descripcion) > 200) {
            $errors[] = 'La descripción no puede exceder 200 caracteres';
        }
        
        if (empty($this->centroFormacionId)) {
            $errors[] = 'El centro de formación es obligatorio';
        }
        
        if (empty($this->nombreCoordinador)) {
            $errors[] = 'El nombre del coordinador es obligatorio';
        }
        
        if (strlen($this->nombreCoordinador) < 3) {
            $errors[] = 'El nombre del coordinador debe tener al menos 3 caracteres';
        }
        
        if (empty($this->correo)) {
            $errors[] = 'El correo es obligatorio';
        }
        
        if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo no es válido';
        }
        
        return $errors;
    }

    /**
     * Buscar coordinaciones por término (incluye búsqueda por centro)
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $sql = "SELECT c.* 
                FROM COORDINACION c
                LEFT JOIN CENTRO_FORMACION cf ON c.CENTRO_FORMACION_cent_id = cf.cent_id
                WHERE c.coord_id LIKE ? 
                   OR c.coord_descripcion LIKE ? 
                   OR c.coord_nombre_coordinador LIKE ?
                   OR c.coord_correo LIKE ?
                   OR cf.cent_nombre LIKE ?
                ORDER BY c.coord_id";
        
        $searchTerm = "%{$term}%";
        $result = $db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $coordinaciones = [];
        foreach ($result as $row) {
            $coordinaciones[] = static::fromArray($row);
        }
        return $coordinaciones;
    }

    /**
     * Guardar nueva coordinación con contraseña
     */
    public static function saveWithPassword($coordinacion, $password) {
        $errors = $coordinacion->validate();
        
        if (empty($password)) {
            $errors[] = 'La contraseña es obligatoria';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $db = Database::getInstance();
        $sql = "INSERT INTO COORDINACION (coord_descripcion, CENTRO_FORMACION_cent_id, coord_nombre_coordinador, coord_correo, coord_password) 
                VALUES (?, ?, ?, ?, ?)";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $db->insert($sql, [
            $coordinacion->getDescripcion(),
            $coordinacion->getCentroFormacionId(),
            $coordinacion->getNombreCoordinador(),
            $coordinacion->getCorreo(),
            $hashedPassword
        ]);
    }

    /**
     * Verificar si la coordinación tiene dependencias
     */
    public static function hasDependencies($id) {
        $db = Database::getInstance();
        $fichas = $db->select("SELECT COUNT(*) as count FROM FICHA WHERE COORDINACION_coord_id = ?", [$id]);
        return $fichas[0]['count'] > 0 ? (int)$fichas[0]['count'] : 0;
    }

    /**
     * Obtener coordinaciones por centro
     */
    public static function getByCentro($centroId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE CENTRO_FORMACION_cent_id = ? ORDER BY coord_descripcion";
        $result = $db->select($sql, [$centroId]);
        
        $coordinaciones = [];
        foreach ($result as $row) {
            $coordinaciones[] = static::fromArray($row);
        }
        return $coordinaciones;
    }

    /**
     * Paginar coordinaciones por centro de formación
     */
    public static function paginateByCentro($centroId, $page = 1, $perPage = 10, $search = '') {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        
        // Construir consulta base
        $whereClause = "WHERE c.CENTRO_FORMACION_cent_id = ?";
        $params = [$centroId];
        
        // Agregar búsqueda si existe
        if (!empty($search)) {
            $whereClause .= " AND (c.coord_id LIKE ? OR c.coord_descripcion LIKE ? OR c.coord_nombre_coordinador LIKE ? OR c.coord_correo LIKE ? OR cf.cent_nombre LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM COORDINACION c
                     LEFT JOIN CENTRO_FORMACION cf ON c.CENTRO_FORMACION_cent_id = cf.cent_id
                     {$whereClause}";
        $countResult = $db->selectOne($countSql, $params);
        $total = $countResult['total'];
        
        // Obtener datos paginados
        $dataSql = "SELECT c.* 
                    FROM COORDINACION c
                    LEFT JOIN CENTRO_FORMACION cf ON c.CENTRO_FORMACION_cent_id = cf.cent_id
                    {$whereClause}
                    ORDER BY c.coord_id
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
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Login de coordinación
     */
    public static function login($correo, $password) {
        $db = Database::getInstance();
        $sql = "SELECT coord_id, coord_descripcion, CENTRO_FORMACION_cent_id, coord_nombre_coordinador, coord_correo, coord_password 
                FROM COORDINACION WHERE coord_correo = ?";
        $result = $db->selectOne($sql, [$correo]);
        
        if ($result && password_verify($password, $result['coord_password'])) {
            return new self(
                $result['coord_id'],
                $result['coord_descripcion'],
                $result['CENTRO_FORMACION_cent_id'],
                $result['coord_nombre_coordinador'],
                $result['coord_correo']
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
     * Alias para mantener compatibilidad
     */
    public static function searchByCentro($centroId) {
        return self::getByCentro($centroId);
    }

    /**
     * Alias para mantener compatibilidad con el método save anterior
     */
    public static function save($coordinacion, $password = null) {
        if ($password !== null) {
            return self::saveWithPassword($coordinacion, $password);
        }
        return parent::save($coordinacion);
    }

    /**
     * Obtener estadísticas del dashboard para un coordinador
     */
    public static function getDashboardStats($coordinacionId) {
        $db = Database::getInstance();
        
        $stats = [];
        
        // Total de instructores líderes de fichas
        $result = $db->select("
            SELECT COUNT(DISTINCT f.INSTRUCTOR_inst_id_lider) as total 
            FROM FICHA f 
            WHERE f.COORDINACION_coord_id = ?
        ", [$coordinacionId]);
        $stats['instructores'] = $result[0]['total'] ?? 0;
        
        // Total de fichas
        $result = $db->select("
            SELECT COUNT(*) as total 
            FROM FICHA 
            WHERE COORDINACION_coord_id = ?
        ", [$coordinacionId]);
        $stats['fichas'] = $result[0]['total'] ?? 0;
        
        // Total de asignaciones
        $result = $db->select("
            SELECT COUNT(*) as total 
            FROM ASIGNACION a
            INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
            WHERE f.COORDINACION_coord_id = ?
        ", [$coordinacionId]);
        $stats['asignaciones'] = $result[0]['total'] ?? 0;
        
        // Total de ambientes (no filtrado)
        $result = $db->select("SELECT COUNT(DISTINCT amb_id) as total FROM AMBIENTE");
        $stats['ambientes'] = $result[0]['total'] ?? 0;
        
        return $stats;
    }

    /**
     * Obtener asignaciones recientes de una coordinación
     */
    public static function getRecentAssignments($coordinacionId, $limit = 5) {
        $db = Database::getInstance();
        
        return $db->select("
            SELECT i.inst_nombres, i.inst_apellidos, c.comp_nombre_corto, 
                   a.FICHA_fich_id, a.asig_fecha_ini
            FROM ASIGNACION a
            INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
            INNER JOIN INSTRUCTOR i ON a.INSTRUCTOR_inst_id = i.inst_id
            INNER JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
            WHERE f.COORDINACION_coord_id = ?
            ORDER BY a.asig_fecha_ini DESC
            LIMIT ?
        ", [$coordinacionId, $limit]);
    }

    /**
     * Obtener disponibilidad de ambientes
     */
    public static function getAmbientesDisponibilidad($limit = 10) {
        $db = Database::getInstance();
        
        $ambientes = $db->select("SELECT amb_id, amb_nombre FROM AMBIENTE LIMIT ?", [$limit]);
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        
        $disponibilidad = [];
        foreach ($ambientes as $ambiente) {
            $result = $db->select("
                SELECT COUNT(*) as total
                FROM DETALLE_ASIGNACION da
                INNER JOIN ASIGNACION a ON da.ASIGNACION_ASIG_ID = a.ASIG_ID
                WHERE a.AMBIENTE_amb_id = ?
                AND da.detasig_fecha = ?
                AND da.detasig_hora_ini <= ?
                AND da.detasig_hora_fin > ?
            ", [$ambiente['amb_id'], $fechaHoy, $horaActual, $horaActual]);
            
            $ocupado = $result[0]['total'] ?? 0;
            
            $disponibilidad[] = [
                'nombre' => $ambiente['amb_nombre'],
                'disponible' => $ocupado == 0
            ];
        }
        
        return $disponibilidad;
    }
}
?>
