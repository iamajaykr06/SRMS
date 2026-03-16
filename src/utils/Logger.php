<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Config.php';

/**
 * Production-ready Logger for SRMS
 * Structured logging with different levels and rotation
 */

class Logger {
    private static ?Logger $instance = null;
    private string $logPath;
    private string $logLevel;
    private array $logLevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    private function __construct() {
        $this->logPath = Config::get('LOG_PATH', __DIR__ . '/../../logs/');
        $this->logLevel = strtoupper(Config::get('LOG_LEVEL', 'ERROR'));
        
        // Create log directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public static function getInstance(): Logger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log message with specified level
     */
    public function log(string $level, string $message, array $context = []): void {
        $level = strtoupper($level);
        
        if (!isset($this->logLevels[$level])) {
            $level = 'INFO';
        }
        
        if ($this->logLevels[$level] < $this->logLevels[$this->logLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logFile = $this->logPath . 'srms_' . date('Y-m-d') . '.log';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli'
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Rotate log file if it's too large (>10MB)
        if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            $this->rotateLog($logFile);
        }
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Debug level logging
     */
    public function debug(string $message, array $context = []): void {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Info level logging
     */
    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Warning level logging
     */
    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Error level logging
     */
    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Critical level logging
     */
    public function critical(string $message, array $context = []): void {
        $this->log('CRITICAL', $message, $context);
        
        // Send email notification for critical errors in production
        if (Config::isProduction()) {
            $this->sendCriticalAlert($message, $context);
        }
    }
    
    /**
     * Rotate log file
     */
    private function rotateLog(string $logFile): void {
        $backupFile = str_replace('.log', '_' . time() . '.log', $logFile);
        rename($logFile, $backupFile);
        
        // Keep only last 7 log files
        $pattern = str_replace('.log', '_*.log', $logFile);
        $files = glob($pattern);
        if (count($files) > 7) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $filesToDelete = array_slice($files, 7);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * Send critical error alert
     */
    private function sendCriticalAlert(string $message, array $context): void {
        $to = Config::get('ADMIN_EMAIL');
        if (!$to) return;
        
        $subject = '[SRMS CRITICAL] ' . substr($message, 0, 50);
        $body = "Critical error occurred in SRMS:\n\n";
        $body .= "Message: " . $message . "\n";
        $body .= "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        $body .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'cli') . "\n";
        
        $headers = [
            'From: noreply@' . parse_url(Config::get('APP_URL', ''), PHP_URL_HOST),
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        @mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    /**
     * Log database query
     */
    public function logQuery(string $query, array $params = [], float $executionTime = 0): void {
        $context = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime
        ];
        
        if ($executionTime > 1.0) {
            $this->warning('Slow query detected', $context);
        } else {
            $this->debug('Database query', $context);
        }
    }
    
    /**
     * Log API request
     */
    public function logApiRequest(string $method, string $endpoint, int $statusCode, float $responseTime): void {
        $context = [
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response_time' => $responseTime
        ];
        
        if ($statusCode >= 400) {
            $this->error('API request failed', $context);
        } else {
            $this->info('API request', $context);
        }
    }
}
