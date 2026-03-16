<?php
declare(strict_types=1);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/../utils/Logger.php';

/**
 * Database Configuration for SRMS
 * Jharkhand Rai University Student Result Management System
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    
    public $conn;
    private Logger $logger;
    
    public function __construct() {
        $config = Config::getDatabaseConfig();
        $this->host = $config['host'];
        $this->db_name = $config['name'];
        $this->username = $config['user'];
        $this->password = $config['password'];
        $this->charset = $config['charset'];
        $this->logger = Logger::getInstance();
    }
    
    public function getConnection(): ?PDO {
        $this->conn = null;
        
        try {
            $startTime = microtime(true);
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_PERSISTENT, true); // Connection pooling
            
            $executionTime = microtime(true) - $startTime;
            $this->logger->logQuery('Database connection established', [], $executionTime);
            
        } catch(PDOException $exception) {
            $this->logger->critical('Database connection failed', [
                'host' => $this->host,
                'database' => $this->db_name,
                'error' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]);
            
            if (Config::isDebug()) {
                error_log("Database Connection Error: " . $exception->getMessage());
            }
            
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
