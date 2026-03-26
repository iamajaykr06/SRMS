<!DOCTYPE html>
<html lang="en">
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
            * { color: #000 !important; }
        }
    </style>
</head>

<body class="bg-gray-100 py-10">

<div class="print-container max-w-5xl mx-auto bg-white border border-black shadow">

    <!-- HEADER -->
    <div class="text-center border-b border-black p-4">
        <div class="relative flex items-center justify-center">

            <!-- Logo (absolute left) -->
            <img src="<?= htmlspecialchars($university['logo_path']) ?>"
                 class="h-16 w-16 absolute left-12">

            <!-- Centered content -->
            <div class="text-center">
                <h1 class="text-xl font-bold uppercase">
                    <?= htmlspecialchars($university['name']) ?>
                </h1>
                <p class="text-sm"><?= htmlspecialchars($university['address']) ?></p>
                <p class="text-xs"><?= htmlspecialchars($university['established_text']) ?></p>
            </div>

        </div>

        <h2 class="mt-3 font-semibold uppercase underline">
            END SEMESTER EXAMINATION - <?= htmlspecialchars($session) ?>
        </h2>
    </div>

    <!-- STUDENT DETAILS -->
    <div class="grid grid-cols-2 gap-4 text-sm p-4 border-b border-black">

        <div>
            <p><strong>Roll No:</strong> <?= $student['roll_number'] ?></p>
            <p><strong>Name:</strong> <?= $student['name'] ?></p>
            <p><strong>Father's Name:</strong> <?= $student['father_name'] ?? '-' ?></p>
            <p><strong>Mother's Name:</strong> <?= $student['mother_name'] ?? '-' ?></p>
            <p><strong>Programme:</strong> <?= $student['program_name'] ?></p>
        </div>

        <div class="text-right">
            <p><strong>Registration No:</strong> <?= $student['registration_no'] ?? '-' ?></p>
            <p><strong>Semester:</strong> <?= $student['semester'] ?></p>
            <p><strong>Date of Birth:</strong> <?= $student['dob'] ?? '-' ?></p>
        </div>

    </div>

    <!-- MARKS TABLE -->
    <div class="p-4">
        <table class="w-full text-sm border border-black border-collapse">

            <thead>
            <tr class="bg-gray-200 text-center">
                <th class="border border-black p-2">SR.NO.</th>
                <th class="border border-black p-2">COURSE CODE</th>
                <th class="border border-black p-2 text-left">COURSE</th>
                <th class="border border-black p-2">CREDITS</th>
                <th class="border border-black p-2">GRADE</th>
                <th class="border border-black p-2">GRADE POINT</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($courses as $i => $course): ?>
                <tr class="text-center">
                    <td class="border border-black p-2"><?= $i + 1 ?></td>
                    <td class="border border-black p-2"><?= $course['subject_code'] ?></td>
                    <td class="border border-black p-2 text-left"><?= $course['subject_name'] ?></td>
                    <td class="border border-black p-2"><?= $course['credits'] ?></td>
                    <td class="border border-black p-2"><?= $course['grade'] ?></td>
                    <td class="border border-black p-2"><?= $course['grade_point'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <!-- SUMMARY -->
    <div class="text-sm font-semibold p-4 border-t border-black">

        <div class="flex justify-between">
            <p>SGPA : <?= $summary['sgpa'] ?></p>
            <p>CGPA : <?= $summary['cgpa'] ?></p>
        </div>

        <div class="flex justify-between mt-2">
            <p>TOTAL CREDITS & EGP : <?= $summary['total_credits'] ?> / <?= $summary['egp'] ?? '-' ?></p>
            <p>CUMULATIVE CREDITS & EGP : <?= $summary['cum_credits'] ?? '-' ?> / <?= $summary['cum_egp'] ?? '-' ?></p>
        </div>

    </div>

</div>

<!-- PRINT BUTTON -->
<div class="text-center py-6 no-print">
    <button onclick="window.print()"
            class="px-6 py-3 bg-black text-white hover:bg-gray-800">
        Print Result
    </button>
</div>

</body>
</html>