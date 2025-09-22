<?php

namespace Rahban\LaravelLogbook\Services;

use Rahban\LaravelLogbook\Models\LogbookEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class LogbookService
{
    protected function isEnabled(): bool
    {
        return config('logbook.enabled', true);
    }

    public function logRequest(
        Request $request,
        $response,
        float $responseTime,
        ?int $userId = null,
        ?string $tokenId = null
    ): void {
        if (!$this->isEnabled()) {
            return;
        }

        // Use provided userId or try to get from authenticated user
        $finalUserId = $userId ?? ($request->user()?->id);

        $data = [
            'type' => 'request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $this->getEndpoint($request),
            'status_code' => $response->getStatusCode(),
            'response_time' => $responseTime,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $finalUserId,
            'token_id' => $tokenId,
            'request_headers' => $this->maskSensitiveData($request->headers->all()),
            'response_headers' => $this->maskSensitiveData($response->headers->all()),
            'request_body' => $this->formatRequestBody($request),
            'response_body' => $this->formatResponseBody($response),
            'metadata' => [
                'route_name' => $request->route()?->getName(),
                'action' => $request->route()?->getActionName(),
                'middleware' => $request->route()?->middleware() ?? [],
                'has_auth_token' => $request->bearerToken() !== null,
                'is_authenticated' => $finalUserId !== null,
            ],
        ];

        $this->store($data);
    }

    public function event(string $eventName, array $data = [], ?int $userId = null): void
    {
        if (!config('logbook.enabled', true)) {
            return;
        }

        LogbookEntry::create([
            'type' => 'event',
            'event_name' => $eventName,
            'event_data' => $data,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected function shouldExcludeRoute(Request $request): bool
    {
        $excludedRoutes = config('logbook.excluded_routes', []);
        $currentPath = $request->path();

        foreach ($excludedRoutes as $pattern) {
            if (Str::is($pattern, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    protected function extractEndpoint(Request $request): string
    {
        $route = $request->route();

        if ($route) {
            return $route->uri();
        }

        // Fallback to path if route is not available
        return $request->path();
    }

    protected function extractTokenId(Request $request): ?string
    {
        $token = $request->bearerToken();

        if ($token) {
            // Return only first 8 characters for privacy
            return substr($token, 0, 8) . '***';
        }

        return null;
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***MASKED***'];
            }
        }

        return $headers;
    }

    protected function processBody(?string $body): ?string
    {
        if (empty($body)) {
            return null;
        }

        // Try to decode JSON and mask sensitive fields
        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $decoded = $this->maskSensitiveData($decoded);
            $body = json_encode($decoded);
        }

        // Truncate if too large
        $maxLength = config('logbook.truncate_body_at', 10240);
        if (strlen($body) > $maxLength) {
            return substr($body, 0, $maxLength) . '... [TRUNCATED]';
        }

        return $body;
    }

    protected function maskSensitiveData(array $data): array
    {
        $maskFields = config('logbook.mask_fields', []);

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), array_map('strtolower', $maskFields))) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }

    protected function getControllerAction(Request $request): ?string
    {
        $route = $request->route();

        if ($route && $route->getAction('controller')) {
            return $route->getAction('controller');
        }

        return null;
    }

    public function cleanup(int $days): int
    {
        return LogbookEntry::olderThan($days)->delete();
    }

    public function cleanupByDateRange(string $from, string $to): int
    {
        return LogbookEntry::dateRange($from, $to)->delete();
    }

    public function cleanupAll(): int
    {
        return LogbookEntry::query()->delete();
    }

    public function getStats(): array
    {
        $totalRequests = LogbookEntry::requests()->count();
        $totalEvents = LogbookEntry::events()->count();

        $errorRate = $totalRequests > 0
            ? (LogbookEntry::requests()->where('status_code', '>=', 400)->count() / $totalRequests) * 100
            : 0;

        $avgResponseTime = LogbookEntry::requests()
            ->whereNotNull('response_time')
            ->avg('response_time');

        $topEndpoints = LogbookEntry::requests()
            ->selectRaw('endpoint, count(*) as count')
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'total_requests' => $totalRequests,
            'total_events' => $totalEvents,
            'error_rate' => round($errorRate, 2),
            'avg_response_time' => $avgResponseTime ? round($avgResponseTime, 2) : 0,
            'top_endpoints' => $topEndpoints,
        ];
    }

    protected function getEndpoint(Request $request): string
    {
        $route = $request->route();

        if ($route) {
            return $route->uri();
        }

        // Fallback to path if route is not available
        return $request->path();
    }

    protected function formatRequestBody(Request $request): ?string
    {
        $body = $request->getContent();
        return $this->processBody($body);
    }

    protected function formatResponseBody($response): ?string
    {
        if (method_exists($response, 'getContent')) {
            return $this->processBody($response->getContent());
        }

        return null;
    }

    protected function store(array $data): void
    {
        LogbookEntry::create($data);
    }
}
