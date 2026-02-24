<?php
declare(strict_types=1);

/**
 * Database Configuration for SRMS
 * Jharkhand Rai University Student Result Management System
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'srms_jru';
    private $username = 'root';
    private $password = 'Ajay@1906';
    private $charset = 'utf8mb4';
    
    public $conn;
    
    public function getConnection(): ?PDO {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
    
    public function createDatabase(): bool {
        try {
            $conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $conn->exec($sql);
            
            return true;
        } catch(PDOException $exception) {
            error_log("Database Creation Error: " . $exception->getMessage());
            return false;
        }
    }
}
