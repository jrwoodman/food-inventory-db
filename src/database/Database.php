<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // Create database directory if it doesn't exist
            $db_dir = dirname(__DIR__ . '/../database/food_inventory.db');
            if (!is_dir($db_dir)) {
                mkdir($db_dir, 0755, true);
            }
            
            $this->conn = new PDO(
                "sqlite:" . __DIR__ . "/../../database/food_inventory.db"
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec('PRAGMA foreign_keys = ON;');
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function initializeDatabase() {
        try {
            $conn = $this->getConnection();
            
            // Read and execute schema file
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            $conn->exec($schema);
            
            return true;
        } catch(PDOException $exception) {
            echo "Database initialization error: " . $exception->getMessage();
            return false;
        }
    }
}
?>