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
    $session = $_GET['session'] ?? 'END SEM DEC 2024';

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

    function gradeToPoint(string $grade): int {
        return match ($grade) {
            'O', 'A+' => 10,
            'A'       => 9,
            'B+'      => 8,
            'B'       => 7,
            'C'       => 6,
            'F'       => 0,
            default   => 0
        };
    }

    function calculateSummary(array $courses): array {
        $total_marks = 0;
        $total_credits = 0;
        $total_points = 0;

        foreach ($courses as $course) {
            $total_marks += $course['total_marks'];
            $total_credits += $course['credits'];
            $total_points += $course['grade_point'];
        }

        if ($total_credits <= 0) {
            throw new Exception('Invalid credit calculation.');
        }

        return [
            'sgpa'          => round($total_points / $total_credits, 2),
            'cgpa'          => round($total_points / $total_credits, 2),
            'total_credits' => $total_credits,
            'egp'           => $total_points,
            'cum_credits'   => $total_credits,
            'cum_egp'       => $total_points
        ];
    }

    /* ===============================
       UNIVERSITY INFO
    =============================== */
    $uniStmt = $conn->query("SELECT * FROM universities LIMIT 1");
    $university = $uniStmt->fetch(PDO::FETCH_ASSOC);

    if (!$university) {
        throw new Exception('University data not found.');
    }

    /* ===============================
       SINGLE STUDENT MODE
    =============================== */
    if (!$roll_number) {
        throw new Exception('Roll number is required.');
    }

    $studentQuery = "
        SELECT s.id, s.roll_no, s.name, s.current_semester, s.father_name, 
               s.mother_name, s.registration_no, s.dob, p.program_name
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

    $resultQuery = "
        SELECT r.total_marks, r.grade, r.grade_point, 
               sub.subject_code, sub.subject_name, sub.credits
        FROM results r
        JOIN subjects sub ON r.subject_id = sub.id
        JOIN examinations e ON r.exam_id = e.id
        WHERE r.student_id = ? AND e.academic_year = ?
        ORDER BY sub.subject_code
    ";

    $stmt = $conn->prepare($resultQuery);
    $stmt->execute([$student['id'], $session]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$courses) {
        throw new Exception('No results found for this student in the selected session.');
    }

    // Ensure grade_point is set
    foreach ($courses as &$course) {
        $course['grade_point'] = $course['grade_point'] ?? gradeToPoint($course['grade']);
    }

    $summary = calculateSummary($courses);

    // Prepare student array for view
    $student['roll_number'] = $student['roll_no'];
    $student['semester'] = $student['current_semester'];

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
