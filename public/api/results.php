<?php
declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Change to SRMS root directory
$rootDir = realpath(__DIR__ . '/../../..');
if (strpos($rootDir, 'SRMS') !== false) {
    chdir($rootDir);
} else {
    // Try to find SRMS directory
    $srmsDir = $rootDir . '/SRMS';
    if (is_dir($srmsDir)) {
        chdir($srmsDir);
    }
}

require_once 'src/config/database.php';

class ResultsAPI {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if (!$this->db) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }
    
    public function getResults(): void {
        $roll_number = $_GET['roll_number'] ?? '';
        $session = $_GET['session'] ?? '';
        $semester = $_GET['semester'] ?? '';
        
        if (empty($roll_number)) {
            http_response_code(400);
            echo json_encode(['error' => 'Roll number is required']);
            return;
        }
        
        try {
            // Get student information
            $student_query = "SELECT s.*, e.exam_name, e.session, e.year as exam_year, e.id as exam_id
                             FROM students s 
                             JOIN examinations e ON s.semester = e.semester AND s.course = e.course";
            
            $params = [];
            $conditions = ["s.roll_number = :roll_number"];
            $params[':roll_number'] = $roll_number;
            
            if (!empty($session)) {
                $conditions[] = "e.session = :session";
                $params[':session'] = $session;
            }
            
            if (!empty($semester)) {
                $conditions[] = "s.semester = :semester";
                $params[':semester'] = (int)$semester;
            }
            
            $student_query .= " WHERE " . implode(" AND ", $conditions);
            $student_query .= " ORDER BY e.year DESC LIMIT 1";
            
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
            
            // Calculate grades
            foreach ($results as &$result) {
                $result['grade'] = $this->calculateGrade((float)$result['total_marks']);
            }
            
            // Calculate overall statistics
            $total_marks = array_sum(array_column($results, 'total_marks'));
            $max_marks = count($results) * 100; // Assuming max 100 marks per subject
            $percentage = $max_marks > 0 ? round(($total_marks / $max_marks) * 100, 2) : 0;
            $total_credits = array_sum(array_column($results, 'credits'));
            
            $response = [
                'student' => [
                    'roll_number' => $student['roll_number'],
                    'name' => $student['name'],
                    'course' => $student['course'],
                    'semester' => $student['semester'],
                    'exam_name' => $student['exam_name'],
                    'session' => $student['session'],
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
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }
        
        $required_fields = ['roll_number', 'subject_code', 'exam_id', 'internal_marks', 'external_marks'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                return;
            }
        }
        
        try {
            // Get student ID
            $student_stmt = $this->db->prepare("SELECT id FROM students WHERE roll_number = :roll_number");
            $student_stmt->bindValue(':roll_number', $data['roll_number']);
            $student_stmt->execute();
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                http_response_code(404);
                echo json_encode(['error' => 'Student not found']);
                return;
            }
            
            // Get subject ID
            $subject_stmt = $this->db->prepare("SELECT id FROM subjects WHERE subject_code = :subject_code");
            $subject_stmt->bindValue(':subject_code', $data['subject_code']);
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
            $stmt->bindValue(':exam_id', $data['exam_id']);
            $stmt->bindValue(':internal_marks', $data['internal_marks']);
            $stmt->bindValue(':external_marks', $data['external_marks']);
            
            $total = $data['internal_marks'] + $data['external_marks'];
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
$api = new ResultsAPI();

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
