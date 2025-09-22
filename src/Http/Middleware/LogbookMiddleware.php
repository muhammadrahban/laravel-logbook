<?php

namespace Rahban\LaravelLogbook\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Rahban\LaravelLogbook\Services\LogbookService;

class LogbookMiddleware
{
    protected LogbookService $logbookService;

    public function __construct(LogbookService $logbookService)
    {
        $this->logbookService = $logbookService;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('logbook.enabled', true)) {
            return $next($request);
        }

        // Skip excluded routes
        $excludedRoutes = config('logbook.excluded_routes', []);
        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $startTime = microtime(true);

        // Extract user ID from Bearer token if present
        $userId = $this->extractUserIdFromToken($request);
        $tokenId = $this->extractTokenId($request);

        // Log the request start (similar to your current approach)
        $this->logRequestStart($request, $userId, $tokenId);

        $response = $next($request);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Log the complete request with response
        $this->logbookService->logRequest($request, $response, $responseTime, $userId, $tokenId);

        return $response;
    }

    protected function extractUserIdFromToken(Request $request): ?int
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        try {
            // If using Laravel Sanctum
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                return $tokenModel?->tokenable_id;
            }

            // If using Laravel Passport
            if (class_exists('\Laravel\Passport\Token')) {
                // You can implement Passport token lookup here
                // This would require more specific implementation based on your setup
            }

            // Custom token validation
            if (method_exists($this, 'validateCustomToken')) {
                return $this->validateCustomToken($token);
            }
        } catch (\Exception $e) {
            // Token validation failed, continue without user ID
        }

        return null;
    }

    protected function extractTokenId(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        // Return first 8 characters + *** for security
        return strlen($token) > 8 ? substr($token, 0, 8) . '***' : $token . '***';
    }

    protected function logRequestStart(Request $request, ?int $userId, ?string $tokenId): void
    {
        // Log similar to your current approach but in structured format
        $this->logbookService->event('api.request.start', [
            'authorization_header' => $request->header('Authorization') ? 'Bearer ***' : 'N/A',
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_headers' => $this->sanitizeHeaders($request->headers->all()),
            'request_payload' => $request->all(),
            'user_id' => $userId,
            'token_id' => $tokenId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $userId);
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $headers[$key] = ['***MASKED***'];
            }
        }

        return $headers;
    }
}
