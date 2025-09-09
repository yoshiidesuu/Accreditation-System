<?php

namespace App\Jobs;

use App\Models\AreaRanking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RankingComputationCompleted;
use Exception;

class ComputeAreaRankings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Retry delays in seconds

    protected string $period;
    protected ?int $collegeId;
    protected ?int $userId;
    protected bool $notifyOnCompletion;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $period = AreaRanking::PERIOD_WEEKLY,
        ?int $collegeId = null,
        ?int $userId = null,
        bool $notifyOnCompletion = true
    ) {
        $this->period = $period;
        $this->collegeId = $collegeId;
        $this->userId = $userId;
        $this->notifyOnCompletion = $notifyOnCompletion;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting area rankings computation', [
                'period' => $this->period,
                'college_id' => $this->collegeId,
                'user_id' => $this->userId
            ]);

            $startTime = microtime(true);

            // Compute rankings
            AreaRanking::computeRankingsForPeriod($this->period, $this->collegeId);

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            // Get computation statistics
            $stats = $this->getComputationStats();

            Log::info('Area rankings computation completed', [
                'period' => $this->period,
                'college_id' => $this->collegeId,
                'execution_time' => $executionTime,
                'stats' => $stats
            ]);

            // Send notification if requested
            if ($this->notifyOnCompletion && $this->userId) {
                $user = User::find($this->userId);
                if ($user) {
                    $user->notify(new RankingComputationCompleted(
                        $this->period,
                        $this->collegeId,
                        $stats,
                        $executionTime
                    ));
                }
            }

        } catch (Exception $e) {
            Log::error('Area rankings computation failed', [
                'period' => $this->period,
                'college_id' => $this->collegeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('Area rankings computation job failed permanently', [
            'period' => $this->period,
            'college_id' => $this->collegeId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        // Notify user of failure if applicable
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                // You can create a RankingComputationFailed notification
                // $user->notify(new RankingComputationFailed($this->period, $exception->getMessage()));
            }
        }
    }

    /**
     * Get computation statistics
     */
    private function getComputationStats(): array
    {
        $query = AreaRanking::where('ranking_period', $this->period);
        
        if ($this->collegeId) {
            $query->where('college_id', $this->collegeId);
        }

        $rankings = $query->latest('computed_at')->get();

        return [
            'total_areas_ranked' => $rankings->count(),
            'colleges_affected' => $rankings->pluck('college_id')->unique()->count(),
            'average_completion' => round($rankings->avg('completion_percentage'), 2),
            'average_quality' => round($rankings->avg('quality_score'), 2),
            'average_accreditor_rating' => round($rankings->avg('accreditor_rating'), 2),
            'top_ranked_area' => $rankings->where('rank_position', 1)->first()?->area?->name,
            'computation_timestamp' => now()->toISOString()
        ];
    }

    /**
     * Get unique job identifier
     */
    public function uniqueId(): string
    {
        return 'compute-rankings-' . $this->period . '-' . ($this->collegeId ?? 'all');
    }

    /**
     * Determine if job should be unique
     */
    public function shouldBeUnique(): bool
    {
        return true; // Prevent duplicate ranking computations
    }

    /**
     * Get tags for monitoring
     */
    public function tags(): array
    {
        return [
            'ranking-computation',
            'period:' . $this->period,
            $this->collegeId ? 'college:' . $this->collegeId : 'all-colleges'
        ];
    }
}
