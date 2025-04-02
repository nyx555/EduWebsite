<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                error_log("Database connection error: " . $this->connection->connect_error);
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
            error_log("Database connected successfully");
        } catch (Exception $e) {
            error_log("Database constructor error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        try {
            $result = $this->connection->query($sql);
            if ($result === false) {
                error_log("Query error: " . $this->connection->error . "\nSQL: " . $sql);
                throw new Exception("Query failed: " . $this->connection->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Query execution error: " . $e->getMessage());
            throw $e;
        }
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
}
?> 