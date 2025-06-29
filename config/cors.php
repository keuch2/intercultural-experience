<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    
    'allowed_origins' => [
        'http://localhost:8081',     // React Native Expo web
        'http://localhost:8082',     // React Native Expo web (port)
        'http://localhost:8083',     // React Native Expo web (current port)
        'http://localhost:19000',    // Expo development server
        'http://localhost:19001',    // Expo development server alternative
        'http://localhost:19002',    // Expo DevTools
        'http://localhost:19006',    // Expo web
        'exp://localhost:19000',     // Expo on device using localhost
        'http://localhost',          // General localhost
        'https://localhost',         // General localhost with HTTPS
        'https://localhost/ieapp',   // Your React Native app
        'capacitor://localhost',     // Capacitor
        'http://localhost:3000',     // React development
        'http://localhost:8000',     // Laravel development
        'http://localhost:8100',     // Ionic
        'null',                      // For some environments
    ],
    
    'allowed_origins_patterns' => [
        // Allow any IP in local network for Expo
        '#^exp://192\.168\.\d+\.\d+:19000$#',
        '#^exp://\d+\.\d+\.\d+\.\d+:19000$#',
    ],
    
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-CSRF-TOKEN',
        'Accept',
        'X-Auth-Token',
        'Origin',
        'Cache-Control',
    ],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true, // Required for Sanctum with mobile apps
];