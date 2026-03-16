<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../utils/Logger.php';

/**
 * API Rate Limiter for SRMS
 * Prevents abuse and ensures fair usage
 */

class RateLimiter {
    private PDO $db;
    private Logger $logger;
    private int $maxRequests;
    private int $windowSeconds;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->logger = Logger::getInstance();
        $this->maxRequests = (int)Config::get('API_RATE_LIMIT', 100);
        $this->windowSeconds = (int)Config::get('API_RATE_WINDOW', 3600);
    }
    
    /**
     * Check if request is allowed
     */
    public function isAllowed(string $ipAddress, string $endpoint): bool {
        try {
            $this->cleanupOldEntries();
            
            // Check current rate limit
            $stmt = $this->db->prepare("
                SELECT request_count, window_start 
                FROM api_rate_limits 
                WHERE ip_address = :ip_address AND endpoint = :endpoint
                AND window_start > DATE_SUB(NOW(), INTERVAL :window SECOND)
                ORDER BY window_start DESC 
                LIMIT 1
            ");
            
            $stmt->bindValue(':ip_address', $ipAddress);
            $stmt->bindValue(':endpoint', $endpoint);
            $stmt->bindValue(':window', $this->windowSeconds, PDO::PARAM_INT);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                // First request in window
                $this->createEntry($ipAddress, $endpoint);
                return true;
            }
            
            $windowStart = new DateTime($record['window_start']);
            $now = new DateTime();
            $secondsSinceStart = $now->getTimestamp() - $windowStart->getTimestamp();
            
            if ($secondsSinceStart >= $this->windowSeconds) {
                // Window expired, create new entry
                $this->createEntry($ipAddress, $endpoint);
                return true;
            }
            
            if ($record['request_count'] >= $this->maxRequests) {
                // Rate limit exceeded
                $this->logger->warning('Rate limit exceeded', [
                    'ip_address' => $ipAddress,
                    'endpoint' => $endpoint,
                    'request_count' => $record['request_count'],
                    'max_requests' => $this->maxRequests
                ]);
                
                return false;
            }
            
            // Increment counter
            $this->incrementCounter($ipAddress, $endpoint);
            return true;
            
        } catch (PDOException $e) {
            $this->logger->error('Rate limiter database error', [
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress,
                'endpoint' => $endpoint
            ]);
            
            // Fail open - allow request if rate limiter fails
            return true;
        }
    }
    
    /**
     * Create new rate limit entry
     */
    private function createEntry(string $ipAddress, string $endpoint): void {
        $stmt = $this->db->prepare("
            INSERT INTO api_rate_limits (ip_address, endpoint, request_count, window_start)
            VALUES (:ip_address, :endpoint, 1, NOW())
            ON DUPLICATE KEY UPDATE 
            request_count = 1,
            window_start = NOW()
        ");
        
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':endpoint', $endpoint);
        $stmt->execute();
    }
    
    /**
     * Increment existing counter
     */
    private function incrementCounter(string $ipAddress, string $endpoint): void {
        $stmt = $this->db->prepare("
            UPDATE api_rate_limits 
            SET request_count = request_count + 1,
                last_request = NOW()
            WHERE ip_address = :ip_address AND endpoint = :endpoint
        ");
        
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':endpoint', $endpoint);
        $stmt->execute();
    }
    
    /**
     * Clean up old entries
     */
    private function cleanupOldEntries(): void {
        $stmt = $this->db->prepare("
            DELETE FROM api_rate_limits 
            WHERE window_start < DATE_SUB(NOW(), INTERVAL :window SECOND)
        ");
        
        $stmt->bindValue(':window', $this->windowSeconds, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * Get remaining requests for client
     */
    public function getRemainingRequests(string $ipAddress, string $endpoint): int {
        try {
            $stmt = $this->db->prepare("
                SELECT request_count, window_start 
                FROM api_rate_limits 
                WHERE ip_address = :ip_address AND endpoint = :endpoint
                AND window_start > DATE_SUB(NOW(), INTERVAL :window SECOND)
                ORDER BY window_start DESC 
                LIMIT 1
            ");
            
            $stmt->bindValue(':ip_address', $ipAddress);
            $stmt->bindValue(':endpoint', $endpoint);
            $stmt->bindValue(':window', $this->windowSeconds, PDO::PARAM_INT);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                return $this->maxRequests;
            }
            
            $windowStart = new DateTime($record['window_start']);
            $now = new DateTime();
            $secondsSinceStart = $now->getTimestamp() - $windowStart->getTimestamp();
            
            if ($secondsSinceStart >= $this->windowSeconds) {
                return $this->maxRequests;
            }
            
            return max(0, $this->maxRequests - $record['request_count']);
            
        } catch (PDOException $e) {
            $this->logger->error('Rate limiter get remaining requests error', [
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress,
                'endpoint' => $endpoint
            ]);
            
            return $this->maxRequests;
        }
    }
    
    /**
     * Get reset time for client
     */
    public function getResetTime(string $ipAddress, string $endpoint): ?DateTime {
        try {
            $stmt = $this->db->prepare("
                SELECT window_start 
                FROM api_rate_limits 
                WHERE ip_address = :ip_address AND endpoint = :endpoint
                AND window_start > DATE_SUB(NOW(), INTERVAL :window SECOND)
                ORDER BY window_start DESC 
                LIMIT 1
            ");
            
            $stmt->bindValue(':ip_address', $ipAddress);
            $stmt->bindValue(':endpoint', $endpoint);
            $stmt->bindValue(':window', $this->windowSeconds, PDO::PARAM_INT);
            $stmt->execute();
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                return null;
            }
            
            $windowStart = new DateTime($record['window_start']);
            $resetTime = clone $windowStart;
            $resetTime->add(new DateInterval("PT{$this->windowSeconds}S"));
            
            return $resetTime;
            
        } catch (PDOException $e) {
            $this->logger->error('Rate limiter get reset time error', [
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress,
                'endpoint' => $endpoint
            ]);
            
            return null;
        }
    }
    
    /**
     * Send rate limit headers
     */
    public function sendHeaders(string $ipAddress, string $endpoint): void {
        $remaining = $this->getRemainingRequests($ipAddress, $endpoint);
        $resetTime = $this->getResetTime($ipAddress, $endpoint);
        
        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . $remaining);
        
        if ($resetTime) {
            header('X-RateLimit-Reset: ' . $resetTime->getTimestamp());
        }
    }
}
