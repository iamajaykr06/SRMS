<?php
/**
 * SRMS - Student Result Management System
 * Main Home Page
 */

// Include configuration
require_once dirname(__DIR__) . '/config/Config.php';

// Set page variables
$appUrl = Config::get('APP_URL', 'http://localhost:8000');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JRU Online Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/nav.js"></script>
</head>

<body class="bg-[#0b0f19] text-gray-800">

<div id="navbar-placeholder"></div>
<!-- HERO SECTION -->
<section class="relative min-h-screen pt-24 flex items-center justify-center overflow-hidden bg-[#0b0f19] text-white">

    <!-- Radial Glow Background -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(212,175,55,0.18),transparent_65%)]"></div>

    <div class="relative z-10 max-w-4xl px-6 text-center">

        <!-- JRU Logo with Enhanced Glassmorphism -->
        <div class="mb-8 relative">
            <div class="w-32 h-32 mx-auto relative group">
                <!-- Logo Background Circle -->
                <div class="absolute inset-0 bg-white/10 backdrop-blur-xl border-2 border-white/20 rounded-full shadow-2xl shadow-black/40 group-hover:bg-white/20 group-hover:border-yellow-400/50 transition-all duration-300 group-hover:scale-105"></div>
                
                <!-- Logo Container -->
                <div class="relative w-full h-full flex items-center justify-center p-4">
                    <img src="/assets/jrulogo.jpg" alt="Jharkhand Rai University Logo" class="w-full h-full object-contain rounded-full z-10">
                </div>
                
                <!-- Subtle Glow Effect -->
                <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/5 to-transparent rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
        </div>

        <!-- Badge with Glassmorphism -->
        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 backdrop-blur-xl px-5 py-2 shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffdd00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles-icon lucide-sparkles">
                <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/><path d="M20 2v4"/><path d="M22 4h-4"/><circle cx="4" cy="20" r="2"/>
            </svg>
            <span class="text-sm tracking-wide font-medium text-yellow-300">
        Jharkhand Rai University, Ranchi
      </span>
        </div>

        <!-- Heading -->
        <h1 class="mt-10 text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-tight">
            <span class="block text-gray-100">Online</span>
            <span class="block bg-gradient-to-r from-yellow-300 via-yellow-400 to-yellow-600 bg-clip-text text-transparent drop-shadow-[0_0_25px_rgba(212,175,55,0.25)]">
        Result Portal
      </span>
        </h1>

        <!-- Description -->
        <p class="mt-8 mx-auto max-w-2xl text-lg text-gray-400 leading-relaxed">
            Access your examination results securely and instantly.
            <br />
            <span class="text-yellow-400 font-medium">
        Best of luck for your exam result!
      </span>
        </p>

        <!-- CTA Button with Glassmorphism -->
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
            <button class="px-8 py-3 rounded-lg bg-white/10 backdrop-blur-xl border border-white/20 text-white font-semibold shadow-lg hover:bg-white/20 hover:scale-105 transition-all duration-300">
                <a href="/results" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Check Result
                </a>
            </button>
            <button class="px-8 py-3 rounded-lg bg-yellow-500/20 backdrop-blur-xl border border-yellow-500/30 text-yellow-300 font-semibold shadow-lg hover:bg-yellow-500/30 hover:scale-105 transition-all duration-300">
                <a href="/notices" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 7H4a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2V9a2 2 0 00-2-2z"></path>
                    </svg>
                    View Notices
                </a>
            </button>
        </div>

    </div>

</section>

<footer class="w-full bg-black/40 backdrop-blur-xl border-t border-white/10 px-4 py-12 text-sm text-gray-400">
    <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8 mb-12">
            <!-- University Info -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="relative w-14 h-14 bg-white/10 backdrop-blur-xl border border-white/20 rounded-full p-2">
                        <img src="/assets/jrulogo.jpg" alt="JRU Logo" class="w-full h-full rounded-full">
                    </div>
                    <h3 class="text-white font-semibold">Jharkhand Rai University</h3>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">
                    Empowering students with quality education and fostering academic excellence since our establishment.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-8 h-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="/results" class="text-gray-400 hover:text-yellow-400 transition-colors">Check Results</a></li>
                    <li><a href="/notices" class="text-gray-400 hover:text-yellow-400 transition-colors">Notices</a></li>
                    <li><a href="/contact" class="text-gray-400 hover:text-yellow-400 transition-colors">Contact Us</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-yellow-400 transition-colors">Academic Calendar</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="text-white font-semibold mb-4">Resources</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-yellow-400 transition-colors">Student Portal</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-yellow-400 transition-colors">Library</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-yellow-400 transition-colors">Examination</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-yellow-400 transition-colors">Downloads</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="text-white font-semibold mb-4">Contact Info</h4>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-yellow-500/20 backdrop-blur-xl border border-yellow-500/30 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Ranchi, Jharkhand</p>
                            <p class="text-gray-500 text-xs">India - 834001</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-yellow-500/20 backdrop-blur-xl border border-yellow-500/30 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">+91 651-1234567</p>
                            <p class="text-gray-500 text-xs">Mon-Fri 9AM-5PM</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-yellow-500/20 backdrop-blur-xl border border-yellow-500/30 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">info@jru.ac.in</p>
                            <p class="text-gray-500 text-xs">24/7 Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <p class="text-gray-400">&copy; <?php echo date('Y'); ?> Jharkhand Rai University. All rights reserved.</p>
                    <p class="text-gray-500 text-xs mt-1">Student Result Management System v2.0 | Optimized for Production</p>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-gray-400 hover:text-yellow-400 text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-yellow-400 text-sm transition-colors">Terms of Service</a>
                    <a href="#" class="text-gray-400 hover:text-yellow-400 text-sm transition-colors">Cookie Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
