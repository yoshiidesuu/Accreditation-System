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
use App\Services\ReportService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReportController extends Controller
{
    use LogsActivity;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware('auth');
        $this->reportService = $reportService;
    }

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
        $stats = $this->reportService->getReportStats();
        
        return view('reports.index', compact('colleges', 'areas', 'stats'));
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

        $filters = array_filter($validated);
        
        // Validate filters
        $errors = $this->reportService->validateFilters($filters, 'rankings');
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Get rankings data
        $rankingsData = $this->reportService->getRankingsData($filters);
        
        $data = [
            'rankings' => $rankingsData['entries'],
            'college' => $validated['college_id'] ? College::find($validated['college_id']) : null,
            'area' => $validated['area_id'] ? Area::find($validated['area_id']) : null,
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

        $filters = array_filter($validated);
        
        // Validate filters
        $errors = $this->reportService->validateFilters($filters, 'rankings');
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Get rankings data
        $rankingsData = $this->reportService->getRankingsData($filters);
        $rankings = $rankingsData['entries'];

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

        // Prepare CSV data
        $csvData = $this->reportService->prepareRankingsCsvData($rankings);
        $csvHeaders = $this->reportService->getRankingsCsvHeaders();

        $callback = function() use ($csvData, $csvHeaders) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, $csvHeaders);

            // Data rows
            foreach ($csvData as $row) {
                fputcsv($file, $row);
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

        $filters = array_filter($validated);
        
        // Validate filters
        $errors = $this->reportService->validateFilters($filters, 'swot');
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Get SWOT data
        $swotData = $this->reportService->getSwotData($filters);
        
        $data = [
            'swotEntries' => $swotData['entries'],
            'college' => $validated['college_id'] ? College::find($validated['college_id']) : null,
            'area' => $validated['area_id'] ? Area::find($validated['area_id']) : null,
            'filters' => $validated,
            'stats' => $swotData['stats'],
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

        $filters = array_filter($validated);
        
        // Validate filters
        $errors = $this->reportService->validateFilters($filters, 'swot');
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Get SWOT data
        $swotData = $this->reportService->getSwotData($filters);
        $swotEntries = $swotData['entries'];

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

        // Prepare CSV data
        $csvData = $this->reportService->prepareSwotCsvData($swotEntries);
        $csvHeaders = $this->reportService->getSwotCsvHeaders();

        $callback = function() use ($csvData, $csvHeaders) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, $csvHeaders);

            // Data rows
            foreach ($csvData as $row) {
                fputcsv($file, $row);
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
        $stats = $this->reportService->getReportStats();
        
        return response()->json($stats);
    }
}
