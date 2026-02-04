<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HealthService;

class SystemHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor PM2 services and auto-restart if they are down (Self-Healing)';

    /**
     * Execute the console command.
     */
    public function handle(HealthService $healthService)
    {
        $this->info('Starting system health check...');
        
        $healed = $healthService->monitorAndHeal();
        
        if (empty($healed)) {
            $this->info('All services are healthy.');
        } else {
            $this->warn('Healed services: ' . implode(', ', $healed));
        }
    }
}
