<?php

namespace Rahban\LaravelLogbook\Console\Commands;

use Illuminate\Console\Command;
use Rahban\LaravelLogbook\Services\LogbookService;

class LogbookCleanupCommand extends Command
{
    protected $signature = 'logbook:cleanup 
                          {--days= : Delete logs older than specified days}
                          {--from= : Delete logs from this date (Y-m-d format)}
                          {--to= : Delete logs to this date (Y-m-d format)}
                          {--all : Delete all logs}
                          {--force : Skip confirmation}';

    protected $description = 'Clean up old logbook entries';

    protected LogbookService $logbookService;

    public function __construct(LogbookService $logbookService)
    {
        parent::__construct();
        $this->logbookService = $logbookService;
    }

    public function handle()
    {
        $days = $this->option('days');
        $from = $this->option('from');
        $to = $this->option('to');
        $all = $this->option('all');
        $force = $this->option('force');

        if ($all) {
            return $this->cleanupAll($force);
        }

        if ($from && $to) {
            return $this->cleanupByDateRange($from, $to, $force);
        }

        if ($days) {
            return $this->cleanupByDays((int) $days, $force);
        }

        // Default: use retention days from config
        $retentionDays = config('logbook.retention_days', 90);
        return $this->cleanupByDays($retentionDays, $force);
    }

    protected function cleanupByDays(int $days, bool $force): int
    {
        if (!$force && !$this->confirm("Delete all logbook entries older than {$days} days?")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $deleted = $this->logbookService->cleanup($days);
        $this->info("Deleted {$deleted} logbook entries older than {$days} days.");

        return 0;
    }

    protected function cleanupByDateRange(string $from, string $to, bool $force): int
    {
        if (!$force && !$this->confirm("Delete all logbook entries between {$from} and {$to}?")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $deleted = $this->logbookService->cleanupByDateRange($from, $to);
        $this->info("Deleted {$deleted} logbook entries between {$from} and {$to}.");

        return 0;
    }

    protected function cleanupAll(bool $force): int
    {
        if (!$force && !$this->confirm('Delete ALL logbook entries? This action cannot be undone!')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $deleted = $this->logbookService->cleanupAll();
        $this->info("Deleted {$deleted} logbook entries.");

        return 0;
    }
}
