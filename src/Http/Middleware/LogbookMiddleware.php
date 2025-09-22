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
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Log the request asynchronously to avoid performance impact
        if (App::environment('production')) {
            dispatch(function () use ($request, $response, $responseTime) {
                $this->logbookService->logRequest($request, $response, $responseTime);
            })->afterResponse();
        } else {
            // In non-production environments, log synchronously for debugging
            $this->logbookService->logRequest($request, $response, $responseTime);
        }

        return $response;
    }
}
