<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edu_website');

// SMTP configuration
define('SMTP_HOST', 'smtp.gmail.com');  // Gmail SMTP server
define('SMTP_PORT', 587);               // Gmail SMTP port
define('SMTP_USERNAME', '');  // Replace with your Gmail address
define('SMTP_PASSWORD', '');     // Replace with the 16-character app password
define('SMTP_FROM_EMAIL', ''); // Replace with your Gmail address
define('SMTP_FROM_NAME', 'CodeMaster Academy');    // Sender name

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Email configuration
define('ADMIN_EMAIL', '');

// Security
define('ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:80', 'http://127.0.0.1', 'http://127.0.0.1:80']);
?> 