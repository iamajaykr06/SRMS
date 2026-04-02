<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Jharkhand Rai University – End Semester Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="bg-gray-100 min-h-screen py-8">
    <div class="w-full max-w-7xl mx-auto bg-white font-serif shadow-lg">

        <!-- Header -->
        <div class="flex items-center pt-6 pb-3 px-4">
            <img src="/assets/jrulogo.jpg" alt="jru-logo" height="100" width="100" class="shrink-0">
            <div class="flex-1 flex flex-col items-center text-center">
                <h1 class="text-2xl font-bold tracking-wide">Jharkhand Rai University</h1>
                <p class="text-sm font-semibold mt-1">
                    Jharkhand Rai University, Raja Ulatu, Namkum, Ranchi -<br />834010
                </p>
                <p class="text-[11px] mt-1 italic">
                    Established under the Jharkhand Rai University Act, 2012 (Jharkhand Act, 03, 2012)
                </p>
                <h2 class="text-base font-extrabold underline mt-3 tracking-wide">
                    <?= htmlspecialchars($sem['exam_title']) ?>
                </h2>
            </div>
            <div class="w-[100px] shrink-0"></div>
        </div>

        <!-- Student Info -->
        <div class="px-6 py-3 text-sm space-y-1.5">
            <div class="flex">
                <div class="flex w-[60%]">
                    <span class="font-bold w-[140px] shrink-0">Roll No</span>
                    <span><?= htmlspecialchars($student['roll_no']) ?></span>
                </div>
                <div class="flex">
                    <span class="font-bold w-[140px] shrink-0">Registration No</span>
                    <span><?= htmlspecialchars($student['registration_no']) ?></span>
                </div>
            </div>
            <div class="flex">
                <div class="flex w-[60%]">
                    <span class="font-bold w-[140px] shrink-0">Name</span>
                    <span><?= htmlspecialchars($student['name']) ?></span>
                </div>
                <div class="flex">
                    <span class="font-bold w-[140px] shrink-0">Semester</span>
                    <span><?= htmlspecialchars($sem['semester_no']) ?></span>
                </div>
            </div>
            <div class="flex">
                <div class="flex w-[60%]">
                    <?php if (!empty($student['father_name'])): ?>
                        <span class="font-bold w-[140px] shrink-0">Father's Name</span>
                        <span><?= htmlspecialchars($student['father_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex">
                    <span class="font-bold w-[140px] shrink-0">Date of Birth</span>
                    <span><?= $dob ?></span>
                </div>
            </div>
            <div class="flex">
                <?php if (!empty($student['mother_name'])): ?>
                    <span class="font-bold w-[140px] shrink-0">Mother's Name</span>
                    <span><?= htmlspecialchars($student['mother_name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex">
                <span class="font-bold w-[140px] shrink-0">Programme</span>
                <span><?= htmlspecialchars($student['programme']) ?></span>
            </div>
        </div>

        <!-- Results Table -->
        <div class="px-6 pb-2">
            <table class="w-full border-collapse text-sm">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-500 px-2 py-2 text-center w-[60px]">SR.NO.</th>
                    <th class="border border-gray-500 px-2 py-2 text-center w-[130px]">COURSE CODE</th>
                    <th class="border border-gray-500 px-2 py-2 text-center">COURSE</th>
                    <th class="border border-gray-500 px-2 py-2 text-center w-[80px]">CREDITS</th>
                    <th class="border border-gray-500 px-2 py-2 text-center w-[80px]">GRADE</th>
                    <th class="border border-gray-500 px-2 py-2 text-center w-[100px]">GRADE POINT</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $resultsRes->fetch_assoc()): ?>
                    <tr>
                        <td class="border border-gray-500 px-2 py-3 text-center"><?= $row['sr_no'] ?></td>
                        <td class="border border-gray-500 px-2 py-3 text-center"><?= htmlspecialchars($row['course_code']) ?></td>
                        <td class="border border-gray-500 px-2 py-3"><?= htmlspecialchars($row['course_name']) ?></td>
                        <td class="border border-gray-500 px-2 py-3 text-center"><?= number_format($row['credits'], 2) ?></td>
                        <td class="border border-gray-500 px-2 py-3 text-center"><?= htmlspecialchars($row['grade']) ?></td>
                        <td class="border border-gray-500 px-2 py-3 text-center"><?= number_format($row['grade_point'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="px-6 pb-6 text-sm space-y-1.5">
            <div class="flex items-center">
                <span class="font-bold mr-1">SGPA :</span>
                <span class="mr-10"><?= number_format($sem['sgpa'], 2) ?></span>
                <span class="font-bold mr-1">CGPA :</span>
                <span class="mr-10"><?= number_format($sem['cgpa'], 2) ?></span>
                <span class="font-bold mr-1 ml-auto">TOTAL CREDITS &amp; EGP :</span>
                <span class="w-[60px] text-center"><?= number_format($sem['total_credits'], 2) ?></span>
                <span class="w-[100px] text-right"><?= number_format($sem['total_egp'], 2) ?></span>
            </div>
            <div class="flex justify-end">
                <span class="font-bold mr-1">CUMULATIVE CREDITS &amp; EGP :</span>
                <span class="w-[60px] text-center"><?= number_format($sem['cumulative_credits'], 2) ?></span>
                <span class="w-[100px] text-right"><?= number_format($sem['cumulative_egp'], 2) ?></span>
            </div>
        </div>

    </div>

    <!-- Print Button -->
    <div class="flex justify-center my-6 no-print">
        <button onclick="window.print()"
                class="bg-gray-700 text-white px-8 py-2 font-serif tracking-wide hover:bg-gray-900 cursor-pointer">
            Print Result
        </button>
    </div>

</div>

</body>
</html>