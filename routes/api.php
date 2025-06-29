<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\ApplicationDocumentController;
use App\Http\Controllers\API\PointController;
use App\Http\Controllers\API\RewardController;
use App\Http\Controllers\API\RedemptionController;
use App\Http\Controllers\API\SupportTicketController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProgramRequisiteController;
use App\Http\Controllers\API\ProgramAssignmentController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public programs endpoint
Route::get('/programs', [ProgramController::class, 'index']);

// Public settings endpoints
Route::get('/settings', [\App\Http\Controllers\API\SettingsController::class, 'index']);
Route::get('/settings/whatsapp', [\App\Http\Controllers\API\SettingsController::class, 'whatsapp']);
Route::get('/settings/contact', [\App\Http\Controllers\API\SettingsController::class, 'contact']);
Route::get('/settings/app-info', [\App\Http\Controllers\API\SettingsController::class, 'appInfo']);

// Rutas públicas para pruebas de requisitos (sin autenticación)
Route::get('/public/programs/{programId}/requisites', \App\Http\Controllers\API\ProgramRequisitosPruebaController::class);
Route::get('/public/applications/{applicationId}/requisites', [\App\Http\Controllers\API\ProgramRequisitosPruebaController::class, 'getApplicationRequisites']);

// Test endpoints for API connectivity
Route::get('/ping', function() {
    return response()->json(['message' => 'API is working!', 'status' => 'success', 'timestamp' => now()]);
});

// Simple test route with no middleware
Route::get('/test-connection', function () {
    return response()->json([
        'status' => 'success', 
        'message' => 'API connection successful',
        'timestamp' => now()->toDateTimeString(),
        'server' => 'Laravel ' . app()->version(),
        'cors' => 'This endpoint should be accessible from any origin',
    ]);
});

// Even simpler test route with specific headers
Route::options('/simple-test', function() {
    return response()->json(['message' => 'CORS preflight request successful']);
});

Route::get('/simple-test', function() {
    return response()
        ->json([
            'message' => 'Simple test successful', 
            'time' => time(),
            'date' => date('Y-m-d H:i:s')
        ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::get('/test-connection-2', function() {
    return response()->json([
        'message' => 'Conexión exitosa con la API de Experiencia Intercultural',
        'status' => 'success',
        'timestamp' => now(),
        'environment' => app()->environment(),
        'version' => '1.0'
    ]);
});

// Authenticated Routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // API para perfil de usuario
    Route::put('/profile', [\App\Http\Controllers\API\ProfileController::class, 'apiUpdate']);
    Route::post('/profile/avatar', [\App\Http\Controllers\API\ProfileController::class, 'apiUpdateAvatar']);

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Participant Routes
    Route::apiResource('programs', ProgramController::class)->only(['show'])->names(['show' => 'api.programs.show']);
    Route::apiResource('applications', ApplicationController::class)->names('api.applications');
    Route::apiResource('application-documents', ApplicationDocumentController::class)->names('api.application-documents');
    
    // Program Requisites
    Route::get('/programs/{programId}/requisites', [ProgramRequisiteController::class, 'getProgramRequisites']);
    Route::get('/applications/{applicationId}/requisites', [ProgramRequisiteController::class, 'getUserRequisites']);
    Route::post('/requisites/{requisiteId}/complete', [ProgramRequisiteController::class, 'completeRequisite']);
    Route::get('/applications/{applicationId}/progress', [ProgramRequisiteController::class, 'getApplicationProgress']);
    // Points Management
    Route::get('/points/balance', [PointController::class, 'balance']);
    Route::get('/points/history', [PointController::class, 'history']);
    Route::get('/points/statistics', [PointController::class, 'statistics']);
    Route::apiResource('points', PointController::class)->only(['index'])->names(['index' => 'api.points.index']);
    
    // Rewards Management
    Route::get('/rewards/categories', [RewardController::class, 'categories']);
    Route::apiResource('rewards', RewardController::class)->only(['index', 'show'])->names(['index' => 'api.rewards.index', 'show' => 'api.rewards.show']);
    
    // Redemptions Management
    Route::apiResource('redemptions', RedemptionController::class)->names('api.redemptions');
    Route::apiResource('support-tickets', SupportTicketController::class)->names('api.support-tickets');
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'update'])->names(['index' => 'api.notifications.index', 'show' => 'api.notifications.show', 'update' => 'api.notifications.update']);

    // Forms
    Route::get('/programs/{program}/form', [\App\Http\Controllers\API\FormController::class, 'getProgramForm']);
    Route::post('/programs/{program}/form/save', [\App\Http\Controllers\API\FormController::class, 'saveFormData']);
    Route::post('/programs/{program}/form/submit', [\App\Http\Controllers\API\FormController::class, 'submitForm']);
    Route::get('/form-submissions', [\App\Http\Controllers\API\FormController::class, 'getUserSubmissions']);
    Route::get('/form-submissions/{submission}', [\App\Http\Controllers\API\FormController::class, 'getSubmission']);
    Route::delete('/form-submissions/{submission}', [\App\Http\Controllers\API\FormController::class, 'deleteSubmission']);

    // Dynamic Forms
    Route::get('/programs/{program}/forms', [\App\Http\Controllers\API\FormController::class, 'getProgramForms']);
    Route::get('/programs/{program}/active-form', [\App\Http\Controllers\API\FormController::class, 'getActiveForm']);
    Route::get('/forms/{form}/structure', [\App\Http\Controllers\API\FormController::class, 'getFormStructure']);
    Route::post('/forms/{form}/submit', [\App\Http\Controllers\API\FormController::class, 'submitForm']);
    Route::post('/forms/{form}/draft', [\App\Http\Controllers\API\FormController::class, 'saveFormData']);
    Route::put('/forms/submissions/{submission}/draft', [\App\Http\Controllers\API\FormController::class, 'saveFormData']);
    Route::delete('/forms/submissions/{submission}/draft', [\App\Http\Controllers\API\FormController::class, 'deleteSubmission']);
    Route::post('/forms/{form}/validate', [\App\Http\Controllers\API\FormController::class, 'validateFormData']);
    Route::post('/forms/upload', [\App\Http\Controllers\API\FormController::class, 'uploadFile']);
    Route::get('/forms/countries', [\App\Http\Controllers\API\FormController::class, 'getCountries']);
    Route::get('/forms/templates', [\App\Http\Controllers\API\FormController::class, 'getFormTemplates']);
    Route::get('/forms/drafts', [\App\Http\Controllers\API\FormController::class, 'getUserSubmissions']);
    Route::get('/forms/submissions', [\App\Http\Controllers\API\FormController::class, 'getUserSubmissions']);

    // Program Assignments (for mobile app)
    Route::get('/assignments', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'index']);
    Route::get('/assignments/{assignment}', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'show']);
    Route::post('/assignments/{assignment}/apply', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'apply']);
    Route::get('/assignments/{assignment}/program', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'getProgramDetails']);
    Route::get('/available-programs', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'getAvailablePrograms']);
    Route::get('/my-stats', [\App\Http\Controllers\API\ProgramAssignmentController::class, 'getMyStats']);

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        // User Management
        Route::get('/admin/users', [AuthController::class, 'index']); // List all users
        Route::put('/admin/users/{id}', [AuthController::class, 'update']); // Update user

        // Program Management
        Route::apiResource('programs', ProgramController::class)->only(['store', 'update', 'destroy'])->names(['store' => 'api.admin.programs.store', 'update' => 'api.admin.programs.update', 'destroy' => 'api.admin.programs.destroy']);

        // Application Management
        Route::post('/admin/applications/{id}/review', [ApplicationController::class, 'review']);
        Route::post('/admin/applications/{id}/approve', [ApplicationController::class, 'approve']);
        Route::post('/admin/applications/{id}/reject', [ApplicationController::class, 'reject']);

        // Document Verification
        Route::post('/admin/application-documents/{id}/verify', [ApplicationDocumentController::class, 'verify']);
        Route::post('/admin/application-documents/{id}/reject', [ApplicationDocumentController::class, 'reject']);

        // Reward Management
        Route::apiResource('rewards', RewardController::class)->only(['store', 'update', 'destroy'])->names(['store' => 'api.admin.rewards.store', 'update' => 'api.admin.rewards.update', 'destroy' => 'api.admin.rewards.destroy']);

        // Redemption Management
        Route::post('/admin/redemptions/{id}/approve', [RedemptionController::class, 'approve']);
        Route::post('/admin/redemptions/{id}/reject', [RedemptionController::class, 'reject']);

        // Support Ticket Management
        Route::post('/admin/support-tickets/{id}/progress', [SupportTicketController::class, 'progress']);
        Route::post('/admin/support-tickets/{id}/close', [SupportTicketController::class, 'close']);

        // Notification Management
        Route::post('/admin/notifications', [NotificationController::class, 'store']); // Send notifications
    });
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json(['message' => 'Route not found'], 404);
});