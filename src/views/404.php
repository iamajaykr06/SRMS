<?php
/**
 * SRMS - Student Result Management System
 * 404 Error Page
 */

// Include configuration
require_once dirname(__DIR__) . '/config/Config.php';

// Set page variables
$pageTitle = "Page Not Found - JRU Student Result Management System";
$appUrl = Config::get('APP_URL', 'http://localhost:8000');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<!-- Navigation -->
<nav class="bg-white shadow-lg fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="/" class="flex items-center">
                    <img src="/assets/university-logo.png" alt="JRU Logo" class="h-8 w-8 rounded-full mr-3">
                    <span class="font-bold text-xl text-gray-800">JRU Result System</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                <a href="/result" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Results</a>
                <a href="/contact" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                <a href="/notices" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Notices</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mx-auto px-4 py-8 mt-16">
    <div class="max-w-2xl mx-auto text-center">
        <!-- 404 Illustration -->
        <div class="mb-8">
            <div class="relative">
                <div class="text-9xl font-bold text-gray-300">404</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-2xl font-semibold text-gray-600">Page Not Found</div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Oops! Page Not Found</h1>
        <p class="text-xl text-gray-600 mb-8">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Go Home
            </a>
            <a href="/result" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Check Results
            </a>
        </div>

        <!-- Help Section -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">What can you do?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold text-gray-800">Search</h3>
                        <p class="text-sm text-gray-600">Try searching for what you're looking for using the search bar.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold text-gray-800">Browse</h3>
                        <p class="text-sm text-gray-600">Navigate through our main sections using the menu above.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold text-gray-800">Check URL</h3>
                        <p class="text-sm text-gray-600">Make sure the URL is spelled correctly and try again.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold text-gray-800">Contact Support</h3>
                        <p class="text-sm text-gray-600">If you need help, contact our support team.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/" class="text-blue-600 hover:text-blue-800 underline">Home</a>
                <span class="text-gray-400">|</span>
                <a href="/result" class="text-blue-600 hover:text-blue-800 underline">Results</a>
                <span class="text-gray-400">|</span>
                <a href="/contact" class="text-blue-600 hover:text-blue-800 underline">Contact</a>
                <span class="text-gray-400">|</span>
                <a href="/notices" class="text-blue-600 hover:text-blue-800 underline">Notices</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="container mx-auto px-4 text-center">
        <p>&copy; <?php echo date('Y'); ?> Jharkhand Rai University. All rights reserved.</p>
        <p class="text-sm text-gray-400 mt-2">Student Result Management System v2.0</p>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Log 404 error for analytics (in production)
    console.log('404 Error:', {
        url: window.location.href,
        referrer: document.referrer,
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString()
    });
    
    // Auto-redirect to home after 30 seconds (optional)
    setTimeout(function() {
        if (confirm('Would you like to go to the homepage?')) {
            window.location.href = '/';
        }
    }, 30000);
});
</script>

</body>
</html>
