<?php
/**
 * Special auth test file for intercultural experience platform
 * This bypasses Laravel authentication to diagnose connection issues
 */

// CORS headers to ensure proper cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization');

// Handle preflight request (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get post data
$postdata = file_get_contents('php://input');
$request = json_decode($postdata, true);

// Build the response
$response = [
    'status' => 'success',
    'message' => 'Test authentication successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'user' => [
        'id' => 1,
        'name' => 'Estudiante de Prueba',
        'email' => isset($request['email']) ? $request['email'] : 'test@example.com',
        'role' => 'user',
        'nationality' => 'Paraguay',
        'points' => 150
    ],
    'token' => 'test_auth_token_' . time(),
    'received' => $request
];

// Send the response
echo json_encode($response);
