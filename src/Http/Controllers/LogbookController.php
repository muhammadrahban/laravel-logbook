<?php

namespace Rahban\LaravelLogbook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rahban\LaravelLogbook\Models\LogbookEntry;
use Rahban\LaravelLogbook\Services\LogbookService;

class LogbookController extends Controller
{
    protected LogbookService $logbookService;

    public function __construct(LogbookService $logbookService)
    {
        $this->logbookService = $logbookService;
    }

    public function dashboard()
    {
        $stats = $this->logbookService->getStats();

        // Recent activity (last 24 hours)
        $recentEntries = LogbookEntry::where('created_at', '>=', now()->subDay())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Status code distribution
        $statusCodes = LogbookEntry::requests()
            ->selectRaw('
                CASE 
                    WHEN status_code >= 200 AND status_code < 300 THEN "2xx"
                    WHEN status_code >= 300 AND status_code < 400 THEN "3xx" 
                    WHEN status_code >= 400 AND status_code < 500 THEN "4xx"
                    WHEN status_code >= 500 THEN "5xx"
                    ELSE "Other"
                END as status_group,
                count(*) as count
            ')
            ->groupBy('status_group')
            ->get();

        return view('logbook::dashboard', compact('stats', 'recentEntries', 'statusCodes'));
    }

    public function tracks(Request $request)
    {
        $query = LogbookEntry::query()->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status_code', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('endpoint')) {
            $query->byEndpoint($request->endpoint);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->dateRange($request->from, $request->to);
        }

        $entries = $query->paginate(50)->withQueryString();

        // Get filter options
        $methods = LogbookEntry::requests()
            ->distinct()
            ->pluck('method')
            ->filter()
            ->sort();

        $statusCodes = LogbookEntry::requests()
            ->distinct()
            ->pluck('status_code')
            ->filter()
            ->sort();

        return view('logbook::tracks', compact('entries', 'methods', 'statusCodes'));
    }

    public function show($id)
    {
        $entry = LogbookEntry::findOrFail($id);

        return view('logbook::show', compact('entry'));
    }
}
