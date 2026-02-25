<?php
require_once '../src/config/database.php';

// Get URL parameters
$roll_number = $_GET['roll_number'] ?? '';
$session = $_GET['session'] ?? '';
$semester = $_GET['semester'] ?? '';

// Initialize variables
$university = [];
$student = [];
$courses = [];
$summary = [];

// Connect to database
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    // Get university info
    $university_query = "SELECT * FROM universities LIMIT 1";
    $university = $conn->query($university_query)->fetch(PDO::FETCH_ASSOC);
    
    // Check if showing all results for DEC 2025
    if ($roll_number == '' && $session == 'END SEM DEC 2025') {
        // Get all students for DEC 2025
        $all_students_query = "
            SELECT s.*, p.program_name 
            FROM students s 
            JOIN programs p ON s.program_id = p.id 
            JOIN examinations e ON s.current_semester = e.semester 
            WHERE e.session = 'END SEM DEC 2025'
            ORDER BY s.roll_no
        ";
        $all_students = $conn->query($all_students_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all results for these students
        $all_results_query = "
            SELECT r.*, sub.subject_code, sub.subject_name, sub.credits, s.roll_no, st.name as student_name
            FROM results r 
            JOIN subjects sub ON r.subject_id = sub.id 
            JOIN examinations e ON r.exam_id = e.id 
            JOIN students st ON r.student_id = st.id 
            JOIN programs p ON st.program_id = p.id 
            WHERE e.session = 'END SEM DEC 2025'
            ORDER BY st.roll_no, sub.subject_code
        ";
        $all_results = $conn->query($all_results_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Group results by student
        $students_results = [];
        foreach ($all_results as $result) {
            $roll_no = $result['roll_no'];
            if (!isset($students_results[$roll_no])) {
                $students_results[$roll_no] = [
                    'student_info' => [
                        'roll_no' => $result['roll_no'],
                        'name' => $result['student_name'],
                        'program_name' => $result['program_name']
                    ],
                    'courses' => []
                ];
            }
            $students_results[$roll_no]['courses'][] = $result;
        }
        
        // Calculate summary for each student
        foreach ($students_results as $roll_no => &$student_data) {
            $total_marks = 0;
            $total_credits = 0;
            $total_points = 0;
            
            foreach ($student_data['courses'] as $course) {
                $total_marks += $course['total_marks'];
                $total_credits += $course['credits'];
                
                // Calculate grade points
                $grade_points = 0;
                switch ($course['grade']) {
                    case 'O': $grade_points = 10; break;
                    case 'A+': $grade_points = 10; break;
                    case 'A': $grade_points = 9; break;
                    case 'B+': $grade_points = 8; break;
                    case 'B': $grade_points = 7; break;
                    case 'C': $grade_points = 6; break;
                    case 'F': $grade_points = 0; break;
                }
                $total_points += $grade_points;
            }
            
            $students_results[$roll_no]['summary'] = [
                'total_marks' => $total_marks,
                'max_marks' => count($student_data['courses']) * 100,
                'percentage' => round(($total_marks / (count($student_data['courses']) * 100)) * 100, 2),
                'sgpa' => round($total_points / $total_credits, 2),
                'cgpa' => round($total_points / $total_credits, 2),
                'total_credits' => $total_credits,
                'total_egp' => $total_points,
                'cumulative_credits' => $total_credits,
                'cumulative_egp' => $total_points
            ];
        }
    }
    
} else {
    // Get individual student result
    if ($roll_number != '') {
        // Get student with their results
        $student_query = "
            SELECT s.*, p.program_name 
            FROM students s 
            JOIN programs p ON s.program_id = p.id 
            WHERE s.roll_no = ?
        ";
        $student_stmt = $conn->prepare($student_query);
        $student_stmt->execute([$roll_number]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            // Get results for this student
            $results_query = "
                SELECT r.*, sub.subject_code, sub.subject_name, sub.credits 
                FROM results r 
                JOIN subjects sub ON r.subject_id = sub.id 
                JOIN examinations e ON r.exam_id = e.id 
                WHERE r.student_id = ? AND e.session = ?
                ORDER BY sub.subject_code
            ";
            $results_stmt = $conn->prepare($results_query);
            $results_stmt->execute([$student['id'], $session]);
            $courses = $results_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate summary
            $total_marks = 0;
            $total_credits = 0;
            $total_points = 0;
            
            foreach ($courses as $course) {
                $total_marks += $course['total_marks'];
                $total_credits += $course['credits'];
                
                // Calculate grade points
                $grade_points = 0;
                switch ($course['grade']) {
                    case 'O': $grade_points = 10; break;
                    case 'A+': $grade_points = 10; break;
                    case 'A': $grade_points = 9; break;
                    case 'B+': $grade_points = 8; break;
                    case 'B': $grade_points = 7; break;
                    case 'C': $grade_points = 6; break;
                    case 'F': $grade_points = 0; break;
                }
                $total_points += $grade_points;
            }
            
            $summary = [
                'total_marks' => $total_marks,
                'max_marks' => count($courses) * 100,
                'percentage' => round(($total_marks / (count($courses) * 100)) * 100, 2),
                'sgpa' => round($total_points / $total_credits, 2),
                'cgpa' => round($total_points / $total_credits, 2),
                'total_credits' => $total_credits,
                'total_egp' => $total_points,
                'cumulative_credits' => $total_credits,
                'cumulative_egp' => $total_points
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/nav.js"></script>
</head>
<body class="bg-gray-100 py-10">

<div class="max-w-5xl mx-auto bg-white border shadow">

    <!-- UNIVERSITY HEADER -->
    <div class="border-b p-6">

        <div class="flex items-center gap-4">
            <img src="<?= $university['logo_path'] ?>" alt="jru-logo" class="h-16 w-16 object-contain">

            <div>
                <h1 class="text-2xl font-bold uppercase">
                    <?= htmlspecialchars($university['name']) ?>
                </h1>
                <p class="text-sm">
                    <?= htmlspecialchars($university['address']) ?>
                </p>
                <p class="text-xs text-gray-600">
                    <?= htmlspecialchars($university['established_text']) ?>
                </p>
            </div>
        </div>

        <h2 class="text-center text-lg font-semibold mt-6 uppercase underline">
            End Semester Examination - DEC 2025
        </h2>
    </div>

    <!-- STUDENT INFO -->
    <div class="grid grid-cols-2 gap-6 p-6 text-sm">

        <div class="space-y-2">
            <p><strong>Enrollment No:</strong> <?= $student['roll_no'] ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
            <p><strong>Father's Name:</strong> <?= htmlspecialchars($student['father_name']) ?></p>
            <p><strong>Mother's Name:</strong> <?= htmlspecialchars($student['mother_name']) ?></p>
            <p><strong>Programme:</strong> <?= htmlspecialchars($student['program_name']) ?></p>
        </div>

        <div class="space-y-2 text-right">
            <p><strong>Registration No:</strong> <?= $student['registration_no'] ?></p>
            <p><strong>Semester:</strong> <?= $student['current_semester'] ?></p>
            <p><strong>Date of Birth:</strong> <?= $student['dob'] ?></p>
        </div>

    </div>

    <!-- COURSE TABLE -->
    <div class="px-6 pb-6">

        <table class="w-full border text-sm">
            <thead class="bg-gray-200">
            <tr>
                <th class="border p-2">SR.NO.</th>
                <th class="border p-2">COURSE CODE</th>
                <th class="border p-2 text-left">COURSE</th>
                <th class="border p-2">CREDITS</th>
                <th class="border p-2">GRADE</th>
                <th class="border p-2">GRADE POINT</th>
            </tr>
            </thead>

            <tbody>
                <?php if ($roll_number == '' && $session == 'END SEM DEC 2025'): ?>
                    <?php foreach ($students_results as $roll_no => &$student_data): ?>
                        <?php foreach ($student_data['courses'] as $index => $course): ?>
                        <tr class="text-center">
                            <td class="border p-2"><?= $index + 1 ?></td>
                            <td class="border p-2"><?= $course['subject_code'] ?></td>
                            <td class="border p-2 text-left"><?= htmlspecialchars($course['subject_name']) ?></td>
                            <td class="border p-2"><?= $course['credits'] ?></td>
                            <td class="border p-2"><?= $course['grade'] ?></td>
                            <td class="border p-2"><?= $course['grade_point'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($courses as $index => $course): ?>
                        <tr class="text-center">
                            <td class="border p-2"><?= $index + 1 ?></td>
                            <td class="border p-2"><?= $course['subject_code'] ?></td>
                            <td class="border p-2 text-left"><?= htmlspecialchars($course['subject_name']) ?></td>
                            <td class="border p-2"><?= $course['credits'] ?></td>
                            <td class="border p-2"><?= $course['grade'] ?></td>
                            <td class="border p-2"><?= $course['grade_point'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- SUMMARY -->
        <div class="mt-6 grid grid-cols-2 text-sm">

            <?php if ($roll_number == '' && $session == 'END SEM DEC 2025'): ?>
                <?php foreach ($students_results as $roll_no => &$student_data): ?>
                    <div>
                        <p><strong>SGPA:</strong> <?= $student_data['summary']['sgpa'] ?></p>
                        <p><strong>CGPA:</strong> <?= $student_data['summary']['cgpa'] ?></p>
                    </div>
                    <div class="text-right">
                        <p><strong>Total Credits & EGP:</strong> <?= $student_data['summary']['total_credits'] ?> / <?= $student_data['summary']['total_egp'] ?></p>
                        <p><strong>Cumulative Credits & EGP:</strong> <?= $student_data['summary']['cumulative_credits'] ?> / <?= $student_data['summary']['cumulative_egp'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div>
                    <p><strong>SGPA:</strong> <?= $summary['sgpa'] ?></p>
                    <p><strong>CGPA:</strong> <?= $summary['cgpa'] ?></p>
                </div>
                <div class="text-right">
                    <p><strong>Total Credits & EGP:</strong> <?= $summary['total_credits'] ?> / <?= $summary['total_egp'] ?></p>
                    <p><strong>Cumulative Credits & EGP:</strong> <?= $summary['cumulative_credits'] ?> / <?= $summary['cumulative_egp'] ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>
</html>
