<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Models\College;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\ParameterContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccreditationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of accreditations for admin management.
     */
    public function index(Request $request)
    {
        $query = Accreditation::with(['college', 'academicYear', 'evaluator', 'creator', 'assignedLead']);

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by accreditation type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by accrediting body
        if ($request->filled('accrediting_body')) {
            $query->where('accrediting_body', 'like', '%' . $request->accrediting_body . '%');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('accrediting_body', 'like', "%{$search}%")
                  ->orWhereHas('college', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $accreditations = $query->latest()->paginate(15);
        
        // Get data for filter dropdowns
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get statistics
        $stats = [
            'total' => Accreditation::count(),
            'planning' => Accreditation::where('status', 'planning')->count(),
            'in_progress' => Accreditation::where('status', 'in_progress')->count(),
            'completed' => Accreditation::where('status', 'completed')->count(),
            'accredited' => Accreditation::where('status', 'accredited')->count(),
        ];

        return view('admin.accreditations.index', compact(
            'accreditations',
            'colleges',
            'academicYears',
            'stats'
        ));
    }

    /**
     * Display the specified accreditation.
     */
    public function show(Accreditation $accreditation)
    {
        $accreditation->load([
            'college', 
            'academicYear', 
            'evaluator', 
            'creator',
            'assignedLead',
            'assignedMembers',
            'accreditationTags.parameterContent.parameter'
        ]);

        // Get parameter contents statistics
        $contentStats = [
            'total_tagged' => $accreditation->accreditationTags()->count(),
            'by_parameter' => $accreditation->accreditationTags()
                ->join('parameter_contents', 'accreditation_tags.parameter_content_id', '=', 'parameter_contents.id')
                ->join('parameters', 'parameter_contents.parameter_id', '=', 'parameters.id')
                ->select('parameters.name', DB::raw('count(*) as count'))
                ->groupBy('parameters.id', 'parameters.name')
                ->get()
        ];

        return view('admin.accreditations.show', compact('accreditation', 'contentStats'));
    }

    /**
     * Show the form for editing the specified accreditation.
     */
    public function edit(Accreditation $accreditation)
    {
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get available accreditors
        $accreditorLeads = User::role('accreditor_lead')->orderBy('name')->get();
        $accreditorMembers = User::role('accreditor_member')->orderBy('name')->get();

        return view('admin.accreditations.edit', compact(
            'accreditation',
            'colleges',
            'academicYears',
            'accreditorLeads',
            'accreditorMembers'
        ));
    }

    /**
     * Update the specified accreditation.
     */
    public function update(Request $request, Accreditation $accreditation)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:institutional,program,specialized',
            'accrediting_body' => 'required|string|max:255',
            'level' => 'required|in:candidate,initial,continuing,reaffirmation',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'visit_date' => 'nullable|date',
            'status' => 'required|in:planning,in_progress,under_review,completed,accredited,denied,suspended',
            'requirements' => 'nullable|array',
            'documents_required' => 'nullable|array',
            'assigned_lead_id' => 'nullable|exists:users,id',
            'assigned_members' => 'nullable|array',
            'assigned_members.*' => 'exists:users,id',
            'is_active' => 'boolean',
            'evaluation_notes' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'recommendations' => 'nullable|array',
        ]);

        // Validate assigned lead role
        if ($request->assigned_lead_id) {
            $lead = User::find($request->assigned_lead_id);
            if (!$lead->hasRole('accreditor_lead')) {
                return back()->withErrors(['assigned_lead_id' => 'Selected user must have accreditor lead role.']);
            }
        }

        // Validate assigned members roles
        if ($request->assigned_members) {
            $members = User::whereIn('id', $request->assigned_members)->get();
            foreach ($members as $member) {
                if (!$member->hasRole('accreditor_member')) {
                    return back()->withErrors(['assigned_members' => 'All selected members must have accreditor member role.']);
                }
            }
        }

        $accreditation->update([
            'title' => $request->title,
            'description' => $request->description,
            'college_id' => $request->college_id,
            'academic_year_id' => $request->academic_year_id,
            'type' => $request->type,
            'accrediting_body' => $request->accrediting_body,
            'level' => $request->level,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'visit_date' => $request->visit_date,
            'status' => $request->status,
            'requirements' => $request->requirements ?? [],
            'documents_required' => $request->documents_required ?? [],
            'assigned_lead_id' => $request->assigned_lead_id,
            'assigned_members' => $request->assigned_members ?? [],
            'is_active' => $request->boolean('is_active'),
            'evaluation_notes' => $request->evaluation_notes,
            'score' => $request->score,
            'recommendations' => $request->recommendations ?? [],
            'updated_by' => Auth::id(),
        ]);

        // Log the update
        activity()
            ->performedOn($accreditation)
            ->causedBy(Auth::user())
            ->withProperties($request->only([
                'title', 'status', 'assigned_lead_id', 'assigned_members'
            ]))
            ->log('Admin updated accreditation');

        return redirect()->route('admin.accreditations.show', $accreditation)
            ->with('success', 'Accreditation updated successfully.');
    }

    /**
     * Remove the specified accreditation.
     */
    public function destroy(Accreditation $accreditation)
    {
        // Log before deletion
        activity()
            ->performedOn($accreditation)
            ->causedBy(Auth::user())
            ->withProperties([
                'title' => $accreditation->title,
                'college' => $accreditation->college->name,
                'status' => $accreditation->status
            ])
            ->log('Admin deleted accreditation');

        $accreditation->delete();

        return redirect()->route('admin.accreditations.index')
            ->with('success', 'Accreditation deleted successfully.');
    }

    /**
     * Bulk update accreditations status.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'accreditation_ids' => 'required|array',
            'accreditation_ids.*' => 'exists:accreditations,id',
            'action' => 'required|in:activate,deactivate,delete,change_status',
            'status' => 'required_if:action,change_status|in:planning,in_progress,under_review,completed,accredited,denied,suspended'
        ]);

        $accreditations = Accreditation::whereIn('id', $request->accreditation_ids);
        $count = $accreditations->count();

        switch ($request->action) {
            case 'activate':
                $accreditations->update(['is_active' => true]);
                $message = "{$count} accreditations activated successfully.";
                break;
            case 'deactivate':
                $accreditations->update(['is_active' => false]);
                $message = "{$count} accreditations deactivated successfully.";
                break;
            case 'change_status':
                $accreditations->update(['status' => $request->status]);
                $message = "{$count} accreditations status changed to {$request->status} successfully.";
                break;
            case 'delete':
                $accreditations->delete();
                $message = "{$count} accreditations deleted successfully.";
                break;
        }

        // Log bulk action
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'action' => $request->action,
                'count' => $count,
                'accreditation_ids' => $request->accreditation_ids,
                'status' => $request->status ?? null
            ])
            ->log('Admin performed bulk action on accreditations');

        return redirect()->route('admin.accreditations.index')
            ->with('success', $message);
    }

    /**
     * Get accreditation statistics for dashboard.
     */
    public function stats()
    {
        $stats = [
            'total' => Accreditation::count(),
            'by_status' => Accreditation::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_type' => Accreditation::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_college' => Accreditation::join('colleges', 'accreditations.college_id', '=', 'colleges.id')
                ->select('colleges.name', DB::raw('count(*) as count'))
                ->groupBy('colleges.id', 'colleges.name')
                ->pluck('count', 'name'),
            'recent' => Accreditation::with(['college', 'academicYear'])
                ->latest()
                ->limit(5)
                ->get(),
            'upcoming_visits' => Accreditation::with(['college'])
                ->whereNotNull('visit_date')
                ->where('visit_date', '>=', now())
                ->orderBy('visit_date')
                ->limit(10)
                ->get()
        ];

        return response()->json($stats);
    }
}