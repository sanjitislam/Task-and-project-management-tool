<?php
/**
 * Database Class (Singleton Pattern)
 * --------------------------------------
 * Provides ONE shared mysqli connection for the entire app.
 * All Models will use this class to run prepared statements.
 *
 * Why "Singleton"?
 *   We only want ONE connection open at a time (saves memory & is faster).
 *   The first call creates it; every other call reuses the same one.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class Database
{
    // Holds the single instance (the "Singleton")
    private static $instance = null;

    // The actual mysqli connection object
    private $conn;

    /**
     * Private constructor — nobody outside can do "new Database()".
     * They MUST go through getInstance(). This enforces ONE connection.
     */
    private function __construct()
    {
        // Load credentials from config
        $config = require __DIR__ . '/../config/database.php';

        // Enable mysqli to throw exceptions on errors (easier debugging)
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database']
            );

            // Set charset to avoid encoding issues (emoji, foreign letters, etc.)
            $this->conn->set_charset($config['charset']);

        } catch (mysqli_sql_exception $e) {
            // Show a clean message instead of leaking credentials
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * The ONLY way to get a Database instance.
     * First call creates it; later calls return the same one.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Returns the raw mysqli connection.
     * Models will use this to prepare statements.
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Helper: run a prepared SELECT and return all rows as an array.
     *
     * @param string $sql    SQL with ? placeholders
     * @param string $types  e.g., "ssi" (s=string, i=int, d=double, b=blob)
     * @param array  $params Values to bind
     * @return array         Array of associative arrays (rows)
     */
    public function select($sql, $types = '', $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('SQL prepare failed: ' . $this->conn->error);
        }

        if ($types !== '' && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Helper: run a prepared INSERT/UPDATE/DELETE.
     *
     * @return int  Number of affected rows (or new insert ID for INSERTs)
     */
    public function execute($sql, $types = '', $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('SQL prepare failed: ' . $this->conn->error);
        }

        if ($types !== '' && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        // For INSERTs, return the new ID; otherwise affected rows
        $result = ($stmt->insert_id > 0) ? $stmt->insert_id : $stmt->affected_rows;
        $stmt->close();

        return $result;
    }

    /**
     * Helper: fetch a single row (for things like "find user by id").
     */
    public function selectOne($sql, $types = '', $params = [])
    {
        $rows = $this->select($sql, $types, $params);
        return $rows[0] ?? null;   // return first row or null
    }

    /**
     * Prevent cloning (Singleton rule).
     */
    private function __clone() {}
}


?>