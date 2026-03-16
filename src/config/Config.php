<?php
declare(strict_types=1);

/**
 * Configuration Manager for SRMS
 * Environment-based configuration with security best practices
 */

class Config {
    private static array $config = [];
    private static bool $loaded = false;
    
    /**
     * Load configuration from environment variables
     */
    private static function load(): void {
        if (self::$loaded) return;
        
        // Load .env file if it exists
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    $_ENV[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get configuration value
     */
    public static function get(string $key, $default = null) {
        self::load();
        
        // Check environment first
        if (isset($_ENV[$key])) {
            return self::sanitizeValue($_ENV[$key]);
        }
        
        // Check $_SERVER
        if (isset($_SERVER[$key])) {
            return self::sanitizeValue($_SERVER[$key]);
        }
        
        // Default values for production
        $defaults = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'srms_jru',
            'DB_CHARSET' => 'utf8mb4',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'LOG_LEVEL' => 'error',
            'API_RATE_LIMIT' => '100',
            'API_RATE_WINDOW' => '3600',
            'SESSION_LIFETIME' => '7200',
            'SESSION_SECURE' => 'true',
            'SESSION_HTTPONLY' => 'true',
            'MAX_FILE_SIZE' => '10485760'
        ];
        
        return $defaults[$key] ?? $default;
    }
    
    /**
     * Sanitize configuration values
     */
    private static function sanitizeValue($value) {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }
    
    /**
     * Check if in production environment
     */
    public static function isProduction(): bool {
        return self::get('APP_ENV') === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool {
        return self::get('APP_DEBUG') === 'true';
    }
    
    /**
     * Get database configuration
     */
    public static function getDatabaseConfig(): array {
        return [
            'host' => self::get('DB_HOST'),
            'name' => self::get('DB_NAME'),
            'user' => self::get('DB_USER'),
            'password' => self::get('DB_PASS'),
            'charset' => self::get('DB_CHARSET', 'utf8mb4')
        ];
    }
    
    /**
     * Get security configuration
     */
    public static function getSecurityConfig(): array {
        return [
            'jwt_secret' => self::get('JWT_SECRET'),
            'encryption_key' => self::get('ENCRYPTION_KEY'),
            'session_secure' => self::get('SESSION_SECURE') === 'true',
            'session_httponly' => self::get('SESSION_HTTPONLY') === 'true',
            'session_lifetime' => (int)self::get('SESSION_LIFETIME', 7200)
        ];
    }
}
