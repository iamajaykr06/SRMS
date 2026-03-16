<?php
/**
 * SRMS - Student Result Management System
 * Notices Page
 */

// Include configuration
require_once dirname(__DIR__) . '/config/Config.php';

// Set page variables
$pageTitle = "Notices - JRU Student Result Management System";
$appUrl = Config::get('APP_URL', 'http://localhost:8000');

// Sample notices data (in production, this would come from database)
$notices = [
    [
        'id' => 1,
        'title' => 'Examination Schedule Released',
        'content' => 'The examination schedule for the upcoming semester has been released. Please check the official website for detailed timetables.',
        'date' => '2024-03-15',
        'category' => 'Examination',
        'priority' => 'high'
    ],
    [
        'id' => 2,
        'title' => 'Result Declaration Date',
        'content' => 'Results for the previous semester will be declared on March 25, 2024. Students can check their results online.',
        'date' => '2024-03-10',
        'category' => 'Results',
        'priority' => 'medium'
    ],
    [
        'id' => 3,
        'title' => 'Holiday Notice',
        'content' => 'The university will remain closed on March 20, 2024, on account of Holi festival.',
        'date' => '2024-03-08',
        'category' => 'Holiday',
        'priority' => 'low'
    ],
    [
        'id' => 4,
        'title' => 'Admission Open for New Session',
        'content' => 'Admissions for the academic session 2024-25 are now open. Interested candidates can apply online.',
        'date' => '2024-03-05',
        'category' => 'Admission',
        'priority' => 'high'
    ],
    [
        'id' => 5,
        'title' => 'Workshop on Career Development',
        'content' => 'A workshop on career development will be organized on March 18, 2024. All students are requested to attend.',
        'date' => '2024-03-01',
        'category' => 'Event',
        'priority' => 'medium'
    ]
];

// Filter notices by category if specified
$category = $_GET['category'] ?? '';
if ($category) {
    $notices = array_filter($notices, function($notice) use ($category) {
        return strtolower($notice['category']) === strtolower($category);
    });
}
?>
 

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice - JRU Online Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/nav.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Jharkhand Rai University examination notices and announcements">
    <link rel="icon" type="image/png" href="/assets/jrulogo.jpg">
    <link rel="shortcut icon" href="/assets/jrulogo.jpg">
</head>
<body class="bg-[#0b0f19] text-gray-800">

<div id="navbar-placeholder"></div>

<!-- Notice Page Content -->
<section class="min-h-screen pt-24 py-20 px-6">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-2 backdrop-blur-md shadow-lg mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/>
                    <path d="M20 2v4"/>
                    <path d="M22 4h-4"/>
                    <circle cx="4" cy="20" r="2"/>
                </svg>
                <span class="text-sm tracking-wide font-medium text-red-300">
                    Examination Cell
                </span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 tracking-tight">
                <span class="bg-gradient-to-r from-red-400 via-red-500 to-orange-500 bg-clip-text text-transparent">
                    Exam Notices
                </span>
            </h1>
            
            <p class="text-gray-400 text-lg">Official examination announcements, schedules, and important updates</p>
        </div>
        
        <!-- Filter Tabs and Search -->
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-12">
            <div class="flex flex-wrap justify-center gap-2">
                <button class="filter-btn px-6 py-2 bg-red-500/20 border border-red-500/30 text-red-300 rounded-full hover:bg-red-500/30 transition-all duration-300" data-filter="all">
                    All Notices
                </button>
                <button class="filter-btn px-6 py-2 bg-white/10 border border-white/20 text-white/80 rounded-full hover:bg-white/20 transition-all duration-300" data-filter="schedule">
                    Schedules
                </button>
                <button class="filter-btn px-6 py-2 bg-white/10 border border-white/20 text-white/80 rounded-full hover:bg-white/20 transition-all duration-300" data-filter="result">
                    Results
                </button>
                <button class="filter-btn px-6 py-2 bg-white/10 border border-white/20 text-white/80 rounded-full hover:bg-white/20 transition-all duration-300" data-filter="guideline">
                    Guidelines
                </button>
            </div>
            
            <!-- Search Bar -->
            <div class="relative w-full lg:w-80">
                <label for="searchInput"></label><input type="text" id="searchInput" placeholder="Search notices..."
                                                        class="w-full px-4 py-2 pl-10 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-red-400 transition-all duration-300">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <div id="noticesContainer" class="grid gap-8">
            <!-- Featured Notice -->
            <div class="notice-item bg-gradient-to-r from-red-500/10 to-orange-500/10 backdrop-blur-2xl border border-red-500/30 rounded-2xl p-8 shadow-2xl shadow-black/50 transform hover:scale-[1.02] transition-all duration-300" data-category="urgent">
                <div class="flex items-start gap-4">
                    <div class="bg-red-500/20 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="inline-block px-3 py-1 bg-red-500/20 text-red-400 text-xs font-semibold rounded-full mb-2">URGENT</span>
                                <h2 class="text-2xl font-bold text-red-400 mb-2">End Semester Examination Schedule Released</h2>
                            </div>
                            <span class="text-sm text-gray-400 whitespace-nowrap">Feb 23, 2025</span>
                        </div>
                        <p class="text-gray-300 leading-relaxed mb-4">The end semester examination schedule for all courses has been officially released. Students are requested to check their respective examination timetables and report to examination centers 30 minutes before scheduled time.</p>
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <span>📅 Starts: March 15, 2025</span>
                            <span>📍 Multiple Centers</span>
                            <a href="#" class="text-red-400 hover:text-red-300">📄 Download Schedule</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Regular Notices -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Notice Item 1 -->
                <div class="notice-item bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:bg-white/10 hover:border-red-400/30 transition-all duration-300" data-category="result">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="bg-green-500/20 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-green-400">December 2024 Results Declared</h3>
                                <span class="text-xs text-gray-400">Feb 20, 2025</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed mb-4">Results for December 2024 end semester examinations have been declared. Students can check their results online using their roll numbers.</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 bg-green-500/20 text-green-400 rounded">Results</span>
                        </div>
                        <a href="#" class="text-xs text-red-400 hover:text-red-300">Check Result →</a>
                    </div>
                </div>
                
                <!-- Notice Item 2 -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:bg-white/10 hover:border-red-400/30 transition-all duration-300">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="bg-orange-500/20 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-orange-400">Examination Form Deadline</h3>
                                <span class="text-xs text-gray-400">Feb 15, 2025</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Last date to submit examination forms for March 2025 end semester examinations. Late fees applicable after deadline.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-orange-500/20 text-orange-400 rounded">Schedule</span>
                        <a href="#" class="text-xs text-red-400 hover:text-red-300">Submit Form →</a>
                    </div>
                </div>
                
                <!-- Notice Item 3 -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:bg-white/10 hover:border-red-400/30 transition-all duration-300">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="bg-blue-500/20 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-blue-400">Exam Guidelines Updated</h3>
                                <span class="text-xs text-gray-400">Feb 10, 2025</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">New examination guidelines and instructions for students appearing in end semester examinations. Important rules and regulations.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-blue-500/20 text-blue-400 rounded">Guidelines</span>
                        <a href="#" class="text-xs text-red-400 hover:text-red-300">Download PDF →</a>
                    </div>
                </div>
                
                <!-- Notice Item 4 -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:bg-white/10 hover:border-red-400/30 transition-all duration-300">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="bg-purple-500/20 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg font-semibold text-purple-400">Exam Center Allotment</h3>
                                <span class="text-xs text-gray-400">Feb 5, 2025</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">Examination center allotment for March 2025 end semester examinations. Students must check their allotted centers and report accordingly.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-xs px-2 py-1 bg-purple-500/20 text-purple-400 rounded">Centers</span>
                        <a href="#" class="text-xs text-red-400 hover:text-red-300">Check Center →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Load More Button -->
        <div class="text-center mt-12">
            <button id="loadMoreBtn" class="px-8 py-3 bg-white/10 backdrop-blur-xl border border-white/20 text-white rounded-xl hover:bg-white/20 hover:border-red-400 transition-all duration-300">
                Load More Exam Notices
            </button>
        </div>
        
        <!-- No Results Message -->
        <div id="noResults" class="hidden text-center py-12">
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No Notices Found</h3>
                <p class="text-gray-400">Try adjusting your search or filter criteria</p>
            </div>
        </div>
    </div>
</section>

<script>

    // Search and Filter Functionality
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const noticeItems = document.querySelectorAll('.notice-item');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const noResults = document.getElementById('noResults');
    let currentFilter = 'all';
    
    // Filter functionality
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            filterBtns.forEach(b => {
                b.classList.remove('bg-red-500/20', 'border-red-500/30', 'text-red-300');
                b.classList.add('bg-white/10', 'border-white/20', 'text-white/80');
            });
            btn.classList.remove('bg-white/10', 'border-white/20', 'text-white/80');
            btn.classList.add('bg-red-500/20', 'border-red-500/30', 'text-red-300');
            
            currentFilter = btn.dataset.filter;
            filterNotices();
        });
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', filterNotices);
    }
    
    function filterNotices() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;
        
        noticeItems.forEach(item => {
            const category = item.dataset.category;
            const text = item.textContent.toLowerCase();
            
            const matchesFilter = currentFilter === 'all' || category === currentFilter;
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            loadMoreBtn.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            loadMoreBtn.classList.remove('hidden');
        }
    }
    
    // Load more functionality
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            // Simulate loading more notices
            loadMoreBtn.textContent = 'Loading...';
            loadMoreBtn.disabled = true;
            
            setTimeout(() => {
                loadMoreBtn.textContent = 'Load More Exam Notices';
                loadMoreBtn.disabled = false;
                // In a real app, this would load more notices from the server
                showMessage('No more notices available at the moment.', 'info');
            }, 1500);
        });
    }
    
    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `fixed top-20 right-4 z-50 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-300 ${
            type === 'info' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 
            'bg-green-500/20 text-green-300 border border-green-500/30'
        }`;
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
</script>
</body>
</html>