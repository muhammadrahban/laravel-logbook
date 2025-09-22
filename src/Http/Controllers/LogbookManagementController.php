<?php

namespace Rahban\LaravelLogbook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rahban\LaravelLogbook\Services\LogbookService;
use Rahban\LaravelLogbook\Models\LogbookEntry;

class LogbookManagementController extends Controller
{
    protected LogbookService $logbookService;

    public function __construct(LogbookService $logbookService)
    {
        $this->logbookService = $logbookService;
    }

    public function index()
    {
        $stats = $this->logbookService->getStats();

        // Storage usage
        $totalEntries = LogbookEntry::count();
        $retentionDays = config('logbook.retention_days', 90);
        $oldEntries = LogbookEntry::olderThan($retentionDays)->count();

        // Size estimation (rough calculation)
        $avgSizePerEntry = 2; // KB estimate
        $totalSizeKb = $totalEntries * $avgSizePerEntry;

        return view('logbook::manage', compact(
            'stats',
            'totalEntries',
            'oldEntries',
            'retentionDays',
            'totalSizeKb'
        ));
    }

    public function cleanupByDays(Request $request, int $days)
    {
        $deleted = $this->logbookService->cleanup($days);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} entries older than {$days} days.",
            'deleted' => $deleted
        ]);
    }

    public function cleanupByRange(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from'
        ]);

        $deleted = $this->logbookService->cleanupByDateRange(
            $request->from,
            $request->to
        );

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} entries between {$request->from} and {$request->to}.",
            'deleted' => $deleted
        ]);
    }

    public function cleanupAll()
    {
        $deleted = $this->logbookService->cleanupAll();

        return response()->json([
            'success' => true,
            'message' => "Deleted all {$deleted} entries.",
            'deleted' => $deleted
        ]);
    }
}
