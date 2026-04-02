<?php
/**
 * SRMS - Result Display Controller
 * Handles result display for single student
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

try {
    /* ===============================
       INPUT
    =============================== */
    $roll_number = $_GET['roll_number'] ?? '';
    $session = $_GET['session'] ?? 'DEC 2024';

    /* ===============================
       DATABASE CONNECTION
    =============================== */
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    /* ===============================
       HELPER FUNCTIONS
    =============================== */

    function gradeToPoint(string $grade): float {
        return match (strtoupper($grade)) {
            'O', 'A+' => 10,
            'A'       => 9,
            'B+'      => 8,
            'B'       => 7,
            'C'       => 6,
            'D'       => 5,
            'F'       => 0,
            default   => 0
        };
    }

    /* ===============================
       UNIVERSITY INFO
    =============================== */
    // Use default university info since universities table might not exist
    $university = [
        'name' => 'Jharkhand Rai University',
        'address' => 'Ranchi, Jharkhand, India',
        'established_text' => 'Established under Jharkhand State Legislature',
        'logo_path' => '/assets/jrulogo.jpg'
    ];

    /* ===============================
       SINGLE STUDENT MODE
    =============================== */
    if (!$roll_number) {
        throw new Exception('Roll number is required.');
    }

    // Get student information with proper joins
    $studentQuery = "
        SELECT s.id, s.roll_no, s.name, s.current_semester, s.father_name, 
               s.mother_name, s.registration_no, s.dob, p.program_name as programme
        FROM students s
        LEFT JOIN programs p ON s.program_id = p.id
        WHERE s.roll_no = ?
    ";

    $stmt = $conn->prepare($studentQuery);
    $stmt->execute([$roll_number]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        throw new Exception('Student not found.');
    }

    // Get examination info for the session
    $examQuery = "
        SELECT e.id, e.exam_type, e.semester, e.academic_year
        FROM examinations e
        WHERE e.academic_year = ? AND e.semester = ?
        ORDER BY e.id DESC
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($examQuery);
    $stmt->execute([$session, $student['current_semester']]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        throw new Exception('Examination not found for the selected session.');
    }

    // Get results with subject information
    $resultQuery = "
        SELECT 
            r.total_marks,
            r.internal_marks,
            r.external_marks,
            r.grade,
            r.grade_point,
            sub.subject_code as course_code,
            sub.subject_name as course_name,
            sub.credits
        FROM results r
        JOIN subjects sub ON r.subject_id = sub.id
        WHERE r.student_id = ? AND r.exam_id = ?
        ORDER BY sub.subject_code
    ";

    $stmt = $conn->prepare($resultQuery);
    $stmt->execute([$student['id'], $exam['id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$courses) {
        throw new Exception('No results found for this student in the selected session.');
    }

    // Calculate grades and grade points if not set
    $total_credits = 0;
    $total_egp = 0;
    $sr_no = 1;
    
    foreach ($courses as &$course) {
        // Calculate grade if not present
        if (empty($course['grade'])) {
            $total_marks = (float)($course['total_marks'] ?? 0);
            $course['grade'] = calculateGrade($total_marks);
        }
        
        // Calculate grade point if not present
        if ($course['grade_point'] === null || $course['grade_point'] === '') {
            $course['grade_point'] = gradeToPoint($course['grade']);
        }
        
        // Add serial number
        $course['sr_no'] = $sr_no++;
        
        // Accumulate for calculations
        $total_credits += (float)$course['credits'];
        $total_egp += (float)$course['grade_point'] * (float)$course['credits'];
    }

    // Calculate SGPA
    $sgpa = $total_credits > 0 ? round($total_egp / $total_credits, 2) : 0;

    // Get cumulative data (all previous semesters)
    $cumulativeQuery = "
        SELECT 
            SUM(sub.credits) as cum_credits,
            SUM(r.grade_point * sub.credits) as cum_egp
        FROM results r
        JOIN subjects sub ON r.subject_id = sub.id
        JOIN examinations e ON r.exam_id = e.id
        WHERE r.student_id = ? AND e.academic_year <= ?
    ";

    $stmt = $conn->prepare($cumulativeQuery);
    $stmt->execute([$student['id'], $session]);
    $cumulative = $stmt->fetch(PDO::FETCH_ASSOC);

    $cumulative_credits = (float)($cumulative['cum_credits'] ?? 0);
    $cumulative_egp = (float)($cumulative['cum_egp'] ?? 0);
    $cgpa = $cumulative_credits > 0 ? round($cumulative_egp / $cumulative_credits, 2) : 0;

    // Format DOB
    $dob = !empty($student['dob']) ? date('d-m-Y', strtotime($student['dob'])) : 'N/A';

    // Prepare student array for view
    $student['roll_number'] = $student['roll_no'];
    $student['semester'] = $student['current_semester'];

    // Prepare semester data for view
    $examTitle = 'END SEMESTER EXAMINATION';
    if ($exam && $exam['exam_type'] === 'END_SEM') {
        $examTitle = 'END SEMESTER EXAMINATION';
    }
    
    // Add session to title if available
    if ($exam && !empty($exam['academic_year'])) {
        $examTitle .= ' - ' . $exam['academic_year'];
    }
    
    $sem = [
        'exam_title' => $examTitle,
        'semester_no' => $exam['semester'] ?? $student['current_semester'],
        'sgpa' => $sgpa,
        'cgpa' => $cgpa,
        'total_credits' => $total_credits,
        'total_egp' => $total_egp,
        'cumulative_credits' => $cumulative_credits,
        'cumulative_egp' => $cumulative_egp
    ];

    // Create results resource for the view
    $resultsRes = new class($courses) {
        private $data;
        private $index = 0;
        
        public function __construct($data) {
            $this->data = $data;
        }
        
        public function fetch_assoc() {
            if ($this->index < count($this->data)) {
                return $this->data[$this->index++];
            }
            return false;
        }
    };

    // Include the view
    include dirname(__DIR__) . '/views/result.php';

} catch (Exception $e) {
    http_response_code(500);
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Error</title>";
    echo "<script src='https://cdn.tailwindcss.com'></script></head>";
    echo "<body class='bg-gray-100 py-10'>";
    echo "<div class='max-w-2xl mx-auto bg-white p-8 rounded-lg shadow'>";
    echo "<h1 class='text-2xl font-bold text-red-600 mb-4'>Result Processing Error</h1>";
    echo "<p class='text-gray-700'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='/results' class='inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700'>Back to Results</a>";
    echo "</div></body></html>";
    exit;
}

function calculateGrade(float $marks): string {
    if ($marks >= 90) return 'O';
    if ($marks >= 80) return 'A+';
    if ($marks >= 70) return 'A';
    if ($marks >= 60) return 'B+';
    if ($marks >= 50) return 'B';
    if ($marks >= 40) return 'C';
    if ($marks >= 30) return 'D';
    return 'F';
}
