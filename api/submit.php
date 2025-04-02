<?php
// Start output buffering
ob_start();

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    // Load required files
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db.php';

    // Enable CORS
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: " . $origin);
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Max-Age: 3600");
    }

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        sendJsonResponse(['success' => true]);
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(['error' => 'Method not allowed'], 405);
    }

    // Get POST data
    $rawData = file_get_contents('php://input');
    error_log("Received data: " . $rawData);

    if (empty($rawData)) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['interest'])) {
        throw new Exception('Missing required fields');
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Sanitize inputs
    $db = Database::getInstance();
    $name = $db->escape($data['name']);
    $email = $db->escape($data['email']);
    $interest = $db->escape($data['interest']);

    // Store in database
    $sql = "INSERT INTO leads (name, email, course_interest, created_at) 
            VALUES ('$name', '$email', '$interest', NOW())";

    try {
        $db->query($sql);
        error_log("Data inserted successfully");
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception('Failed to save your information. Please try again later.');
    }

    // Clear any output buffers
    ob_clean();

    // Return success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Thank you for your interest! We will contact you soon.'
    ]);

} catch (Exception $e) {
    // Clear any output buffers
    ob_clean();
    
    error_log("Form submission error: " . $e->getMessage());
    sendJsonResponse([
        'error' => $e->getMessage(),
        'details' => 'An error occurred while processing your request'
    ], 500);
}

// End output buffering and send response
ob_end_flush();
?> 