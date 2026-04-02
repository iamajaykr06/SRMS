<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/middleware/RateLimiter.php';
require_once __DIR__ . '/../../src/utils/Validator.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Initialize rate limiter
$database = new Database();
$db = $database->getConnection();
$rateLimiter = new RateLimiter($db);

// Get client IP and endpoint
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$endpoint = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];

// Check rate limit
if (!$rateLimiter->isAllowed($clientIp, $endpoint)) {
    http_response_code(429);
    header('Retry-After: ' . Config::get('API_RATE_WINDOW', 3600));
    echo json_encode([
        'error' => 'Rate limit exceeded',
        'message' => 'Too many requests. Please try again later.'
    ]);
    exit;
}

// Send rate limit headers
$rateLimiter->sendHeaders($clientIp, $endpoint);

class ResultsAPI {
    private $db;
    private $validator;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->validator = new Validator();
    }
    
    public function getResults(): void {
        $roll_number = $this->validator->validateRollNumber($_GET['roll_number'] ?? '');
        $session = $this->validator->validateSession($_GET['session'] ?? '');
        $semester = $this->validator->validateSemester($_GET['semester'] ?? '');
        
        if (!$roll_number) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing roll number', 'details' => $this->validator->getErrors()]);
            return;
        }
        
        if (!$this->validator->isValid()) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input parameters', 'details' => $this->validator->getErrors()]);
            return;
        }
        
        try {
            // Get student information
            $student_query = "SELECT s.*, e.exam_type, e.academic_year, e.exam_year, e.id as exam_id
                             FROM students s 
                             JOIN examinations e ON s.current_semester = e.semester AND s.program_id = e.program_id";
            
            $params = [];
            $conditions = ["s.roll_no = :roll_number"];
            $params[':roll_number'] = $roll_number;
            
            if (!empty($session)) {
                $conditions[] = "e.academic_year = :session";
                $params[':session'] = $session;
            }
            
            if (!empty($semester)) {
                $conditions[] = "s.current_semester = :semester";
                $params[':semester'] = (int)$semester;
            }
            
            $student_query .= " WHERE " . implode(" AND ", $conditions);
            $student_query .= " ORDER BY e.exam_year DESC LIMIT 1";
            
            $student_stmt = $this->db->prepare($student_query);
            foreach ($params as $key => $value) {
                $student_stmt->bindValue($key, $value);
            }
            $student_stmt->execute();
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                http_response_code(404);
                echo json_encode(['error' => 'Student not found or no results available']);
                return;
            }
            
            // Get results for the student
            $results_query = "SELECT r.*, sub.subject_code, sub.subject_name, sub.credits 
                             FROM results r 
                             JOIN subjects sub ON r.subject_id = sub.id 
                             WHERE r.student_id = :student_id AND r.exam_id = :exam_id";
            
            $results_stmt = $this->db->prepare($results_query);
            $results_stmt->bindValue(':student_id', $student['id']);
            $results_stmt->bindValue(':exam_id', $student['exam_id']);
            $results_stmt->execute();
            $results = $results_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate grades (grades are already stored in database)
            foreach ($results as &$result) {
                if (!isset($result['grade'])) {
                    $result['grade'] = $this->calculateGrade((float)$result['total_marks']);
                }
            }
            
            // Calculate overall statistics
            $total_marks = array_sum(array_column($results, 'total_marks'));
            $max_marks = count($results) * 100; // Assuming max 100 marks per subject
            $percentage = $max_marks > 0 ? round(($total_marks / $max_marks) * 100, 2) : 0;
            $total_credits = array_sum(array_column($results, 'credits'));
            
            $response = [
                'student' => [
                    'roll_number' => $student['roll_no'],
                    'name' => $student['name'],
                    'course' => 'BCA', // Default since course field doesn't exist
                    'semester' => $student['current_semester'],
                    'exam_name' => $student['exam_type'],
                    'session' => $student['academic_year'],
                    'year' => $student['exam_year']
                ],
                'results' => $results,
                'summary' => [
                    'total_marks' => $total_marks,
                    'max_marks' => $max_marks,
                    'percentage' => $percentage,
                    'total_credits' => $total_credits,
                    'status' => $percentage >= 40 ? 'PASS' : 'FAIL'
                ]
            ];
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            error_log("Error fetching results: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch results']);
        }
    }
    
    public function addResult(): void {
        $input = file_get_contents('php://input');
        $data = $this->validator->validateJson($input);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data', 'details' => $this->validator->getErrors()]);
            return;
        }
        
        $validatedData = $this->validator->validateResultData($data);
        
        if (!$validatedData) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data', 'details' => $this->validator->getErrors()]);
            return;
        }
        
        $required_fields = ['roll_number', 'subject_code', 'exam_id', 'internal_marks', 'external_marks'];
        foreach ($required_fields as $field) {
            if (!isset($validatedData[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                return;
            }
        }
        
        try {
            // Get student ID
            $student_stmt = $this->db->prepare("SELECT id FROM students WHERE roll_no = :roll_number");
            $student_stmt->bindValue(':roll_number', $validatedData['roll_number']);
            $student_stmt->execute();
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                http_response_code(404);
                echo json_encode(['error' => 'Student not found']);
                return;
            }
            
            // Get subject ID
            $subject_stmt = $this->db->prepare("SELECT id FROM subjects WHERE subject_code = :subject_code");
            $subject_stmt->bindValue(':subject_code', $validatedData['subject_code']);
            $subject_stmt->execute();
            $subject = $subject_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subject) {
                http_response_code(404);
                echo json_encode(['error' => 'Subject not found']);
                return;
            }
            
            // Insert or update result
            $query = "INSERT INTO results (student_id, subject_id, exam_id, internal_marks, external_marks, status) 
                     VALUES (:student_id, :subject_id, :exam_id, :internal_marks, :external_marks, :status)
                     ON DUPLICATE KEY UPDATE 
                     internal_marks = VALUES(internal_marks),
                     external_marks = VALUES(external_marks),
                     status = VALUES(status),
                     updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':student_id', $student['id']);
            $stmt->bindValue(':subject_id', $subject['id']);
            $stmt->bindValue(':exam_id', $validatedData['exam_id']);
            $stmt->bindValue(':internal_marks', $validatedData['internal_marks']);
            $stmt->bindValue(':external_marks', $validatedData['external_marks']);
            
            $total = $validatedData['internal_marks'] + $validatedData['external_marks'];
            $status = ($total >= 40) ? 'pass' : 'fail';
            $stmt->bindValue(':status', $status);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Result added/updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add result']);
            }
            
        } catch (PDOException $e) {
            error_log("Error adding result: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add result']);
        }
    }
    
    private function calculateGrade(float $marks): string {
        if ($marks >= 90) return 'O';
        if ($marks >= 80) return 'A+';
        if ($marks >= 70) return 'A';
        if ($marks >= 60) return 'B+';
        if ($marks >= 50) return 'B';
        if ($marks >= 40) return 'C';
        return 'F';
    }
}

// Handle requests
$api = new ResultsAPI($db);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $api->getResults();
        break;
    case 'POST':
        $api->addResult();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
