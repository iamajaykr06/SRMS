<?php
require_once '../src/config/database.php';

// Get URL parameters with defaults
$roll_number = $_GET['roll_number'] ?? '';
$session = $_GET['session'] ?? '2023-24';  // Default to existing session
$semester = $_GET['semester'] ?? '3';     // Default to semester 3

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
            LEFT JOIN programs p ON s.course = p.program_code 
            JOIN examinations e ON s.semester = e.semester 
            WHERE e.session = 'END SEM DEC 2025'
            ORDER BY s.roll_number
        ";
        $all_students = $conn->query($all_students_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all results for these students
        $all_results_query = "
            SELECT r.*, sub.subject_code, sub.subject_name, sub.credits, s.roll_number, st.name as student_name
            FROM results r 
            JOIN subjects sub ON r.subject_id = sub.id 
            JOIN examinations e ON r.exam_id = e.id 
            JOIN students st ON r.student_id = st.id 
            LEFT JOIN programs p ON st.course = p.program_code 
            WHERE e.session = 'END SEM DEC 2025'
            ORDER BY st.roll_number, sub.subject_code
        ";
        $all_results = $conn->query($all_results_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Group results by student
        $students_results = [];
        foreach ($all_results as $result) {
            $roll_number = $result['roll_number'];
            if (!isset($students_results[$roll_number])) {
                $students_results[$roll_number] = [
                    'student_info' => [
                        'roll_number' => $result['roll_number'],
                        'name' => $result['student_name'],
                        'program_name' => $result['program_name']
                    ],
                    'courses' => []
                ];
            }
            $students_results[$roll_number]['courses'][] = $result;
        }
        
        // Calculate summary for each student
        foreach ($students_results as $roll_number => &$student_data) {
            $total_marks = 0;
            $total_credits = 0;
            $total_points = 0;
            
            foreach ($student_data['courses'] as &$course) {
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
                // Add grade_point to course array for display
                $course['grade_point'] = $grade_points;
            }
            
            $students_results[$roll_number]['summary'] = [
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
            LEFT JOIN programs p ON s.course = p.program_code 
            WHERE s.roll_number = ?
        ";
        $student_stmt = $conn->prepare($student_query);
        $student_stmt->execute([$roll_number]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fallback: get first student if no roll_number provided
        $first_student_query = "
            SELECT s.*, p.program_name 
            FROM students s 
            LEFT JOIN programs p ON s.course = p.program_code 
            LIMIT 1
        ";
        $student = $conn->query($first_student_query)->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            $roll_number = $student['roll_number'];
        }
    }
        
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
            
            foreach ($courses as &$course) {
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
                // Add grade_point to course array for display
                $course['grade_point'] = $grade_points;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            /* Hide everything except the result container */
            body * {
                visibility: hidden;
            }
            
            /* Show only the result container */
            .max-w-5xl, .max-w-5xl * {
                visibility: visible;
            }
            
            /* Remove background and shadows for printing */
            .max-w-5xl {
                background: white !important;
                border: 1px solid #000 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Ensure proper print layout */
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            /* Hide navigation and other elements */
            nav, header, footer, .navbar, script {
                display: none !important;
            }
            
            /* Print-friendly styling */
            .border {
                border: 1px solid #000 !important;
            }
            
            .text-gray-600, .text-gray-500 {
                color: #000 !important;
            }
            
            /* Ensure all text is black */
            * {
                color: #000 !important;
            }
            
            /* Remove hover effects */
            *:hover {
                background: transparent !important;
            }
        }
    </style>
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

    <?php if (empty($student)): ?>
        <!-- NO STUDENT DATA MESSAGE -->
        <div class="text-center py-10">
            <h3 class="text-xl font-semibold text-gray-600 mb-4">No Student Data Found</h3>
            <p class="text-gray-500 mb-4">Please use the result form to view your results.</p>
            <a href="ResultPage.html" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Go to Result Form
            </a>
        </div>
    <?php else: ?>
    <!-- STUDENT INFO -->
    <div class="grid grid-cols-2 gap-6 p-6 text-sm">

        <div class="space-y-2">
            <p><strong>Enrollment No:</strong> <?= $student['roll_number'] ?? 'N/A' ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($student['name'] ?? 'N/A') ?></p>
            <p><strong>Father's Name:</strong> <?= htmlspecialchars($student['father_name'] ?? 'N/A') ?></p>
            <p><strong>Mother's Name:</strong> <?= htmlspecialchars($student['mother_name'] ?? 'N/A') ?></p>
            <p><strong>Programme:</strong> <?= htmlspecialchars($student['program_name'] ?? 'N/A') ?></p>
        </div>

        <div class="space-y-2 text-right">
            <p><strong>Registration No:</strong> <?= $student['registration_no'] ?? 'N/A' ?></p>
            <p><strong>Semester:</strong> <?= $student['semester'] ?? 'N/A' ?></p>
            <p><strong>Date of Birth:</strong> <?= $student['dob'] ?? 'N/A' ?></p>
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
                    <p><strong>SGPA:</strong> <?= $summary['sgpa'] ?? 'N/A' ?></p>
                    <p><strong>CGPA:</strong> <?= $summary['cgpa'] ?? 'N/A' ?></p>
                </div>
                <div class="text-right">
                    <p><strong>Total Credits & EGP:</strong> <?= $summary['total_credits'] ?? 'N/A' ?> / <?= $summary['total_egp'] ?? 'N/A' ?></p>
                    <p><strong>Cumulative Credits & EGP:</strong> <?= $summary['cumulative_credits'] ?? 'N/A' ?> / <?= $summary['cumulative_egp'] ?? 'N/A' ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php endif; ?>

</div>

<!-- Print Button -->
<div class="text-center py-4">
    <button onclick="window.print()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
        🖨️ Print Result
    </button>
</div>

</body>
</html>
