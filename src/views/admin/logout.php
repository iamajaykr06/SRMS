<?php
require_once __DIR__ . '/../../utils/Logger.php';

// Start session and destroy it
session_start();
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Log logout
$logger = Logger::getInstance();
$logger->info("Admin logged out");

// Redirect to login page
header('Location: /admin/login');
exit;
?>
