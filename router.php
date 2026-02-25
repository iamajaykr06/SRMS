<?php
/**
 * SRMS Router for PHP Development Server
 * This file routes requests to the appropriate files when using PHP built-in server
 */

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove query string
$uri = strtok($uri, '?');

// Set the public directory as the document root
$publicDir = __DIR__ . '/public';

// Handle specific routes first
switch ($uri) {
    case '/api/results':
    case '/api/results.php':
        $apiFile = $publicDir . '/api/results.php';
        include $apiFile;
        break;
    
    default:
        // Check if the requested file exists in the public directory
        $requestedFile = $publicDir . $uri;
        
        if (file_exists($requestedFile) && is_file($requestedFile)) {
            // Check if it's an HTML file, if so, include it
            if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'html') {
                include $requestedFile;
                return true;
            }
            // For JS files, include them directly
            if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'js') {
                header('Content-Type: application/javascript');
                readfile($requestedFile);
                return true;
            }
            // For CSS files, include them directly
            if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'css') {
                header('Content-Type: text/css');
                readfile($requestedFile);
                return true;
            }
            // For image files, include them directly
            if (in_array(pathinfo($requestedFile, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'])) {
                $mimeType = 'image/' . pathinfo($requestedFile, PATHINFO_EXTENSION);
                if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'jpg') $mimeType = 'image/jpeg';
                if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'svg') $mimeType = 'image/svg+xml';
                if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'ico') $mimeType = 'image/x-icon';
                header('Content-Type: ' . $mimeType);
                readfile($requestedFile);
                return true;
            }
            // For other files, change to public directory and return false
            chdir($publicDir);
            $relativePath = str_replace($publicDir, '', $requestedFile);
            $relativePath = ltrim($relativePath, '/\\');
            // Update the URI to remove the leading slash since we're now in the public directory
            $_SERVER['REQUEST_URI'] = $relativePath;
            return false;
        }
        
        // Route to index.php for all other requests
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        include $publicDir . '/index.php';
        break;
}
?>
