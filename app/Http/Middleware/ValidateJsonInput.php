<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ValidateJsonInput
{
    /**
     * Handle an incoming request to prevent malicious input
     */
    public function handle(Request $request, Closure $next)
    {
        // Sanitize input data
        $this->sanitizeRequest($request);
        
        // Check for suspicious patterns
        if ($this->containsSuspiciousContent($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entrada inválida detectada.'
            ], 400);
        }
        
        return $next($request);
    }
    
    /**
     * Sanitize request input
     */
    private function sanitizeRequest(Request $request)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters
                $value = strip_tags($value);
                $value = trim($value);
                
                // Limit string length to prevent DoS
                if (strlen($value) > 10000) {
                    $value = substr($value, 0, 10000);
                }
            }
        });
        
        $request->replace($input);
    }
    
    /**
     * Check for suspicious content patterns
     */
    private function containsSuspiciousContent(Request $request): bool
    {
        $suspiciousPatterns = [
            '/(<script[^>]*>.*?<\/script>)/is', // Script tags
            '/(javascript\s*:)/i',              // JavaScript URLs
            '/(on\w+\s*=)/i',                  // Event handlers
            '/(\bUNION\b.*\bSELECT\b)/i',      // SQL injection
            '/(\bDROP\b.*\bTABLE\b)/i',        // SQL DROP
            '/(\bINSERT\b.*\bINTO\b)/i',       // SQL INSERT
            '/(\bDELETE\b.*\bFROM\b)/i',       // SQL DELETE
        ];
        
        $input = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                \Log::warning('Suspicious input detected', [
                    'pattern' => $pattern,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url()
                ]);
                return true;
            }
        }
        
        return false;
    }
}
