<?php
require_once __DIR__ . '/../Database.php';

/**
 * Clase base para todos los modelos
 * Proporciona funcionalidad CRUD común
 */
abstract class Model {
    
    /**
     * Nombre de la tabla en la base de datos
     * Debe ser definido por cada modelo hijo
     */
    protected static $table;
    
    /**
     * Nombre de la columna ID (clave primaria)
     * Debe ser definido por cada modelo hijo
     */
    protected static $primaryKey;
    
    /**
     * Columnas de la tabla
     * Debe ser definido por cada modelo hijo
     */
    protected static $columns = [];
    
    /**
     * Obtener todos los registros
     */
    public static function all() {
        $db = Database::getInstance();
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        
        $sql = "SELECT * FROM {$table} ORDER BY {$primaryKey}";
        $result = $db->select($sql);
        
        $items = [];
        foreach ($result as $row) {
            $items[] = static::fromArray($row);
        }
        return $items;
    }
    
    /**
     * Buscar por ID
     */
    public static function find($id) {
        $db = Database::getInstance();
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = ?";
        $result = $db->selectOne($sql, [$id]);
        
        return $result ? static::fromArray($result) : null;
    }
    
    /**
     * Buscar por término (búsqueda en todas las columnas)
     */
    public static function search($term) {
        if (empty($term)) {
            return static::all();
        }
        
        $db = Database::getInstance();
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        $columns = static::$columns;
        
        // Construir condiciones WHERE para cada columna
        $conditions = [];
        $params = [];
        foreach ($columns as $column) {
            $conditions[] = "{$column} LIKE ?";
            $params[] = "%{$term}%";
        }
        
        $whereClause = implode(' OR ', $conditions);
        $sql = "SELECT * FROM {$table} WHERE {$whereClause} ORDER BY {$primaryKey}";
        $result = $db->select($sql, $params);
        
        $items = [];
        foreach ($result as $row) {
            $items[] = static::fromArray($row);
        }
        return $items;
    }
    
    /**
     * Paginación
     */
    public static function paginate($page = 1, $perPage = 10, $searchTerm = '') {
        $items = empty($searchTerm) ? static::all() : static::search($searchTerm);
        
        $total = count($items);
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages ?: 1));
        $offset = ($page - 1) * $perPage;
        
        return [
            'data' => array_slice($items, $offset, $perPage),
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage
        ];
    }
    
    /**
     * Guardar (insertar)
     */
    public static function save($model) {
        $errors = $model->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $db = Database::getInstance();
        $table = static::$table;
        $data = $model->toArray();
        $primaryKey = static::$primaryKey;
        
        // Solo remover el ID si es null o string vacío (auto-incremental)
        // Si tiene valor, se incluye en el INSERT (ID manual)
        if ($data[$primaryKey] === null || $data[$primaryKey] === '') {
            unset($data[$primaryKey]);
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        return $db->insert($sql, array_values($data));
    }
    
    /**
     * Actualizar
     */
    public static function update($model) {
        $errors = $model->validate();
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $db = Database::getInstance();
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        $data = $model->toArray();
        
        $id = $data[$primaryKey];
        unset($data[$primaryKey]);
        
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$primaryKey} = ?";
        $params = array_merge(array_values($data), [$id]);
        
        return $db->update($sql, $params);
    }
    
    /**
     * Eliminar
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $table = static::$table;
        $primaryKey = static::$primaryKey;
        
        $sql = "DELETE FROM {$table} WHERE {$primaryKey} = ?";
        return $db->delete($sql, [$id]);
    }
    
    /**
     * Contar registros
     */
    public static function count() {
        $db = Database::getInstance();
        $table = static::$table;
        
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $result = $db->selectOne($sql);
        
        return (int)$result['count'];
    }
    
    /**
     * Verificar si existe un registro
     */
    public static function exists($id) {
        return static::find($id) !== null;
    }
    
    // ============================================
    // MÉTODOS ABSTRACTOS (deben ser implementados por los hijos)
    // ============================================
    
    /**
     * Crear instancia desde array
     */
    abstract protected static function fromArray($data);
    
    /**
     * Convertir instancia a array
     */
    abstract public function toArray();
    
    /**
     * Validar datos del modelo
     */
    abstract public function validate();
}
?>
