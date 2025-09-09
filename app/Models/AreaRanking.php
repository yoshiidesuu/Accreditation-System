<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AreaRanking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'area_id',
        'college_id',
        'ranking_period',
        'completion_percentage',
        'quality_score',
        'accreditor_rating',
        'weighted_score',
        'rank_position',
        'total_parameters',
        'completed_parameters',
        'approved_swot_count',
        'rejected_swot_count',
        'computed_at',
        'computed_by'
    ];

    protected $casts = [
        'completion_percentage' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'accreditor_rating' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'computed_at' => 'datetime'
    ];

    // Ranking weights configuration
    const COMPLETION_WEIGHT = 0.4; // 40%
    const QUALITY_WEIGHT = 0.35;   // 35%
    const ACCREDITOR_WEIGHT = 0.25; // 25%

    // Ranking periods
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_ANNUAL = 'annual';

    public static function getPeriods(): array
    {
        return [
            self::PERIOD_WEEKLY => 'Weekly',
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_QUARTERLY => 'Quarterly',
            self::PERIOD_ANNUAL => 'Annual'
        ];
    }

    public function getPeriodName(): string
    {
        return self::getPeriods()[$this->ranking_period] ?? 'Unknown';
    }

    /**
     * Relationships
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function computedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'computed_by');
    }

    /**
     * Scopes
     */
    public function scopeByPeriod($query, $period)
    {
        return $query->where('ranking_period', $period);
    }

    public function scopeByCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('computed_at', 'desc');
    }

    public function scopeTopRanked($query, $limit = 10)
    {
        return $query->orderBy('rank_position', 'asc')->limit($limit);
    }

    /**
     * Static methods for ranking computation
     */
    public static function computeRankingsForPeriod(string $period, ?int $collegeId = null): void
    {
        $areas = Area::query()
            ->when($collegeId, fn($q) => $q->where('college_id', $collegeId))
            ->with(['college', 'parameters'])
            ->get();

        $rankings = [];

        foreach ($areas as $area) {
            $ranking = self::computeAreaRanking($area, $period);
            if ($ranking) {
                $rankings[] = $ranking;
            }
        }

        // Sort by weighted score and assign ranks
        usort($rankings, fn($a, $b) => $b['weighted_score'] <=> $a['weighted_score']);

        DB::transaction(function () use ($rankings, $period, $collegeId) {
            // Delete existing rankings for this period
            $query = self::where('ranking_period', $period);
            if ($collegeId) {
                $query->where('college_id', $collegeId);
            }
            $query->delete();

            // Insert new rankings with positions
            foreach ($rankings as $index => $ranking) {
                $ranking['rank_position'] = $index + 1;
                self::create($ranking);
            }
        });
    }

    private static function computeAreaRanking(Area $area, string $period): ?array
    {
        // Get parameter completion data
        $totalParameters = $area->parameters()->count();
        if ($totalParameters === 0) {
            return null; // Skip areas with no parameters
        }

        $completedParameters = $area->parameters()
            ->whereHas('parameterContents', function ($query) {
                $query->whereNotNull('content')
                      ->where('content', '!=', '');
            })
            ->count();

        $completionPercentage = ($completedParameters / $totalParameters) * 100;

        // Get quality score from parameter content quality
        $qualityScore = self::calculateQualityScore($area);

        // Get accreditor rating from SWOT reviews
        $accreditorRating = self::calculateAccreditorRating($area);

        // Get SWOT statistics
        $swotStats = self::getSwotStatistics($area);

        // Calculate weighted score
        $weightedScore = (
            ($completionPercentage * self::COMPLETION_WEIGHT) +
            ($qualityScore * self::QUALITY_WEIGHT) +
            ($accreditorRating * self::ACCREDITOR_WEIGHT)
        );

        return [
            'area_id' => $area->id,
            'college_id' => $area->college_id,
            'ranking_period' => $period,
            'completion_percentage' => round($completionPercentage, 2),
            'quality_score' => round($qualityScore, 2),
            'accreditor_rating' => round($accreditorRating, 2),
            'weighted_score' => round($weightedScore, 2),
            'total_parameters' => $totalParameters,
            'completed_parameters' => $completedParameters,
            'approved_swot_count' => $swotStats['approved'],
            'rejected_swot_count' => $swotStats['rejected'],
            'computed_at' => now(),
            'computed_by' => auth()->id()
        ];
    }

    private static function calculateQualityScore(Area $area): float
    {
        // Quality score based on parameter content length and completeness
        $parameterContents = DB::table('parameter_contents')
            ->join('parameters', 'parameter_contents.parameter_id', '=', 'parameters.id')
            ->where('parameters.area_id', $area->id)
            ->whereNotNull('parameter_contents.content')
            ->where('parameter_contents.content', '!=', '')
            ->pluck('parameter_contents.content');

        if ($parameterContents->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($parameterContents as $content) {
            $contentLength = strlen(trim($content));
            
            // Score based on content length (0-100)
            if ($contentLength >= 500) {
                $score = 100;
            } elseif ($contentLength >= 200) {
                $score = 80;
            } elseif ($contentLength >= 100) {
                $score = 60;
            } elseif ($contentLength >= 50) {
                $score = 40;
            } else {
                $score = 20;
            }

            $totalScore += $score;
            $count++;
        }

        return $count > 0 ? $totalScore / $count : 0;
    }

    private static function calculateAccreditorRating(Area $area): float
    {
        // Rating based on SWOT entry approval rates and feedback
        $swotEntries = SwotEntry::where('area_id', $area->id)
            ->whereIn('status', [SwotEntry::STATUS_APPROVED, SwotEntry::STATUS_REJECTED])
            ->get();

        if ($swotEntries->isEmpty()) {
            return 50; // Neutral score if no reviews yet
        }

        $approvedCount = $swotEntries->where('status', SwotEntry::STATUS_APPROVED)->count();
        $totalReviewed = $swotEntries->count();
        
        // Base score from approval rate (0-100)
        $approvalRate = ($approvedCount / $totalReviewed) * 100;
        
        // Bonus for having constructive feedback (approved with notes)
        $constructiveFeedback = $swotEntries
            ->where('status', SwotEntry::STATUS_APPROVED)
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->count();
        
        $feedbackBonus = $constructiveFeedback > 0 ? min(10, $constructiveFeedback * 2) : 0;
        
        return min(100, $approvalRate + $feedbackBonus);
    }

    private static function getSwotStatistics(Area $area): array
    {
        return [
            'approved' => SwotEntry::where('area_id', $area->id)
                ->where('status', SwotEntry::STATUS_APPROVED)
                ->count(),
            'rejected' => SwotEntry::where('area_id', $area->id)
                ->where('status', SwotEntry::STATUS_REJECTED)
                ->count()
        ];
    }

    /**
     * Get ranking trends for an area
     */
    public static function getRankingTrends(int $areaId, int $limit = 12): array
    {
        return self::where('area_id', $areaId)
            ->orderBy('computed_at', 'desc')
            ->limit($limit)
            ->get(['ranking_period', 'rank_position', 'weighted_score', 'computed_at'])
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Get college comparison data
     */
    public static function getCollegeComparison(string $period): array
    {
        return self::select(
            'college_id',
            DB::raw('AVG(rank_position) as avg_rank'),
            DB::raw('AVG(weighted_score) as avg_score'),
            DB::raw('COUNT(*) as area_count')
        )
        ->where('ranking_period', $period)
        ->with('college:id,name')
        ->groupBy('college_id')
        ->orderBy('avg_score', 'desc')
        ->get()
        ->toArray();
    }

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rank_position', 'weighted_score', 'completion_percentage'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
