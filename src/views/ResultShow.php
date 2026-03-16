<?php
require_once '../src/config/database.php';

try {

    /* ===============================
       INPUT
    =============================== */
    $roll_number = $_GET['roll_number'] ?? '';
    $session     = $_GET['session'] ?? '2023-24';

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

        $total_marks   = 0;
        $total_credits = 0;
        $total_points  = 0;

        foreach ($courses as $course) {
            $total_marks   += $course['total_marks'];
            $total_credits += $course['credits'];
            $total_points  += $course['grade_point'];
        }

        if ($total_credits <= 0) {
            throw new Exception('Invalid credit calculation.');
        }

        return [
                'sgpa'               => round($total_points / $total_credits, 2),
                'cgpa'               => round($total_points / $total_credits, 2),
                'total_credits'      => $total_credits,
                'total_egp'          => $total_points,
                'cumulative_credits' => $total_credits,
                'cumulative_egp'     => $total_points
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
       DETERMINE MODE
    =============================== */
    $isBulk = ($roll_number === '' && $session === 'END SEM DEC 2025');

    $students_results = [];
    $student = [];
    $courses = [];
    $summary = [];

    /* ===============================
       BULK MODE
    =============================== */
    if ($isBulk) {

        $query = "
            SELECT st.id as student_id,
                   st.roll_number,
                   st.name,
                   p.program_name,
                   sub.subject_code,
                   sub.subject_name,
                   sub.credits,
                   r.total_marks,
                   r.grade
            FROM results r
            JOIN students st ON r.student_id = st.id
            JOIN subjects sub ON r.subject_id = sub.id
            JOIN examinations e ON r.exam_id = e.id
            LEFT JOIN programs p ON st.course = p.program_code
            WHERE e.session = ?
            ORDER BY st.roll_number, sub.subject_code
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute([$session]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            throw new Exception('No results found for this session.');
        }

        foreach ($rows as $row) {

            $rn = $row['roll_number'];

            if (!isset($students_results[$rn])) {
                $students_results[$rn] = [
                        'info' => [
                                'roll_number' => $rn,
                                'name' => $row['name'],
                                'program_name' => $row['program_name']
                        ],
                        'courses' => []
                ];
            }

            $row['grade_point'] = gradeToPoint($row['grade']);
            $students_results[$rn]['courses'][] = $row;
        }

        foreach ($students_results as &$data) {
            $data['summary'] = calculateSummary($data['courses']);
        }
    }

    /* ===============================
       SINGLE STUDENT MODE
    =============================== */
    else {

        if (!$roll_number) {
            throw new Exception('Roll number is required.');
        }

        $studentQuery = "
            SELECT s.*, p.program_name
            FROM students s
            LEFT JOIN programs p ON s.course = p.program_code
            WHERE s.roll_number = ?
        ";

        $stmt = $conn->prepare($studentQuery);
        $stmt->execute([$roll_number]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception('Student not found.');
        }

        $resultQuery = "
            SELECT r.*, sub.subject_code, sub.subject_name, sub.credits
            FROM results r
            JOIN subjects sub ON r.subject_id = sub.id
            JOIN examinations e ON r.exam_id = e.id
            WHERE r.student_id = ? AND e.session = ?
            ORDER BY sub.subject_code
        ";

        $stmt = $conn->prepare($resultQuery);
        $stmt->execute([$student['id'], $session]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$courses) {
            throw new Exception('No results found for this student.');
        }

        foreach ($courses as &$course) {
            $course['grade_point'] = gradeToPoint($course['grade']);
        }

        $summary = calculateSummary($courses);
    }

} catch (Exception $e) {

    http_response_code(500);
    echo "<h1>Result Processing Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .print-container {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                margin: 0 !important;
            }
            th, td { border: 1px solid #000 !important; }
            thead { display: table-header-group; }
            * { color: #000 !important; }
        }
    </style>
</head>

<body class="bg-gray-100 py-10">

<div class="print-container max-w-5xl mx-auto bg-white border shadow-lg rounded-lg overflow-hidden">

    <div class="border-b p-6">
        <div class="flex items-center gap-4">
            <img src="<?= htmlspecialchars($university['logo_path']) ?>"
                 class="h-16 w-16 object-contain">
            <div>
                <h1 class="text-2xl font-bold uppercase">
                    <?= htmlspecialchars($university['name']) ?>
                </h1>
                <p class="text-sm"><?= htmlspecialchars($university['address']) ?></p>
                <p class="text-xs text-gray-600">
                    <?= htmlspecialchars($university['established_text']) ?>
                </p>
            </div>
        </div>

        <h2 class="text-center text-lg font-semibold mt-6 uppercase underline">
            End Semester Examination - <?= htmlspecialchars($session) ?>
        </h2>
    </div>

    <?php if ($isBulk): ?>

        <?php foreach ($students_results as $data): ?>

            <div class="p-6 border-b">

                <h3 class="font-semibold mb-4">
                    <?= htmlspecialchars($data['info']['roll_number']) ?> -
                    <?= htmlspecialchars($data['info']['name']) ?>
                </h3>

                <table class="w-full text-sm border-collapse mb-4">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">#</th>
                        <th class="p-2">Code</th>
                        <th class="p-2 text-left">Subject</th>
                        <th class="p-2">Credits</th>
                        <th class="p-2">Grade</th>
                        <th class="p-2">GP</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['courses'] as $i => $course): ?>
                        <tr class="text-center">
                            <td class="p-2"><?= $i + 1 ?></td>
                            <td class="p-2"><?= htmlspecialchars($course['subject_code']) ?></td>
                            <td class="p-2 text-left"><?= htmlspecialchars($course['subject_name']) ?></td>
                            <td class="p-2"><?= $course['credits'] ?></td>
                            <td class="p-2"><?= $course['grade'] ?></td>
                            <td class="p-2"><?= $course['grade_point'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="text-sm font-medium">
                    SGPA: <?= $data['summary']['sgpa'] ?> |
                    Total Credits: <?= $data['summary']['total_credits'] ?>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="p-6">

            <div class="grid md:grid-cols-2 gap-6 text-sm mb-6">
                <div>
                    <p><strong>Enrollment:</strong> <?= htmlspecialchars($student['roll_number']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
                    <p><strong>Programme:</strong> <?= htmlspecialchars($student['program_name']) ?></p>
                </div>
                <div class="md:text-right">
                    <p><strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?></p>
                </div>
            </div>

            <table class="w-full text-sm border-collapse mb-6">
                <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">SR. No.</th>
                    <th class="p-2">Code</th>
                    <th class="p-2 text-left">Subject</th>
                    <th class="p-2">Credits</th>
                    <th class="p-2">Grade</th>
                    <th class="p-2">GP</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $i => $course): ?>
                    <tr class="text-center">
                        <td class="p-2"><?= $i + 1 ?></td>
                        <td class="p-2"><?= htmlspecialchars($course['subject_code']) ?></td>
                        <td class="p-2 text-left"><?= htmlspecialchars($course['subject_name']) ?></td>
                        <td class="p-2"><?= $course['credits'] ?></td>
                        <td class="p-2"><?= $course['grade'] ?></td>
                        <td class="p-2"><?= $course['grade_point'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-sm font-medium">
                SGPA: <?= $summary['sgpa'] ?> |
                CGPA: <?= $summary['cgpa'] ?> |
                Total Credits: <?= $summary['total_credits'] ?>
            </div>

        </div>

    <?php endif; ?>

</div>

<div class="text-center py-6 no-print">
    <button onclick="window.print()"
            class="px-6 py-3 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
        Print Result
    </button>
</div>

</body>
</html>