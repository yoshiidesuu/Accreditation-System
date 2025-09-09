<?php

namespace App\Services;

use App\Models\SwotEntry;
use App\Models\AreaRanking;
use App\Models\College;
use App\Models\Area;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get SWOT data with filters and statistics
     */
    public function getSwotData(array $filters = []): array
    {
        $query = SwotEntry::with(['college', 'area', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
        }

        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $swotEntries = $query->get();

        // Generate statistics
        $stats = $this->generateSwotStats($swotEntries);

        return [
            'entries' => $swotEntries,
            'stats' => $stats,
            'filters' => $filters
        ];
    }

    /**
     * Get rankings data with filters and statistics
     */
    public function getRankingsData(array $filters = []): array
    {
        $query = AreaRanking::with(['college', 'area'])
            ->orderBy('rank', 'asc');

        // Apply filters
        if (!empty($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
        }

        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (!empty($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        $rankings = $query->get();

        // Generate statistics
        $stats = $this->generateRankingsStats($rankings);

        return [
            'rankings' => $rankings,
            'stats' => $stats,
            'filters' => $filters
        ];
    }

    /**
     * Generate SWOT statistics
     */
    private function generateSwotStats(Collection $entries): array
    {
        $stats = [
            'total' => $entries->count(),
            'by_type' => [
                'S' => $entries->where('type', 'S')->count(),
                'W' => $entries->where('type', 'W')->count(),
                'O' => $entries->where('type', 'O')->count(),
                'T' => $entries->where('type', 'T')->count(),
            ],
            'by_status' => [
                'pending' => $entries->where('status', 'pending')->count(),
                'approved' => $entries->where('status', 'approved')->count(),
                'rejected' => $entries->where('status', 'rejected')->count(),
            ],
            'by_college' => $entries->groupBy('college.name')->map->count(),
            'by_area' => $entries->groupBy('area.name')->map->count(),
            'recent_activity' => $entries->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        // Calculate approval rate
        $stats['approval_rate'] = $stats['total'] > 0 
            ? round(($stats['by_status']['approved'] / $stats['total']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Generate rankings statistics
     */
    private function generateRankingsStats(Collection $rankings): array
    {
        $stats = [
            'total_areas' => $rankings->count(),
            'avg_completion' => $rankings->avg('completion_percentage') ?? 0,
            'avg_quality' => $rankings->avg('quality_score') ?? 0,
            'avg_rating' => $rankings->avg('accreditor_rating') ?? 0,
            'avg_final_score' => $rankings->avg('final_score') ?? 0,
            'by_college' => $rankings->groupBy('college.name')->map->count(),
            'top_performers' => $rankings->take(5),
            'bottom_performers' => $rankings->sortByDesc('rank')->take(5),
            'score_distribution' => [
                'excellent' => $rankings->where('final_score', '>=', 90)->count(),
                'good' => $rankings->whereBetween('final_score', [80, 89.99])->count(),
                'satisfactory' => $rankings->whereBetween('final_score', [70, 79.99])->count(),
                'needs_improvement' => $rankings->where('final_score', '<', 70)->count(),
            ],
        ];

        return $stats;
    }

    /**
     * Get report generation statistics
     */
    public function getReportStats(): array
    {
        return [
            'total_swot_entries' => SwotEntry::count(),
            'total_rankings' => AreaRanking::count(),
            'total_colleges' => College::count(),
            'total_areas' => Area::count(),
            'recent_swot' => SwotEntry::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'pending_approvals' => SwotEntry::where('status', 'pending')->count(),
            'last_ranking_update' => AreaRanking::latest('updated_at')->value('updated_at'),
        ];
    }

    /**
     * Prepare data for CSV export
     */
    public function prepareSwotCsvData(Collection $entries): array
    {
        return $entries->map(function ($entry) {
            return [
                'ID' => $entry->id,
                'College' => $entry->college->name,
                'Area' => $entry->area->name,
                'Type' => $entry->type_label,
                'Description' => $entry->description,
                'Status' => ucfirst($entry->status),
                'Created By' => $entry->createdBy->name ?? 'Unknown',
                'Created Date' => $entry->created_at->format('Y-m-d H:i:s'),
                'Updated Date' => $entry->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Prepare data for rankings CSV export
     */
    public function prepareRankingsCsvData(Collection $rankings): array
    {
        return $rankings->map(function ($ranking) {
            return [
                'Rank' => $ranking->rank,
                'College' => $ranking->college->name,
                'Area' => $ranking->area->name,
                'Period' => ucfirst($ranking->period),
                'Completion %' => number_format($ranking->completion_percentage, 2),
                'Quality Score' => number_format($ranking->quality_score, 2),
                'Accreditor Rating' => number_format($ranking->accreditor_rating, 2),
                'Final Score' => number_format($ranking->final_score, 2),
                'Academic Year' => $ranking->academicYear->name ?? 'N/A',
                'Last Updated' => $ranking->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get CSV headers for SWOT export
     */
    public function getSwotCsvHeaders(): array
    {
        return [
            'ID', 'College', 'Area', 'Type', 'Description', 'Status', 
            'Created By', 'Created Date', 'Updated Date'
        ];
    }

    /**
     * Get CSV headers for rankings export
     */
    public function getRankingsCsvHeaders(): array
    {
        return [
            'Rank', 'College', 'Area', 'Period', 'Completion %', 
            'Quality Score', 'Accreditor Rating', 'Final Score', 
            'Academic Year', 'Last Updated'
        ];
    }

    /**
     * Validate report filters
     */
    public function validateFilters(array $filters, string $type): array
    {
        $errors = [];

        if ($type === 'swot') {
            if (isset($filters['type']) && !in_array($filters['type'], ['S', 'W', 'O', 'T'])) {
                $errors[] = 'Invalid SWOT type specified.';
            }

            if (isset($filters['status']) && !in_array($filters['status'], ['pending', 'approved', 'rejected'])) {
                $errors[] = 'Invalid status specified.';
            }
        }

        if ($type === 'rankings') {
            if (isset($filters['period']) && !in_array($filters['period'], ['weekly', 'monthly', 'quarterly', 'annual'])) {
                $errors[] = 'Invalid period specified.';
            }
        }

        // Common validations
        if (isset($filters['college_id']) && !College::find($filters['college_id'])) {
            $errors[] = 'Invalid college specified.';
        }

        if (isset($filters['area_id']) && !Area::find($filters['area_id'])) {
            $errors[] = 'Invalid area specified.';
        }

        if (isset($filters['date_from']) && !Carbon::parse($filters['date_from'])) {
            $errors[] = 'Invalid start date format.';
        }

        if (isset($filters['date_to']) && !Carbon::parse($filters['date_to'])) {
            $errors[] = 'Invalid end date format.';
        }

        return $errors;
    }
}