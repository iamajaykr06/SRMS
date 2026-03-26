<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';
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

// Get dashboard statistics
try {
    $conn = $database->getConnection();
    
    // Total Students
    $stmt = $conn->query("SELECT COUNT(*) as total FROM students");
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Results
    $stmt = $conn->query("SELECT COUNT(*) as total FROM results_simple");
    $totalResults = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Notices (simulated - you might want to add a notices table)
    $totalNotices = 12; // This would come from database
    
    // Recent Results
    $stmt = $conn->query("SELECT r.*, s.name, s.roll_no FROM results_simple r JOIN students s ON r.student_id = s.id ORDER BY r.created_at DESC LIMIT 5");
    $recentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent Students
    $stmt = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $recentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $logger->error("Dashboard database error: " . $e->getMessage());
    $totalStudents = $totalResults = $totalNotices = 0;
    $recentResults = $recentStudents = [];
}

$pageTitle = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SRMS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
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
                    <a href="/admin/dashboard" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="/admin/students" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
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
                        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                        <p class="text-gray-400">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-400">
                            <?php echo date('l, F j, Y'); ?>
                        </div>
                        <div class="w-8 h-8 bg-green-400 rounded-full animate-pulse"></div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-slide-in">
                    <!-- Total Students Card -->
                    <div class="stat-card glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-green-400 bg-green-400/20 px-2 py-1 rounded-full">+12%</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-1"><?php echo number_format($totalStudents); ?></h3>
                        <p class="text-gray-400 text-sm">Total Students</p>
                    </div>

                    <!-- Total Results Card -->
                    <div class="stat-card glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-green-400 bg-green-400/20 px-2 py-1 rounded-full">+8%</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-1"><?php echo number_format($totalResults); ?></h3>
                        <p class="text-gray-400 text-sm">Total Results</p>
                    </div>

                    <!-- Total Notices Card -->
                    <div class="stat-card glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-yellow-400 bg-yellow-400/20 px-2 py-1 rounded-full">+3</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-1"><?php echo number_format($totalNotices); ?></h3>
                        <p class="text-gray-400 text-sm">Active Notices</p>
                    </div>

                    <!-- System Status Card -->
                    <div class="stat-card glass-effect rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-green-400 bg-green-400/20 px-2 py-1 rounded-full">Online</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-1">100%</h3>
                        <p class="text-gray-400 text-sm">System Health</p>
                    </div>
                </div>

                <!-- Charts and Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Chart Section -->
                    <div class="glass-effect rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Results Overview</h2>
                        <canvas id="resultsChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Recent Activity -->
                    <div class="glass-effect rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Recent Activity</h2>
                        <div class="space-y-4">
                            <?php if (!empty($recentResults)): ?>
                                <?php foreach ($recentResults as $result): ?>
                                    <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                                        <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-white">New result added for <span class="font-semibold"><?php echo htmlspecialchars($result['name']); ?></span></p>
                                            <p class="text-xs text-gray-400"><?php echo date('M j, Y g:i A', strtotime($result['created_at'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-400 text-sm">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass-effect rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="/admin/students?action=add" class="glass-effect rounded-lg p-4 text-center hover:bg-white/20 transition-colors">
                            <svg class="w-8 h-8 text-yellow-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <p class="text-sm text-white">Add Student</p>
                        </a>
                        
                        <a href="/admin/results?action=add" class="glass-effect rounded-lg p-4 text-center hover:bg-white/20 transition-colors">
                            <svg class="w-8 h-8 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <p class="text-sm text-white">Add Result</p>
                        </a>
                        
                        <a href="/admin/notices?action=add" class="glass-effect rounded-lg p-4 text-center hover:bg-white/20 transition-colors">
                            <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                            <p class="text-sm text-white">Add Notice</p>
                        </a>
                        
                        <a href="/admin/reports" class="glass-effect rounded-lg p-4 text-center hover:bg-white/20 transition-colors">
                            <svg class="w-8 h-8 text-purple-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4"/>
                            </svg>
                            <p class="text-sm text-white">View Reports</p>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/admin/logout';
            }
        }

        // Initialize Chart
        const ctx = document.getElementById('resultsChart').getContext('2d');
        const resultsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Results Added',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: 'rgb(251, 191, 36)',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                }
            }
        });

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
