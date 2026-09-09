<?php
/**
 * Barangay Management System (BarangayOS)
 * Central Database Connection & PDO Query Helpers
 */

// Database Configuration Defaults
defined('DB_HOST')    or define('DB_HOST', 'localhost');
defined('DB_PORT')    or define('DB_PORT', '3306');
defined('DB_NAME')    or define('DB_NAME', 'barangay_db');
defined('DB_USER')    or define('DB_USER', 'root');
defined('DB_PASS')    or define('DB_PASS', '');
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance
 * @return PDO
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database doesn't exist; offer friendly message
            if ($e->getCode() == 1049) {
                // Unknown database
                http_response_code(503);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error_code' => 'DB_NOT_FOUND',
                    'message' => 'Database "'.DB_NAME.'" does not exist yet. Please run install.php to initialize the database.',
                    'install_url' => 'install.php'
                ]);
                exit;
            }

            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error_code' => 'DB_CONNECTION_ERROR',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    return $pdo;
}

/**
 * Execute parameterized query
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function db_query($sql, $params = []) {
    $db = get_db_connection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all matching records
 * @param string $sql
 * @param array $params
 * @return array
 */
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Fetch a single record
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function db_fetch_one($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}

/**
 * Insert record into table and return lastInsertId
 * @param string $table
 * @param array $data Associative array of column => value
 * @return int Last insert ID
 */
function db_insert($table, $data) {
    $db = get_db_connection();
    $columns = array_keys($data);
    $fields = implode('`, `', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));

    $sql = "INSERT INTO `{$table}` (`{$fields}`) VALUES ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    return (int)$db->lastInsertId();
}

/**
 * Update records in table
 * @param string $table
 * @param array $data Associative array of column => value
 * @param string $whereClause
 * @param array $whereParams
 * @return int Rows affected
 */
function db_update($table, $data, $whereClause, $whereParams = []) {
    $db = get_db_connection();
    $setParts = [];
    $values = [];

    foreach ($data as $column => $value) {
        $setParts[] = "`{$column}` = ?";
        $values[] = $value;
    }

    $setString = implode(', ', $setParts);
    $sql = "UPDATE `{$table}` SET {$setString} WHERE {$whereClause}";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($values, $whereParams));
    return $stmt->rowCount();
}

/**
 * Delete records from table
 * @param string $table
 * @param string $whereClause
 * @param array $whereParams
 * @return int Rows affected
 */
function db_delete($table, $whereClause, $whereParams = []) {
    $sql = "DELETE FROM `{$table}` WHERE {$whereClause}";
    $stmt = db_query($sql, $whereParams);
    return $stmt->rowCount();
}

/**
 * Count records
 * @param string $table
 * @param string $whereClause
 * @param array $whereParams
 * @return int
 */
function db_count($table, $whereClause = '', $whereParams = []) {
    $sql = "SELECT COUNT(*) AS total FROM `{$table}`";
    if (!empty($whereClause)) {
        $sql .= " WHERE {$whereClause}";
    }
    $row = db_fetch_one($sql, $whereParams);
    return (int)($row['total'] ?? 0);
}

/**
 * Send standard JSON response and exit
 * @param bool $success
 * @param mixed $data
 * @param string $message
 * @param int $statusCode
 */
function json_response($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => (bool)$success,
        'data'    => $data,
        'message' => $message,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Parse incoming JSON body payload
 * @return array
 */
function get_json_input() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
