<?php
/**
 * SRMS - Student Result Management System
 * Contact Page
 */

// Include configuration
require_once dirname(__DIR__) . '/config/Config.php';

// Set page variables
$appUrl = Config::get('APP_URL', 'http://localhost:8000');

// Process form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include validator
    require_once dirname(__DIR__) . '/utils/Validator.php';
    $validator = new Validator();
    
    $name = $validator->validateName($_POST['name'] ?? '');
    $email = $validator->validateEmail($_POST['email'] ?? '');
    $phone = $validator->validatePhone($_POST['phone'] ?? '');
    $subject = $validator->validateName($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && $subject && !empty($message) && $validator->isValid()) {
        // Here you would typically save to database or send email
        // For now, we'll just show success message
        $successMessage = 'Thank you for contacting us. We will get back to you soon!';
        
        // Log the contact attempt
        require_once dirname(__DIR__) . '/utils/Logger.php';
        $logger = Logger::getInstance();
        $logger->info('Contact form submission', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject
        ]);
    } else {
        $errorMessage = 'Please fill in all required fields correctly.';
    }
}
?>
 

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - JRU Online Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/nav.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Jharkhand Rai University for inquiries and support">
    <link rel="icon" type="image/png" href="/assets/jrulogo.jpg">
    <link rel="shortcut icon" href="/assets/jrulogo.jpg">
</head>
<body class="bg-[#0b0f19] text-gray-800">
<div id="navbar-placeholder"></div>

<!-- Contact Page Content -->
<section class="min-h-screen pt-24 py-20 px-6">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header Section -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-2 backdrop-blur-md shadow-lg mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/>
                    <path d="M20 2v4"/>
                    <path d="M22 4h-4"/>
                    <circle cx="4" cy="20" r="2"/>
                </svg>
                <span class="text-sm tracking-wide font-medium text-blue-300">
                    Contact Us
                </span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 tracking-tight">
                <span class="bg-gradient-to-r from-blue-300 via-blue-400 to-blue-600 bg-clip-text text-transparent">
                    Get in Touch
                </span>
            </h1>
            
            <p class="text-gray-400 text-lg">Reach out to us for any queries or assistance</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Information -->
            <div class="space-y-8">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Contact Information</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="bg-blue-500/20 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Address</h3>
                                <p class="text-gray-300">Jharkhand Rai University<br>Ranchi, Jharkhand - 834001</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-green-500/20 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 2.493a1 1 0 00.902.603l2.168.97a1 1 0 01.966 1.41l1.082 2.162A1 1 0 0118.95 13H19a2 2 0 002-2V7a2 2 0 00-2-2h-1"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l4.172 4.172a2 2 0 001.414.586L13 14"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Phone</h3>
                                <p class="text-gray-300">+91 651-234-5678<br>+91 651-234-5679</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-purple-500/20 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Email</h3>
                                <p class="text-gray-300">info@jru.edu.in<br>exam@jru.edu.in</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Office Hours -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Office Hours</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-300">
                            <span>Monday - Friday</span>
                            <span>9:00 AM - 5:00 PM</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Saturday</span>
                            <span>9:00 AM - 1:00 PM</span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Sunday</span>
                            <span>Closed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-white mb-6">Send us a Message</h2>
                <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                        <label>
                            <input type="text" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 transition-all duration-300" placeholder="Your name">
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <label>
                            <input type="email" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 transition-all duration-300" placeholder="your@email.com">
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
                        <label>
                            <input type="text" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 transition-all duration-300" placeholder="How can we help?">
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                        <label>
                            <textarea rows="4" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 transition-all duration-300" placeholder="Your message..."></textarea>
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-blue-500/30">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

</body>
</html>