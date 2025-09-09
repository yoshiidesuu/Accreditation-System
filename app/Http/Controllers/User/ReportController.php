<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\AcademicYear;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\Accreditation;
use App\Models\SwotEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get colleges based on user role
        $colleges = $this->getCollegesForUser();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get summary statistics
        $stats = $this->getReportStats($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        return view('user.reports.index', compact(
            'colleges',
            'academicYears',
            'stats',
            'recentActivities'
        ));
    }

    /**
     * Display college-specific reports.
     */
    public function college(Request $request, College $college)
    {
        // Check if user can view this college's reports
        if (!$this->canViewCollegeReports($college)) {
            abort(403, 'You do not have permission to view reports for this college.');
        }

        $academicYear = $this->getSelectedAcademicYear($request);
        
        // Get college statistics
        $stats = $this->getCollegeStats($college, $academicYear);
        
        // Get parameter completion data
        $parameterCompletion = $this->getParameterCompletionData($college, $academicYear);
        
        // Get SWOT analysis data
        $swotData = $this->getSwotAnalysisData($college, $academicYear);
        
        // Get accreditation status
        $accreditationStatus = $this->getAccreditationStatus($college, $academicYear);
        
        // Get areas performance
        $areasPerformance = $this->getAreasPerformance($college, $academicYear);
        
        return view('user.reports.college', compact(
            'college',
            'academicYear',
            'stats',
            'parameterCompletion',
            'swotData',
            'accreditationStatus',
            'areasPerformance'
        ));
    }

    /**
     * Display accreditation reports.
     */
    public function accreditation(Request $request)
    {
        $user = Auth::user();
        $query = Accreditation::with(['college', 'academicYear']);
        
        // Apply role-based filtering
        if ($user->hasRole('staff')) {
            $query->where('college_id', $user->college_id);
        } elseif ($user->hasRole('coordinator') || $user->hasRole('faculty')) {
            $query->where('college_id', $user->college_id);
        }
        
        // Apply filters
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        $accreditations = $query->latest()->paginate(15);
        
        // Get filter options
        $colleges = $this->getCollegesForUser();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get accreditation statistics
        $accreditationStats = $this->getAccreditationStats($user);
        
        return view('user.reports.accreditation', compact(
            'accreditations',
            'colleges',
            'academicYears',
            'accreditationStats'
        ));
    }

    /**
     * Display user's personal reports.
     */
    public function myReports(Request $request)
    {
        $user = Auth::user();
        
        // Get user's parameter contents
        $parameterContents = ParameterContent::with(['parameter', 'area', 'college'])
            ->where('created_by', $user->id)
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('area_id'), function ($query) use ($request) {
                $query->where('area_id', $request->area_id);
            })
            ->latest()
            ->paginate(10);
        
        // Get user's SWOT entries
        $swotEntries = SwotEntry::with(['college', 'area'])
            ->where('created_by', $user->id)
            ->when($request->filled('swot_status'), function ($query) use ($request) {
                $query->where('status', $request->swot_status);
            })
            ->when($request->filled('swot_type'), function ($query) use ($request) {
                $query->where('type', $request->swot_type);
            })
            ->latest()
            ->paginate(10);
        
        // Get user's statistics
        $userStats = $this->getUserStats($user);
        
        // Get areas for filter
        $areas = Area::where('is_active', true)->orderBy('name')->get();
        
        return view('user.reports.my-reports', compact(
            'parameterContents',
            'swotEntries',
            'userStats',
            'areas'
        ));
    }

    /**
     * Export reports in various formats.
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:college,accreditation,parameter,swot,comprehensive',
            'format' => 'required|in:pdf,excel,csv',
            'college_id' => 'nullable|exists:colleges,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);
        
        $type = $request->type;
        $format = $request->format;
        $collegeId = $request->college_id;
        $academicYearId = $request->academic_year_id;
        $areaId = $request->area_id;
        
        // Validate access
        if ($collegeId && !$this->canViewCollegeReports(College::find($collegeId))) {
            abort(403, 'You do not have permission to export reports for this college.');
        }
        
        // Generate filename
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "{$type}_report_{$timestamp}";
        
        // Get data based on report type
        $data = $this->getExportData($type, $collegeId, $academicYearId, $areaId);
        
        // Export based on format
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($data, $type, $filename);
            case 'excel':
                return $this->exportToExcel($data, $type, $filename);
            case 'csv':
                return $this->exportToCsv($data, $type, $filename);
            default:
                abort(400, 'Invalid export format.');
        }
    }

    /**
     * Get colleges based on user role.
     */
    private function getCollegesForUser()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return College::orderBy('name')->get();
        }
        
        return College::where('id', $user->college_id)->get();
    }

    /**
     * Check if user can view college reports.
     */
    private function canViewCollegeReports(College $college)
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return $college->id === $user->college_id;
    }

    /**
     * Get selected academic year or default to active year.
     */
    private function getSelectedAcademicYear(Request $request)
    {
        if ($request->filled('academic_year_id')) {
            return AcademicYear::find($request->academic_year_id);
        }
        
        return AcademicYear::where('is_active', true)->first() 
            ?? AcademicYear::latest('start_date')->first();
    }

    /**
     * Get report statistics for dashboard.
     */
    private function getReportStats($user)
    {
        $stats = [];
        
        if ($user->hasRole('admin')) {
            $stats['total_colleges'] = College::count();
            $stats['total_parameters'] = Parameter::count();
            $stats['total_contents'] = ParameterContent::count();
            $stats['total_swot_entries'] = SwotEntry::count();
        } else {
            $stats['college_parameters'] = Parameter::whereHas('area', function ($query) use ($user) {
                $query->where('college_id', $user->college_id);
            })->count();
            
            $stats['my_contents'] = ParameterContent::where('created_by', $user->id)->count();
            $stats['my_swot_entries'] = SwotEntry::where('created_by', $user->id)->count();
            $stats['pending_reviews'] = ParameterContent::where('college_id', $user->college_id)
                ->where('status', 'pending_review')->count();
        }
        
        return $stats;
    }

    /**
     * Get recent activities for dashboard.
     */
    private function getRecentActivities($user)
    {
        $activities = collect();
        
        // Recent parameter contents
        $recentContents = ParameterContent::with(['parameter', 'creator'])
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('college_id', $user->college_id);
            })
            ->latest()
            ->limit(5)
            ->get();
        
        foreach ($recentContents as $content) {
            $activities->push([
                'type' => 'parameter_content',
                'title' => "Parameter content: {$content->parameter->title}",
                'user' => $content->creator->name,
                'date' => $content->created_at,
                'status' => $content->status,
            ]);
        }
        
        // Recent SWOT entries
        $recentSwot = SwotEntry::with(['creator'])
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('college_id', $user->college_id);
            })
            ->latest()
            ->limit(5)
            ->get();
        
        foreach ($recentSwot as $swot) {
            $activities->push([
                'type' => 'swot_entry',
                'title' => "SWOT: {$swot->title}",
                'user' => $swot->creator->name,
                'date' => $swot->created_at,
                'status' => $swot->status,
            ]);
        }
        
        return $activities->sortByDesc('date')->take(10);
    }

    /**
     * Get college-specific statistics.
     */
    private function getCollegeStats(College $college, AcademicYear $academicYear = null)
    {
        $query = ParameterContent::where('college_id', $college->id);
        
        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }
        
        return [
            'total_parameters' => Parameter::whereHas('area', function ($q) use ($college) {
                $q->where('college_id', $college->id);
            })->count(),
            'completed_contents' => $query->clone()->where('status', 'approved')->count(),
            'pending_contents' => $query->clone()->where('status', 'pending_review')->count(),
            'draft_contents' => $query->clone()->where('status', 'draft')->count(),
            'total_swot_entries' => SwotEntry::where('college_id', $college->id)
                ->when($academicYear, function ($q) use ($academicYear) {
                    $q->where('academic_year_id', $academicYear->id);
                })->count(),
        ];
    }

    /**
     * Get parameter completion data for college.
     */
    private function getParameterCompletionData(College $college, AcademicYear $academicYear = null)
    {
        // Implementation for parameter completion charts/data
        return [];
    }

    /**
     * Get SWOT analysis data for college.
     */
    private function getSwotAnalysisData(College $college, AcademicYear $academicYear = null)
    {
        $query = SwotEntry::where('college_id', $college->id);
        
        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }
        
        return [
            'strengths' => $query->clone()->where('type', 'strength')->count(),
            'weaknesses' => $query->clone()->where('type', 'weakness')->count(),
            'opportunities' => $query->clone()->where('type', 'opportunity')->count(),
            'threats' => $query->clone()->where('type', 'threat')->count(),
        ];
    }

    /**
     * Get accreditation status for college.
     */
    private function getAccreditationStatus(College $college, AcademicYear $academicYear = null)
    {
        $query = Accreditation::where('college_id', $college->id);
        
        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }
        
        return $query->get()->groupBy('status');
    }

    /**
     * Get areas performance for college.
     */
    private function getAreasPerformance(College $college, AcademicYear $academicYear = null)
    {
        // Implementation for areas performance data
        return [];
    }

    /**
     * Get accreditation statistics.
     */
    private function getAccreditationStats($user)
    {
        $query = Accreditation::query();
        
        if (!$user->hasRole('admin')) {
            $query->where('college_id', $user->college_id);
        }
        
        return [
            'total' => $query->count(),
            'planning' => $query->clone()->where('status', 'planning')->count(),
            'in_progress' => $query->clone()->where('status', 'in_progress')->count(),
            'completed' => $query->clone()->where('status', 'completed')->count(),
            'accredited' => $query->clone()->where('status', 'accredited')->count(),
        ];
    }

    /**
     * Get user-specific statistics.
     */
    private function getUserStats($user)
    {
        return [
            'total_contents' => ParameterContent::where('created_by', $user->id)->count(),
            'approved_contents' => ParameterContent::where('created_by', $user->id)
                ->where('status', 'approved')->count(),
            'pending_contents' => ParameterContent::where('created_by', $user->id)
                ->where('status', 'pending_review')->count(),
            'total_swot' => SwotEntry::where('created_by', $user->id)->count(),
            'approved_swot' => SwotEntry::where('created_by', $user->id)
                ->where('status', 'approved')->count(),
        ];
    }

    /**
     * Get export data based on type.
     */
    private function getExportData($type, $collegeId, $academicYearId, $areaId)
    {
        // Implementation for different export data types
        switch ($type) {
            case 'college':
                return $this->getCollegeExportData($collegeId, $academicYearId);
            case 'accreditation':
                return $this->getAccreditationExportData($collegeId, $academicYearId);
            case 'parameter':
                return $this->getParameterExportData($collegeId, $academicYearId, $areaId);
            case 'swot':
                return $this->getSwotExportData($collegeId, $academicYearId, $areaId);
            case 'comprehensive':
                return $this->getComprehensiveExportData($collegeId, $academicYearId);
            default:
                return [];
        }
    }

    /**
     * Export to PDF.
     */
    private function exportToPdf($data, $type, $filename)
    {
        $pdf = Pdf::loadView("user.reports.exports.{$type}-pdf", compact('data'));
        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Export to Excel.
     */
    private function exportToExcel($data, $type, $filename)
    {
        return Excel::download(new ReportExport($data, $type), "{$filename}.xlsx");
    }

    /**
     * Export to CSV.
     */
    private function exportToCsv($data, $type, $filename)
    {
        return Excel::download(new ReportExport($data, $type), "{$filename}.csv");
    }

    // Additional helper methods for specific export data types
    private function getCollegeExportData($collegeId, $academicYearId) { return []; }
    private function getAccreditationExportData($collegeId, $academicYearId) { return []; }
    private function getParameterExportData($collegeId, $academicYearId, $areaId) { return []; }
    private function getSwotExportData($collegeId, $academicYearId, $areaId) { return []; }
    private function getComprehensiveExportData($collegeId, $academicYearId) { return []; }
}