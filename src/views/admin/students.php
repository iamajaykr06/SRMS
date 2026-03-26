<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/Logger.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}

$config = new Config();
$logger = Logger::getInstance();
$database = new Database();
$validator = new Validator();

// Handle form submissions
$message = '';
$messageType = '';

// Add/Edit Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $conn = $database->getConnection();
        
        if ($_POST['action'] === 'add_student') {
            // Validate inputs
            $name = $validator->sanitize($_POST['name']);
            $rollNumber = $validator->sanitize($_POST['roll_number']);
            $email = $validator->sanitize($_POST['email']);
            $phone = $validator->sanitize($_POST['phone']);
            $course = $validator->sanitize($_POST['course']);
            $semester = $validator->sanitize($_POST['semester']);
            $branch = $validator->sanitize($_POST['branch']);
            
            // Validate required fields
            if (empty($name) || empty($rollNumber) || empty($email)) {
                throw new Exception('Name, Roll Number, and Email are required');
            }
            
            // Check if roll number already exists
            $stmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ?");
            $stmt->execute([$rollNumber]);
            if ($stmt->fetch()) {
                throw new Exception('Roll Number already exists');
            }
            
            // Insert student
            $stmt = $conn->prepare("INSERT INTO students (roll_no, name, current_semester, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$rollNumber, $name, $semester]);
            
            $message = 'Student added successfully!';
            $messageType = 'success';
            $logger->info("Student added: $name ($rollNumber)");
            
        } elseif ($_POST['action'] === 'edit_student') {
            $id = $_POST['id'];
            $name = $validator->sanitize($_POST['name']);
            $rollNumber = $validator->sanitize($_POST['roll_number']);
            $email = $validator->sanitize($_POST['email']);
            $phone = $validator->sanitize($_POST['phone']);
            $course = $validator->sanitize($_POST['course']);
            $semester = $validator->sanitize($_POST['semester']);
            $branch = $validator->sanitize($_POST['branch']);
            
            // Update student
            $stmt = $conn->prepare("UPDATE students SET name = ?, roll_no = ?, current_semester = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $rollNumber, $semester, $id]);
            
            $message = 'Student updated successfully!';
            $messageType = 'success';
            $logger->info("Student updated: $name ($rollNumber)");
            
        } elseif ($_POST['action'] === 'delete_student') {
            $id = $_POST['id'];
            
            // Get student info before deletion
            $stmt = $conn->prepare("SELECT name, roll_no FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                // Delete student (cascade delete should handle related records)
                $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = 'Student deleted successfully!';
                $messageType = 'success';
                $logger->info("Student deleted: {$student['name']} ({$student['roll_no']})");
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $logger->error("Student management error: " . $e->getMessage());
    }
}

// Get students list
$students = [];
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $conn = $database->getConnection();
    
    // Build query
    $query = "SELECT * FROM students";
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE name LIKE ? OR roll_no LIKE ?";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM students";
    $countParams = [];
    
    if (!empty($search)) {
        $countQuery .= " WHERE name LIKE ? OR roll_no LIKE ?";
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
    }
    
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($countParams);
    $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalCount / $limit);
    
} catch (PDOException $e) {
    $logger->error("Database error in students: " . $e->getMessage());
    $students = [];
    $totalCount = 0;
    $totalPages = 0;
}

// Get student for editing
$editingStudent = null;
if (isset($_GET['edit'])) {
    try {
        $conn = $database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editingStudent = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logger->error("Error fetching student for edit: " . $e->getMessage());
    }
}

$pageTitle = "Student Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SRMS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0b0f19 0%, #1a1f2e 100%);
            font-family: 'Inter', sans-serif;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background: rgba(251, 191, 36, 0.1);
            border-left: 3px solid #fbbf24;
        }
        .sidebar-item.active {
            background: rgba(251, 191, 36, 0.2);
            border-left: 3px solid #fbbf24;
        }
        .table-row:hover {
            background: rgba(251, 191, 36, 0.05);
        }
        .btn-primary {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <!-- Admin Layout Container -->
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <aside class="w-64 glass-dark border-r border-white/10">
            <div class="p-6">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">SRMS Admin</h1>
                        <p class="text-xs text-gray-400">Management Panel</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="space-y-2">
                    <a href="/admin/dashboard" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="/admin/students" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Students</span>
                    </a>
                    
                    <a href="/admin/results" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Results</span>
                    </a>
                    
                    <a href="/admin/notices" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        <span>Notices</span>
                    </a>
                    
                    <a href="/admin/contacts" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Contacts</span>
                    </a>
                    
                    <a href="/admin/settings" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Settings</span>
                    </a>
                </nav>

                <!-- User Info -->
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="glass-effect rounded-lg p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                                <p class="text-xs text-gray-400">Administrator</p>
                            </div>
                        </div>
                        <button onclick="logout()" class="mt-3 w-full bg-red-500/20 hover:bg-red-500/30 text-red-400 py-2 px-4 rounded-lg text-sm transition-colors">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="glass-effect border-b border-white/10 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Student Management</h1>
                        <p class="text-gray-400">Manage student records and information</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="openAddModal()" class="btn-primary text-gray-900 font-semibold py-2 px-4 rounded-lg flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Add Student</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="mx-8 mt-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-500/20 border-green-500/50 text-green-400' : 'bg-red-500/20 border-red-500/50 text-red-400'; ?> border">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <?php if ($messageType === 'success'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            <?php else: ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            <?php endif; ?>
                        </svg>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Search and Filters -->
            <div class="px-8 mt-6">
                <div class="glass-effect rounded-lg p-4">
                    <form method="GET" class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by name, roll number, or email..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400"
                            >
                        </div>
                        <button type="submit" class="bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-500 transition-colors">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="/admin/students" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Students Table -->
            <div class="px-8 mt-6">
                <div class="glass-effect rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/5">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Roll Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Semester</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <?php if (!empty($students)): ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr class="table-row">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white"><?php echo htmlspecialchars($student['roll_no']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white"><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($student['current_semester'] ?? 'N/A'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center space-x-2">
                                                    <button onclick="editStudent(<?php echo $student['id']; ?>)" class="text-blue-400 hover:text-blue-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')" class="text-red-400 hover:text-red-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                            No students found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="px-6 py-4 border-t border-white/10">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-400">
                                    Showing <?php echo (($page - 1) * $limit) + 1; ?> to <?php echo min($page * $limit, $totalCount); ?> of <?php echo $totalCount; ?> results
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 bg-white/10 rounded hover:bg-white/20 transition-colors">
                                            Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 <?php echo $i === $page ? 'bg-yellow-400 text-gray-900' : 'bg-white/10 hover:bg-white/20'; ?> rounded transition-colors">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 bg-white/10 rounded hover:bg-white/20 transition-colors">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Student Modal -->
    <div id="studentModal" class="modal">
        <div class="glass-effect rounded-xl p-6 w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white"><?php echo $editingStudent ? 'Edit Student' : 'Add New Student'; ?></h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editingStudent ? 'edit_student' : 'add_student'; ?>">
                <?php if ($editingStudent): ?>
                    <input type="hidden" name="id" value="<?php echo $editingStudent['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                        <input type="text" name="name" required value="<?php echo $editingStudent ? htmlspecialchars($editingStudent['name']) : ''; ?>" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Roll Number *</label>
                        <input type="text" name="roll_number" required value="<?php echo $editingStudent ? htmlspecialchars($editingStudent['roll_number']) : ''; ?>" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Email *</label>
                        <input type="email" name="email" required value="<?php echo $editingStudent ? htmlspecialchars($editingStudent['email']) : ''; ?>" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                        <input type="tel" name="phone" value="<?php echo $editingStudent ? htmlspecialchars($editingStudent['phone']) : ''; ?>" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Course</label>
                        <select name="course" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-400">
                            <option value="">Select Course</option>
                            <option value="B.Tech" <?php echo ($editingStudent && $editingStudent['course'] === 'B.Tech') ? 'selected' : ''; ?>>B.Tech</option>
                            <option value="M.Tech" <?php echo ($editingStudent && $editingStudent['course'] === 'M.Tech') ? 'selected' : ''; ?>>M.Tech</option>
                            <option value="BCA" <?php echo ($editingStudent && $editingStudent['course'] === 'BCA') ? 'selected' : ''; ?>>BCA</option>
                            <option value="MCA" <?php echo ($editingStudent && $editingStudent['course'] === 'MCA') ? 'selected' : ''; ?>>MCA</option>
                            <option value="BBA" <?php echo ($editingStudent && $editingStudent['course'] === 'BBA') ? 'selected' : ''; ?>>BBA</option>
                            <option value="MBA" <?php echo ($editingStudent && $editingStudent['course'] === 'MBA') ? 'selected' : ''; ?>>MBA</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Semester</label>
                        <select name="semester" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-400">
                            <option value="">Select Semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($editingStudent && $editingStudent['semester'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?><?php echo $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')); ?> Semester</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Branch</label>
                        <input type="text" name="branch" value="<?php echo $editingStudent ? htmlspecialchars($editingStudent['branch']) : ''; ?>" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400" placeholder="e.g., Computer Science, Mechanical, etc.">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-gray-900 font-semibold py-2 px-6 rounded-lg">
                        <?php echo $editingStudent ? 'Update Student' : 'Add Student'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('studentModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('studentModal').classList.remove('active');
        }

        function editStudent(id) {
            window.location.href = '?edit=' + id;
        }

        function deleteStudent(id, name) {
            if (confirm('Are you sure you want to delete student "' + name + '"? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/admin/logout';
            }
        }

        // Auto-open edit modal if editing
        <?php if ($editingStudent): ?>
            document.getElementById('studentModal').classList.add('active');
        <?php endif; ?>

        // Close modal on outside click
        document.getElementById('studentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
