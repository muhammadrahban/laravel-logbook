<?php

namespace Rahban\LaravelLogbook\Services;

use Rahban\LaravelLogbook\Models\LogbookEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class LogbookService
{
    public function logRequest(Request $request, Response $response, float $responseTime): void
    {
        if (!config('logbook.enabled', true)) {
            return;
        }

        if ($this->shouldExcludeRoute($request)) {
            return;
        }

        if (!in_array($request->method(), config('logbook.included_methods', []))) {
            return;
        }

        $data = [
            'type' => 'request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $this->extractEndpoint($request),
            'status_code' => $response->getStatusCode(),
            'response_time' => $responseTime,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'token_id' => $this->extractTokenId($request),
            'request_headers' => $this->sanitizeHeaders($request->headers->all()),
            'response_headers' => $this->sanitizeHeaders($response->headers->all()),
            'request_body' => $this->processBody($request->getContent()),
            'response_body' => $this->processBody($response->getContent()),
            'metadata' => [
                'route_name' => $request->route()?->getName(),
                'controller' => $this->getControllerAction($request),
            ],
        ];

        LogbookEntry::create($data);
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
}
