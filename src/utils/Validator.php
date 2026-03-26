<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/Logger.php';

/**
 * Input Validation and Sanitization for SRMS
 * Comprehensive validation with security best practices
 */

class Validator {
    private Logger $logger;
    private array $errors = [];
    
    public function __construct() {
        $this->logger = Logger::getInstance();
    }
    
    /**
     * Validate and sanitize roll number
     */
    public function validateRollNumber(string $rollNumber): ?string {
        if (empty($rollNumber)) {
            $this->addError('Roll number is required');
            return null;
        }
        
        $sanitized = preg_replace('/[^a-zA-Z0-9-]/', '', $rollNumber);
        
        if (strlen($sanitized) < 3 || strlen($sanitized) > 20) {
            $this->addError('Roll number must be between 3 and 20 characters');
            return null;
        }
        
        if ($sanitized !== $rollNumber) {
            $this->logger->warning('Roll number sanitized', [
                'original' => $rollNumber,
                'sanitized' => $sanitized,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli'
            ]);
        }
        
        return strtoupper($sanitized);
    }
    
    /**
     * Validate and sanitize name
     */
    public function validateName(string $name): ?string {
        if (empty($name)) {
            $this->addError('Name is required');
            return null;
        }
        
        $sanitized = trim(preg_replace('/[^a-zA-Z\s.\-]/', '', $name));
        
        if (strlen($sanitized) < 2 || strlen($sanitized) > 100) {
            $this->addError('Name must be between 2 and 100 characters');
            return null;
        }
        
        if (!preg_match('/^[a-zA-Z\s.\-]+$/', $sanitized)) {
            $this->addError('Name contains invalid characters');
            return null;
        }
        
        return ucwords(strtolower($sanitized));
    }
    
    /**
     * Validate email
     */
    public function validateEmail(string $email): ?string {
        if (empty($email)) {
            return null; // Email is optional
        }
        
        $sanitized = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            $this->addError('Invalid email format');
            return null;
        }
        
        if (strlen($sanitized) > 100) {
            $this->addError('Email is too long');
            return null;
        }
        
        return strtolower($sanitized);
    }
    
    /**
     * Validate phone number
     */
    public function validatePhone(string $phone): ?string {
        if (empty($phone)) {
            return null; // Phone is optional
        }
        
        $sanitized = preg_replace('/[^0-9+]/', '', $phone);
        
        if (!preg_match('/^\+?[0-9]{10,15}$/', $sanitized)) {
            $this->addError('Invalid phone number format');
            return null;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate course
     */
    public function validateCourse(string $course): ?string {
        if (empty($course)) {
            $this->addError('Course is required');
            return null;
        }
        
        $validCourses = ['BCA', 'BBA', 'BCOM', 'BSC', 'BA', 'MCA', 'MBA', 'MCOM', 'MSC', 'MA'];
        $sanitized = strtoupper(trim(preg_replace('/[^a-zA-Z]/', '', $course)));
        
        if (!in_array($sanitized, $validCourses)) {
            $this->addError('Invalid course');
            return null;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate semester
     */
    public function validateSemester($semester): ?int {
        if ($semester === null || $semester === '') {
            $this->addError('Semester is required');
            return null;
        }
        
        $sanitized = filter_var($semester, FILTER_SANITIZE_NUMBER_INT);
        $semesterInt = (int)$sanitized;
        
        if ($semesterInt < 1 || $semesterInt > 10) {
            $this->addError('Semester must be between 1 and 10');
            return null;
        }
        
        return $semesterInt;
    }
    
    /**
     * Validate session
     */
    public function validateSession(string $session): ?string {
        if (empty($session)) {
            return null; // Session is optional
        }
        
        $sanitized = trim(preg_replace('/[^a-zA-Z0-9-]/', '', $session));
        
        if (!preg_match('/^[A-Z0-9-]+$/i', $sanitized)) {
            $this->addError('Invalid session format');
            return null;
        }
        
        if (strlen($sanitized) > 20) {
            $this->addError('Session is too long');
            return null;
        }
        
        return strtoupper($sanitized);
    }
    
    /**
     * Validate marks (internal/external)
     */
    public function validateMarks($marks): ?float {
        if ($marks === null || $marks === '') {
            $this->addError('Marks are required');
            return null;
        }
        
        $sanitized = filter_var($marks, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $marksFloat = (float)$sanitized;
        
        if ($marksFloat < 0 || $marksFloat > 100) {
            $this->addError('Marks must be between 0 and 100');
            return null;
        }
        
        return round($marksFloat, 2);
    }
    
    /**
     * Validate subject code
     */
    public function validateSubjectCode(string $subjectCode): ?string {
        if (empty($subjectCode)) {
            $this->addError('Subject code is required');
            return null;
        }
        
        $sanitized = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $subjectCode)));
        
        if (strlen($sanitized) < 3 || strlen($sanitized) > 20) {
            $this->addError('Subject code must be between 3 and 20 characters');
            return null;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate year
     */
    public function validateYear($year): ?int {
        if ($year === null || $year === '') {
            $this->addError('Year is required');
            return null;
        }
        
        $sanitized = filter_var($year, FILTER_SANITIZE_NUMBER_INT);
        $yearInt = (int)$sanitized;
        
        $currentYear = (int)date('Y');
        $minYear = $currentYear - 10;
        $maxYear = $currentYear + 2;
        
        if ($yearInt < $minYear || $yearInt > $maxYear) {
            $this->addError("Year must be between $minYear and $maxYear");
            return null;
        }
        
        return $yearInt;
    }
    
    /**
     * Validate batch year
     */
    public function validateBatchYear($year): ?int {
        if ($year === null || $year === '') {
            $this->addError('Batch year is required');
            return null;
        }
        
        $sanitized = filter_var($year, FILTER_SANITIZE_NUMBER_INT);
        $yearInt = (int)$sanitized;
        
        $currentYear = (int)date('Y');
        $minYear = $currentYear - 10;
        $maxYear = $currentYear;
        
        if ($yearInt < $minYear || $yearInt > $maxYear) {
            $this->addError("Batch year must be between $minYear and $maxYear");
            return null;
        }
        
        return $yearInt;
    }
    
    /**
     * Validate JSON input
     */
    public function validateJson(string $json): ?array {
        if (empty($json)) {
            $this->addError('JSON input is required');
            return null;
        }
        
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('Invalid JSON format: ' . json_last_error_msg());
            return null;
        }
        
        if (!is_array($decoded)) {
            $this->addError('JSON must be an object');
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Validate against SQL injection attempts
     */
    public function validateSqlInjection(string $input): bool {
        $suspiciousPatterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE)\b/i',
            '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/\b(OR|AND)\s+["\']?\w+["\']?\s*=\s*["\']?\w+["\']?/i',
            '/--/',
            '/\/\*/',
            '/\*\/',
            '/;/',
            '/\bxp_cmdshell\b/i',
            '/\bsp_executesql\b/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logger->warning('Potential SQL injection attempt detected', [
                    'input' => $input,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli'
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate XSS attempts
     */
    public function validateXss(string $input): bool {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<.*?on\w+.*?=.*?>/i',
            '/<.*?javascript:.*?>/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logger->warning('Potential XSS attempt detected', [
                    'input' => $input,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli'
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Comprehensive validation for student data
     */
    public function validateStudentData(array $data): ?array {
        $validated = [];
        
        if (isset($data['roll_number'])) {
            $validated['roll_number'] = $this->validateRollNumber($data['roll_number']);
        }
        
        if (isset($data['name'])) {
            $validated['name'] = $this->validateName($data['name']);
        }
        
        if (isset($data['email'])) {
            $validated['email'] = $this->validateEmail($data['email']);
        }
        
        if (isset($data['phone'])) {
            $validated['phone'] = $this->validatePhone($data['phone']);
        }
        
        if (isset($data['course'])) {
            $validated['course'] = $this->validateCourse($data['course']);
        }
        
        if (isset($data['semester'])) {
            $validated['semester'] = $this->validateSemester($data['semester']);
        }
        
        if (isset($data['batch_year'])) {
            $validated['batch_year'] = $this->validateBatchYear($data['batch_year']);
        }
        
        // Check for null values (validation failed)
        foreach ($validated as $key => $value) {
            if ($value === null && isset($data[$key]) && $data[$key] !== '') {
                return null;
            }
        }
        
        return $validated;
    }
    
    /**
     * Comprehensive validation for result data
     */
    public function validateResultData(array $data): ?array {
        $validated = [];
        
        if (isset($data['roll_number'])) {
            $validated['roll_number'] = $this->validateRollNumber($data['roll_number']);
        }
        
        if (isset($data['subject_code'])) {
            $validated['subject_code'] = $this->validateSubjectCode($data['subject_code']);
        }
        
        if (isset($data['exam_id'])) {
            $validated['exam_id'] = $this->validateYear($data['exam_id']);
        }
        
        if (isset($data['internal_marks'])) {
            $validated['internal_marks'] = $this->validateMarks($data['internal_marks']);
        }
        
        if (isset($data['external_marks'])) {
            $validated['external_marks'] = $this->validateMarks($data['external_marks']);
        }
        
        // Check for null values (validation failed)
        foreach ($validated as $key => $value) {
            if ($value === null && isset($data[$key]) && $data[$key] !== '') {
                return null;
            }
        }
        
        return $validated;
    }
    
    /**
     * Add validation error
     */
    private function addError(string $error): void {
        $this->errors[] = $error;
    }
    
    /**
     * Get all validation errors
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     */
    public function isValid(): bool {
        return empty($this->errors);
    }
    
    /**
     * Clear all errors
     */
    public function clearErrors(): void {
        $this->errors = [];
    }
    
    /**
     * Basic sanitization method for admin panel
     */
    public function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
