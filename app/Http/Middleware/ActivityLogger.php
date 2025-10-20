<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Handle an incoming request and log critical activities
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Log critical activities for admin users
        if ($request->user() && $request->user()->role === 'admin') {
            $this->logAdminActivity($request, $response);
        }
        
        // Log failed authentication attempts
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 403) {
            $this->logFailedAccess($request, $response);
        }
        
        return $response;
    }
    
    /**
     * Log admin activities for audit trail
     */
    private function logAdminActivity(Request $request, $response)
    {
        $criticalRoutes = [
            'admin/users',
            'admin/applications',
            'admin/finance',
            'admin/settings',
            'admin/currencies',
        ];
        
        $isWriteOperation = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
        $isCriticalRoute = collect($criticalRoutes)->some(function($route) use ($request) {
            return str_contains($request->path(), $route);
        });
        
        if ($isWriteOperation && $isCriticalRoute) {
            ActivityLog::create([
                'log_name' => 'admin_activity',
                'description' => "Admin {$request->method()} operation on {$request->path()}",
                'subject_type' => 'admin_action',
                'causer_id' => $request->user()->id,
                'causer_type' => 'App\Models\User',
                'properties' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => $response->getStatusCode(),
                    'input_data' => $this->sanitizeInputForLogging($request->all()),
                ],
            ]);
        }
    }
    
    /**
     * Log failed access attempts
     */
    private function logFailedAccess(Request $request, $response)
    {
        Log::warning('Failed access attempt', [
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'user_id' => $request->user() ? $request->user()->id : null,
        ]);
    }
    
    /**
     * Sanitize input data for logging (remove sensitive info)
     */
    private function sanitizeInputForLogging(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'bank_info'];
        
        return collect($data)->map(function ($value, $key) use ($sensitiveFields) {
            if (in_array($key, $sensitiveFields)) {
                return '[REDACTED]';
            }
            
            if (is_array($value)) {
                return $this->sanitizeInputForLogging($value);
            }
            
            return $value;
        })->toArray();
    }
}
