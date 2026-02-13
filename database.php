<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $this->conn = new mysqli(
            Config::db_host,
            Config::db_user,
            Config::db_pass,
            Config::db_name
        );
        
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
}
?>