<?php

namespace App\Console\Commands;

use App\Jobs\ComputeAreaRankings;
use App\Models\AreaRanking;
use App\Models\College;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class ComputeRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rankings:compute 
                            {--period=weekly : The ranking period (weekly, monthly, quarterly, annual)}
                            {--college= : Specific college ID to compute rankings for}
                            {--sync : Run synchronously instead of queuing}
                            {--notify : Send notification when completed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute area rankings for the specified period and scope';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $collegeId = $this->option('college');
        $sync = $this->option('sync');
        $notify = $this->option('notify');

        // Validate period
        if (!in_array($period, array_keys(AreaRanking::getPeriods()))) {
            $this->error('Invalid period. Valid periods are: ' . implode(', ', array_keys(AreaRanking::getPeriods())));
            return 1;
        }

        // Validate college if provided
        if ($collegeId && !College::find($collegeId)) {
            $this->error("College with ID {$collegeId} not found.");
            return 1;
        }

        $periodName = AreaRanking::getPeriods()[$period];
        $scope = $collegeId ? "college ID {$collegeId}" : 'all colleges';

        $this->info("Starting {$periodName} rankings computation for {$scope}...");

        if ($sync) {
            // Run synchronously
            $this->runSynchronously($period, $collegeId, $notify);
        } else {
            // Queue the job
            $this->queueJob($period, $collegeId, $notify);
        }

        return 0;
    }

    /**
     * Run the ranking computation synchronously
     */
    private function runSynchronously(string $period, ?int $collegeId, bool $notify): void
    {
        $startTime = microtime(true);
        
        try {
            $this->withProgressBar(1, function () use ($period, $collegeId) {
                AreaRanking::computeRankingsForPeriod($period, $collegeId);
            });
            
            $this->newLine(2);
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $this->displayResults($period, $collegeId, $executionTime);
            
            if ($notify) {
                $this->info('Note: Notifications are only sent when running via queue.');
            }
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Rankings computation failed: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }
        }
    }

    /**
     * Queue the ranking computation job
     */
    private function queueJob(string $period, ?int $collegeId, bool $notify): void
    {
        $userId = null; // Could be set to current user if running in authenticated context
        
        $job = new ComputeAreaRankings($period, $collegeId, $userId, $notify);
        
        // Dispatch to specific queue for ranking computations
        $jobId = Queue::push($job->onQueue('rankings'));
        
        $this->info('Rankings computation job has been queued.');
        $this->line("Job ID: {$jobId}");
        $this->line('You can monitor the job progress in your queue dashboard.');
        
        if ($notify && !$userId) {
            $this->warn('Notification will not be sent as no user context is available.');
        }
    }

    /**
     * Display computation results
     */
    private function displayResults(string $period, ?int $collegeId, float $executionTime): void
    {
        $query = AreaRanking::where('ranking_period', $period)
            ->latest('computed_at');
            
        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }
        
        $rankings = $query->with(['area', 'college'])->get();
        
        $this->info("Rankings computation completed in {$executionTime} seconds.");
        $this->newLine();
        
        if ($rankings->isEmpty()) {
            $this->warn('No rankings were computed. This might indicate no areas with parameters were found.');
            return;
        }
        
        // Summary statistics
        $this->info('Computation Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Areas Ranked', $rankings->count()],
                ['Colleges Affected', $rankings->pluck('college_id')->unique()->count()],
                ['Average Completion', round($rankings->avg('completion_percentage'), 2) . '%'],
                ['Average Quality Score', round($rankings->avg('quality_score'), 2)],
                ['Average Accreditor Rating', round($rankings->avg('accreditor_rating'), 2)],
                ['Execution Time', $executionTime . ' seconds']
            ]
        );
        
        $this->newLine();
        
        // Top 10 rankings
        $topRankings = $rankings->sortBy('rank_position')->take(10);
        
        $this->info('Top 10 Rankings:');
        $this->table(
            ['Rank', 'Area', 'College', 'Score', 'Completion %', 'Quality', 'Accreditor Rating'],
            $topRankings->map(function ($ranking) {
                return [
                    $ranking->rank_position,
                    $ranking->area->name ?? 'N/A',
                    $ranking->college->name ?? 'N/A',
                    $ranking->weighted_score,
                    $ranking->completion_percentage . '%',
                    $ranking->quality_score,
                    $ranking->accreditor_rating
                ];
            })->toArray()
        );
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['period', 'p', 'optional', 'The ranking period', 'weekly'],
            ['college', 'c', 'optional', 'Specific college ID'],
            ['sync', 's', 'none', 'Run synchronously'],
            ['notify', 'n', 'none', 'Send notification when completed']
        ];
    }
}
