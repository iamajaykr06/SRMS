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
        include dirname(__DIR__) . '/src/views/index.html';
        break;
        
    case '/result':
    case '/results':
        include dirname(__DIR__) . '/src/views/ResultPage.html';
        break;
        
    case '/contact':
        include dirname(__DIR__) . '/src/views/Contact.html';
        break;
        
    case '/notices':
        include dirname(__DIR__) . '/src/views/Notice.html';
        break;
        
    case '/api/results':
    case '/api/results.php':
        include __DIR__ . '/api/results.php';
        break;
        
    case '/ResultShow.php':
    case '/ResultShow':
        include __DIR__ . '/ResultShow.php';
        break;
        
    default:
        // 404 - Page not found
        http_response_code(404);
        include dirname(__DIR__) . '/src/views/404.html';
        break;
}

