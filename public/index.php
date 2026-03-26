<?php
/**
 * SRMS - Student Result Management System
 * Main Entry Point
 */

// Set headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Include configuration
require_once dirname(__DIR__) . '/src/config/database.php';

// Simple router based on request
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Route to appropriate page
switch ($path) {
    case '/':
    case '':
    case '/home':
        include dirname(__DIR__) . '/src/views/index.php';
        break;
        
    case '/results':
        include dirname(__DIR__) . '/src/views/ResultPage.php';
        break;
        
    case '/contact':
        include dirname(__DIR__) . '/src/views/Contact.php';
        break;
        
    case '/notices':
        include dirname(__DIR__) . '/src/views/Notice.php';
        break;
        
    case '/api/results':
    case '/api/results.php':
        include __DIR__ . '/api/results.php';
        break;
        
    case '/result':
    case '/result.php':
        include dirname(__DIR__) . '/src/controllers/result.php';
        break;
        
    case '/admin':
    case '/admin/login':
        include dirname(__DIR__) . '/src/views/admin/login.php';
        break;
        
    case '/admin/dashboard':
        include dirname(__DIR__) . '/src/views/admin/dashboard.php';
        break;
        
    case '/admin/students':
        include dirname(__DIR__) . '/src/views/admin/students.php';
        break;
        
    case '/admin/results':
        include dirname(__DIR__) . '/src/views/admin/results.php';
        break;
        
    case '/admin/logout':
        include dirname(__DIR__) . '/src/views/admin/logout.php';
        break;
        
    default:
        // 404 - Page not found
        http_response_code(404);
        include dirname(__DIR__) . '/src/views/404.php';
        break;
}

