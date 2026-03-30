<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marksheet</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: Arial, Helvetica, sans-serif; }

        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>

<body class="bg-white p-6">

<div class="max-w-5xl mx-auto border border-gray-400">

    <!-- HEADER -->
    <div class="p-4 border-b border-gray-400">

        <div class="relative flex items-center justify-center">

            <!-- LOGO -->
            <img src="<?= htmlspecialchars($university['logo_path']) ?>"
                 class="h-20 absolute left-2">

            <!-- CENTER TEXT -->
            <div class="text-center leading-tight">
                <h1 class="text-2xl font-bold">
                    <?= htmlspecialchars($university['name']) ?>
                </h1>

                <p class="text-sm">
                    <?= htmlspecialchars($university['address']) ?>
                </p>

                <p class="text-xs">
                    <?= htmlspecialchars($university['established_text']) ?>
                </p>
            </div>

        </div>

        <p class="text-center mt-3 font-semibold tracking-wide">
            END SEMESTER EXAMINATION - <?= htmlspecialchars($session) ?>
        </p>

    </div>

    <!-- STUDENT INFO -->
    <div class="text-[13px] px-4 py-3">
        <div class="grid grid-cols-4 gap-y-1">
            <p class="font-semibold">Roll No</p>
            <p><?= $student['roll_number'] ?></p>
            <p class="font-semibold text-left">Registration No</p>
            <p class="text-left"><?= $student['registration_no'] ?? '-' ?></p>

            <p class="font-semibold">Name</p>
            <p><?= $student['name'] ?></p>
            <p class="font-semibold text-left">Semester</p>
            <p class="text-left"><?= $student['semester'] ?></p>
        </div>

        <?php if (!empty($student['father_name']) || !empty($student['mother_name'])): ?>
        <div class="grid grid-cols-4 gap-y-1 mt-1">
            <?php if (!empty($student['father_name'])): ?>
            <p class="font-semibold">Father's Name</p>
            <p><?= $student['father_name'] ?></p>
            <?php else: ?>
            <p></p>
            <p></p>
            <?php endif; ?>
            
            <p class="font-semibold text-left">Date of Birth</p>
            <p class="text-left"><?= $student['dob'] ?? '-' ?></p>

            <?php if (!empty($student['mother_name'])): ?>
            <p class="font-semibold">Mother's Name</p>
            <p><?= $student['mother_name'] ?></p>
            <?php else: ?>
            <p></p>
            <p></p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-4 gap-y-1 mt-1">
            <p class="font-semibold">Date of Birth</p>
            <p class="text-left"><?= $student['dob'] ?? '-' ?></p>
            <p></p>
            <p></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-4 gap-y-1 mt-1">
            <p class="font-semibold">Programme</p>
            <p class="col-span-3"><?= $student['program_name'] ?></p>
            <p></p>
            <p></p>
        </div>
    </div>

    <!-- TABLE -->
    <table class="w-full text-[13px] border border-gray-400 border-collapse">

        <thead>
        <tr class="bg-gray-200 text-center">
            <th class="border border-gray-400 p-1">SR.NO.</th>
            <th class="border border-gray-400 p-1">COURSE CODE</th>
            <th class="border border-gray-400 p-1 text-left">COURSE</th>
            <th class="border border-gray-400 p-1">CREDITS</th>
            <th class="border border-gray-400 p-1">GRADE</th>
            <th class="border border-gray-400 p-1">GRADE POINT</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($courses as $i => $c): ?>
            <tr>
                <td class="border border-gray-400 p-1 text-center"><?= $i+1 ?></td>
                <td class="border border-gray-400 p-1 text-center"><?= $c['subject_code'] ?></td>
                <td class="border border-gray-400 p-1"><?= $c['subject_name'] ?></td>
                <td class="border border-gray-400 p-1 text-center"><?= $c['credits'] ?></td>
                <td class="border border-gray-400 p-1 text-center"><?= $c['grade'] ?></td>
                <td class="border border-gray-400 p-1 text-center"><?= $c['grade_point'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>

    </table>

    <!-- SUMMARY -->
    <div class="text-[13px] px-4 py-3 border-t border-gray-400">

        <div class="flex justify-between">
            <div class="flex gap-10">
                <p><b>SGPA :</b> <?= $summary['sgpa'] ?></p>
                <p><b>CGPA :</b> <?= $summary['cgpa'] ?></p>
            </div>

            <div>
                <p><b>TOTAL CREDITS & EGP :</b> <?= $summary['total_credits'] ?> / <?= $summary['egp'] ?? '-' ?></p>
            </div>
        </div>

        <div class="flex justify-end mt-2">
            <p><b>CUMULATIVE CREDITS & EGP :</b> <?= $summary['cum_credits'] ?? '-' ?> / <?= $summary['cum_egp'] ?? '-' ?></p>
        </div>

    </div>

</div>

<!-- PRINT BUTTON -->
<div class="text-center mt-6 no-print">
    <button onclick="window.print()" class="px-4 py-2 bg-black text-white">
        Print
    </button>
</div>

</body>
</html>