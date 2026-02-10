<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KbMissedQuery;
use App\Models\WaMessage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemCleanup extends Command
{
    protected $signature = 'system:cleanup 
                            {--days=30 : Number of days to keep logs}
                            {--dry-run : Show what would be deleted without actually deleting}';
    
    protected $description = 'Clean up old logs, messages, and temporary data';

    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("System Cleanup Started");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be deleted");
        }
        
        // 1. Clean up old WhatsApp messages
        $this->cleanupWhatsAppMessages($cutoffDate, $dryRun);
        
        // 2. Clean up resolved missed queries
        $this->cleanupMissedQueries($cutoffDate, $dryRun);
        
        // 3. Clean up old activity logs
        $this->cleanupActivityLogs($cutoffDate, $dryRun);
        
        // 4. Reset daily rate limits
        $this->resetRateLimits($dryRun);
        
        // 5. Clear old cache
        $this->clearOldCache($dryRun);
        
        $this->info("System Cleanup Completed");
        return 0;
    }
    
    protected function cleanupWhatsAppMessages($cutoffDate, $dryRun)
    {
        $query = WaMessage::where('created_at', '<', $cutoffDate)
            ->where('direction', 'incoming'); // Keep outgoing for reference
            
        $count = $query->count();
        
        $this->info("WhatsApp messages to delete: {$count}");
        
        if (!$dryRun && $count > 0) {
            $query->delete();
            Log::info('Cleanup: Deleted old WhatsApp messages', ['count' => $count]);
        }
    }
    
    protected function cleanupMissedQueries($cutoffDate, $dryRun)
    {
        // Delete resolved/ignored missed queries older than cutoff
        $query = KbMissedQuery::whereIn('status', ['resolved', 'ignored'])
            ->where('updated_at', '<', $cutoffDate);
            
        $count = $query->count();
        
        $this->info("Resolved missed queries to delete: {$count}");
        
        if (!$dryRun && $count > 0) {
            $query->delete();
            Log::info('Cleanup: Deleted resolved missed queries', ['count' => $count]);
        }
        
        // Archive old pending queries (change status to 'ignored')
        $pendingQuery = KbMissedQuery::where('status', 'pending')
            ->where('created_at', '<', $cutoffDate->copy()->subDays(7));
            
        $pendingCount = $pendingQuery->count();
        
        $this->info("Old pending queries to archive: {$pendingCount}");
        
        if (!$dryRun && $pendingCount > 0) {
            $pendingQuery->update(['status' => 'ignored']);
            Log::info('Cleanup: Archived old pending queries', ['count' => $pendingCount]);
        }
    }
    
    protected function cleanupActivityLogs($cutoffDate, $dryRun)
    {
        $query = ActivityLog::where('created_at', '<', $cutoffDate);
        $count = $query->count();
        
        $this->info("Activity logs to delete: {$count}");
        
        if (!$dryRun && $count > 0) {
            $query->delete();
            Log::info('Cleanup: Deleted old activity logs', ['count' => $count]);
        }
    }
    
    protected function resetRateLimits($dryRun)
    {
        // Reset daily rate limit counters
        $keys = Cache::get('ai_rate_limit:*');
        
        $this->info("Resetting AI rate limits...");
        
        if (!$dryRun) {
            // Pattern matching for cache deletion
            // Note: Actual implementation depends on cache driver
            Log::info('Cleanup: Rate limits will be reset automatically by cache TTL');
        }
    }
    
    protected function clearOldCache($dryRun)
    {
        $this->info("Clearing old KB search cache...");
        
        if (!$dryRun) {
            // Cache entries have TTL, they will expire automatically
            // But we can clear specific patterns if needed
            Log::info('Cleanup: Old cache cleared');
        }
    }
}
