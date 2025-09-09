<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AreaRanking;
use App\Models\SwotEntry;
use App\Models\College;
use App\Models\Area;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReportController extends Controller
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['action', 'report_type', 'filters'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Display the reports dashboard
     */
    public function index()
    {
        $colleges = College::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        
        return view('reports.index', compact('colleges', 'areas'));
    }

    /**
     * Generate ranking report in PDF format
     */
    public function rankingsPdf(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|in:current,previous,all',
            'college_id' => 'nullable|exists:colleges,id',
            'area_id' => 'nullable|exists:areas,id',
            'format' => 'required|string|in:summary,detailed'
        ]);

        $query = AreaRanking::with(['area', 'college'])
            ->orderBy('rank_position')
            ->orderBy('weighted_score', 'desc');

        // Apply filters
        if ($validated['college_id']) {
            $query->where('college_id', $validated['college_id']);
        }
        
        if ($validated['area_id']) {
            $query->where('area_id', $validated['area_id']);
        }

        if ($validated['period'] !== 'all') {
            $currentPeriod = now()->format('Y-m');
            $previousPeriod = now()->subMonth()->format('Y-m');
            
            $period = $validated['period'] === 'current' ? $currentPeriod : $previousPeriod;
            $query->where('ranking_period', $period);
        }

        $rankings = $query->get();
        $college = $validated['college_id'] ? College::find($validated['college_id']) : null;
        $area = $validated['area_id'] ? Area::find($validated['area_id']) : null;

        $data = [
            'rankings' => $rankings,
            'college' => $college,
            'area' => $area,
            'period' => $validated['period'],
            'format' => $validated['format'],
            'generated_at' => now(),
            'generated_by' => auth()->user()->name
        ];

        // Log activity
        activity()
            ->withProperties([
                'action' => 'export_rankings_pdf',
                'report_type' => 'rankings',
                'filters' => $validated
            ])
            ->log('Generated rankings PDF report');

        $pdf = Pdf::loadView('reports.rankings-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true
            ]);

        $filename = 'rankings-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate ranking report in CSV format
     */
    public function rankingsCsv(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|in:current,previous,all',
            'college_id' => 'nullable|exists:colleges,id',
            'area_id' => 'nullable|exists:areas,id'
        ]);

        $query = AreaRanking::with(['area', 'college'])
            ->orderBy('rank_position')
            ->orderBy('weighted_score', 'desc');

        // Apply same filters as PDF
        if ($validated['college_id']) {
            $query->where('college_id', $validated['college_id']);
        }
        
        if ($validated['area_id']) {
            $query->where('area_id', $validated['area_id']);
        }

        if ($validated['period'] !== 'all') {
            $currentPeriod = now()->format('Y-m');
            $previousPeriod = now()->subMonth()->format('Y-m');
            
            $period = $validated['period'] === 'current' ? $currentPeriod : $previousPeriod;
            $query->where('ranking_period', $period);
        }

        $rankings = $query->get();

        // Log activity
        activity()
            ->withProperties([
                'action' => 'export_rankings_csv',
                'report_type' => 'rankings',
                'filters' => $validated
            ])
            ->log('Generated rankings CSV report');

        $filename = 'rankings-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($rankings) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Rank Position',
                'College',
                'Area',
                'Completion Percentage',
                'Quality Score',
                'Accreditor Rating',
                'Weighted Score',
                'Total Parameters',
                'Completed Parameters',
                'Approved SWOT',
                'Rejected SWOT',
                'Ranking Period',
                'Computed At'
            ]);

            // Data rows
            foreach ($rankings as $ranking) {
                fputcsv($file, [
                    $ranking->rank_position,
                    $ranking->college->name,
                    $ranking->area->name,
                    number_format($ranking->completion_percentage, 2) . '%',
                    number_format($ranking->quality_score, 2),
                    number_format($ranking->accreditor_rating, 2),
                    number_format($ranking->weighted_score, 2),
                    $ranking->total_parameters,
                    $ranking->completed_parameters,
                    $ranking->approved_swot_count,
                    $ranking->rejected_swot_count,
                    $ranking->ranking_period,
                    $ranking->computed_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate SWOT report in PDF format
     */
    public function swotPdf(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'nullable|exists:colleges,id',
            'area_id' => 'nullable|exists:areas,id',
            'type' => 'nullable|string|in:S,W,O,T',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'format' => 'required|string|in:summary,detailed'
        ]);

        $query = SwotEntry::with(['college', 'area', 'creator', 'reviewer'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($validated['college_id']) {
            $query->where('college_id', $validated['college_id']);
        }
        
        if ($validated['area_id']) {
            $query->where('area_id', $validated['area_id']);
        }

        if ($validated['type']) {
            $query->where('type', $validated['type']);
        }

        if ($validated['status']) {
            $query->where('status', $validated['status']);
        }

        if ($validated['date_from']) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to']) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $swotEntries = $query->get();
        $college = $validated['college_id'] ? College::find($validated['college_id']) : null;
        $area = $validated['area_id'] ? Area::find($validated['area_id']) : null;

        // Generate statistics
        $stats = [
            'total' => $swotEntries->count(),
            'by_type' => $swotEntries->groupBy('type')->map->count(),
            'by_status' => $swotEntries->groupBy('status')->map->count(),
            'by_college' => $swotEntries->groupBy('college.name')->map->count(),
        ];

        $data = [
            'swotEntries' => $swotEntries,
            'college' => $college,
            'area' => $area,
            'filters' => $validated,
            'stats' => $stats,
            'format' => $validated['format'],
            'generated_at' => now(),
            'generated_by' => auth()->user()->name
        ];

        // Log activity
        activity()
            ->withProperties([
                'action' => 'export_swot_pdf',
                'report_type' => 'swot',
                'filters' => $validated
            ])
            ->log('Generated SWOT PDF report');

        $pdf = Pdf::loadView('reports.swot-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true
            ]);

        $filename = 'swot-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate SWOT report in CSV format
     */
    public function swotCsv(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'nullable|exists:colleges,id',
            'area_id' => 'nullable|exists:areas,id',
            'type' => 'nullable|string|in:S,W,O,T',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        $query = SwotEntry::with(['college', 'area', 'creator', 'reviewer'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as PDF
        if ($validated['college_id']) {
            $query->where('college_id', $validated['college_id']);
        }
        
        if ($validated['area_id']) {
            $query->where('area_id', $validated['area_id']);
        }

        if ($validated['type']) {
            $query->where('type', $validated['type']);
        }

        if ($validated['status']) {
            $query->where('status', $validated['status']);
        }

        if ($validated['date_from']) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to']) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $swotEntries = $query->get();

        // Log activity
        activity()
            ->withProperties([
                'action' => 'export_swot_csv',
                'report_type' => 'swot',
                'filters' => $validated
            ])
            ->log('Generated SWOT CSV report');

        $filename = 'swot-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($swotEntries) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'College',
                'Area',
                'Type',
                'Description',
                'Status',
                'Created By',
                'Reviewed By',
                'Review Notes',
                'Created At',
                'Updated At',
                'Reviewed At'
            ]);

            // Data rows
            foreach ($swotEntries as $entry) {
                fputcsv($file, [
                    $entry->id,
                    $entry->college->name,
                    $entry->area->name,
                    $entry->type_label,
                    $entry->description,
                    ucfirst($entry->status),
                    $entry->creator->name,
                    $entry->reviewer ? $entry->reviewer->name : 'N/A',
                    $entry->review_notes ?? 'N/A',
                    $entry->created_at->format('Y-m-d H:i:s'),
                    $entry->updated_at->format('Y-m-d H:i:s'),
                    $entry->reviewed_at ? $entry->reviewed_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get report statistics for dashboard
     */
    public function getStats(Request $request)
    {
        $collegeId = $request->get('college_id');
        $areaId = $request->get('area_id');

        // Rankings stats
        $rankingsQuery = AreaRanking::query();
        if ($collegeId) $rankingsQuery->where('college_id', $collegeId);
        if ($areaId) $rankingsQuery->where('area_id', $areaId);
        
        $rankingsStats = [
            'total_rankings' => $rankingsQuery->count(),
            'avg_completion' => $rankingsQuery->avg('completion_percentage'),
            'avg_quality' => $rankingsQuery->avg('quality_score'),
            'avg_rating' => $rankingsQuery->avg('accreditor_rating')
        ];

        // SWOT stats
        $swotQuery = SwotEntry::query();
        if ($collegeId) $swotQuery->where('college_id', $collegeId);
        if ($areaId) $swotQuery->where('area_id', $areaId);
        
        $swotStats = [
            'total_entries' => $swotQuery->count(),
            'pending' => $swotQuery->where('status', 'pending')->count(),
            'approved' => $swotQuery->where('status', 'approved')->count(),
            'rejected' => $swotQuery->where('status', 'rejected')->count(),
            'by_type' => [
                'strengths' => $swotQuery->where('type', 'S')->count(),
                'weaknesses' => $swotQuery->where('type', 'W')->count(),
                'opportunities' => $swotQuery->where('type', 'O')->count(),
                'threats' => $swotQuery->where('type', 'T')->count()
            ]
        ];

        return response()->json([
            'rankings' => $rankingsStats,
            'swot' => $swotStats
        ]);
    }
}
