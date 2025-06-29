<?php
// Simple test file to check API accessibility
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo json_encode([
    'status' => 'success',
    'message' => 'Direct PHP access is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => 'PHP ' . phpversion(),
]);
