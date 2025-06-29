<?php
// Simple test file for login bypass
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create a mock login response
$response = [
    'status' => 'success',
    'message' => 'Login successful via direct PHP',
    'user' => [
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'user',
    ],
    'token' => 'test_token_' . time(),
    'timestamp' => date('Y-m-d H:i:s')
];

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Add the received credentials to the response (for debugging)
if ($data) {
    $response['received'] = $data;
}

echo json_encode($response);
