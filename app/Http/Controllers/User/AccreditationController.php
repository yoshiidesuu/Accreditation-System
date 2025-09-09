<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Models\College;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\ParameterContent;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccreditationController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }
    /**
     * Display a listing of accreditations.
     */
    public function index(Request $request)
    {
        $query = Accreditation::with(['college', 'academicYear', 'evaluator']);

        // Staff can only see accreditations for their college
        if (Auth::user()->hasRole('staff')) {
            $query->where('college_id', Auth::user()->college_id);
        }

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
        
        // Get colleges for filter dropdown (admin can see all, staff only their own)
        $colleges = Auth::user()->hasRole('admin') 
            ? College::orderBy('name')->get()
            : College::where('id', Auth::user()->college_id)->get();
            
        // Get academic years for filter
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('user.accreditations.index', compact(
            'accreditations',
            'colleges',
            'academicYears'
        ));
    }

    /**
     * Show the form for creating a new accreditation.
     */
    public function create()
    {
        // Get colleges (admin can create for any college, staff only for their own)
        $colleges = Auth::user()->hasRole('admin') 
            ? College::orderBy('name')->get()
            : College::where('id', Auth::user()->college_id)->get();
            
        $academicYears = AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('user.accreditations.create', compact('colleges', 'academicYears'));
    }

    /**
     * Store a newly created accreditation.
     */
    public function store(Request $request)
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
            'requirements' => 'nullable|array',
            'documents_required' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Staff can only create accreditations for their college
        if (Auth::user()->hasRole('staff') && $request->college_id != Auth::user()->college_id) {
            abort(403, 'You can only create accreditations for your college.');
        }

        $accreditation = Accreditation::create([
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
            'requirements' => $request->requirements ?? [],
            'documents_required' => $request->documents_required ?? [],
            'status' => 'planning',
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
            'meta' => $request->meta ?? [],
        ]);

        return redirect()->route('user.accreditations.show', $accreditation)
            ->with('success', 'Accreditation created successfully.');
    }

    /**
     * Display the specified accreditation.
     */
    public function show(Accreditation $accreditation)
    {
        // Check if user can view this accreditation
        if (!Gate::allows('view', $accreditation)) {
            abort(403, 'You do not have permission to view this accreditation.');
        }

        $accreditation->load(['college', 'academicYear', 'evaluator', 'creator']);

        return view('user.accreditations.show', compact('accreditation'));
    }

    /**
     * Show the form for editing the specified accreditation.
     */
    public function edit(Accreditation $accreditation)
    {
        // Check if user can edit this accreditation
        if (!Gate::allows('update', $accreditation)) {
            abort(403, 'You do not have permission to edit this accreditation.');
        }

        // Get colleges (admin can edit for any college, staff only for their own)
        $colleges = Auth::user()->hasRole('admin') 
            ? College::orderBy('name')->get()
            : College::where('id', Auth::user()->college_id)->get();
            
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('user.accreditations.edit', compact(
            'accreditation',
            'colleges',
            'academicYears'
        ));
    }

    /**
     * Update the specified accreditation.
     */
    public function update(Request $request, Accreditation $accreditation)
    {
        // Check if user can update this accreditation
        if (!Gate::allows('update', $accreditation)) {
            abort(403, 'You do not have permission to update this accreditation.');
        }

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
            'requirements' => 'nullable|array',
            'documents_required' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Staff can only update accreditations for their college
        if (Auth::user()->hasRole('staff') && $request->college_id != Auth::user()->college_id) {
            abort(403, 'You can only update accreditations for your college.');
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
            'requirements' => $request->requirements ?? [],
            'documents_required' => $request->documents_required ?? [],
            'is_active' => $request->boolean('is_active'),
            'updated_by' => Auth::id(),
            'meta' => $request->meta ?? [],
        ]);

        return redirect()->route('user.accreditations.show', $accreditation)
            ->with('success', 'Accreditation updated successfully.');
    }

    /**
     * Remove the specified accreditation.
     */
    public function destroy(Accreditation $accreditation)
    {
        // Check if user can delete this accreditation
        if (!Gate::allows('delete', $accreditation)) {
            abort(403, 'You do not have permission to delete this accreditation.');
        }

        // Cannot delete accreditations that are in progress or completed
        if (in_array($accreditation->status, ['in_progress', 'completed', 'accredited'])) {
            return redirect()->route('user.accreditations.index')
                ->with('error', 'Cannot delete accreditations that are in progress or completed.');
        }

        $accreditation->delete();

        return redirect()->route('user.accreditations.index')
            ->with('success', 'Accreditation deleted successfully.');
    }

    /**
     * Evaluate an accreditation (change status and add evaluation notes).
     */
    public function evaluate(Request $request, Accreditation $accreditation)
    {
        // Check if user can evaluate this accreditation
        if (!Gate::allows('evaluate', $accreditation)) {
            abort(403, 'You do not have permission to evaluate this accreditation.');
        }

        $request->validate([
            'status' => 'required|in:planning,in_progress,under_review,completed,accredited,denied,suspended',
            'evaluation_notes' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'recommendations' => 'nullable|array',
        ]);

        $accreditation->update([
            'status' => $request->status,
            'evaluation_notes' => $request->evaluation_notes,
            'score' => $request->score,
            'recommendations' => $request->recommendations ?? [],
            'evaluated_by' => Auth::id(),
            'evaluated_at' => now(),
        ]);

        return redirect()->route('user.accreditations.show', $accreditation)
            ->with('success', 'Accreditation evaluation updated successfully.');
    }

    /**
     * Submit accreditation report.
     */
    public function submitReport(Request $request, Accreditation $accreditation)
    {
        // Check if user can submit report for this accreditation
        if (!Gate::allows('update', $accreditation)) {
            abort(403, 'You do not have permission to submit report for this accreditation.');
        }

        $request->validate([
            'report_content' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        // Handle file attachments
        $attachments = $accreditation->report_attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('accreditation-reports', $filename, 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $accreditation->update([
            'report_content' => $request->report_content,
            'report_attachments' => $attachments,
            'report_submitted_at' => now(),
            'report_submitted_by' => Auth::id(),
            'status' => 'under_review',
        ]);

        return redirect()->route('user.accreditations.show', $accreditation)
            ->with('success', 'Accreditation report submitted successfully.');
    }

    /**
     * Show the coordinator tagging interface.
     */
    public function coordinatorTagging(Request $request)
    {
        // Only overall coordinators can access this
        if (!Auth::user()->hasRole('overall_coordinator')) {
            abort(403, 'Access denied. Only overall coordinators can access this feature.');
        }

        $query = Accreditation::with(['college', 'academicYear', 'assignedLead', 'assignedMembers']);

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accreditations = $query->latest()->paginate(15);
        
        // Get filter options
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get available accreditors
        $accreditorLeads = User::role('accreditor_lead')->orderBy('name')->get();
        $accreditorMembers = User::role('accreditor_member')->orderBy('name')->get();

        return view('user.accreditations.coordinator-tagging', compact(
            'accreditations',
            'colleges',
            'academicYears',
            'accreditorLeads',
            'accreditorMembers'
        ));
    }

    /**
     * Assign accreditors to an accreditation.
     */
    public function assignAccreditors(Request $request, Accreditation $accreditation)
    {
        // Only overall coordinators can assign accreditors
        if (!Auth::user()->hasRole('overall_coordinator')) {
            abort(403, 'Access denied. Only overall coordinators can assign accreditors.');
        }

        $request->validate([
            'assigned_lead_id' => 'nullable|exists:users,id',
            'assigned_members' => 'nullable|array',
            'assigned_members.*' => 'exists:users,id',
        ]);

        // Validate that assigned lead has accreditor_lead role
        if ($request->assigned_lead_id) {
            $lead = User::find($request->assigned_lead_id);
            if (!$lead->hasRole('accreditor_lead')) {
                return back()->withErrors(['assigned_lead_id' => 'Selected user must have accreditor lead role.']);
            }
        }

        // Validate that assigned members have accreditor_member role
        if ($request->assigned_members) {
            $members = User::whereIn('id', $request->assigned_members)->get();
            foreach ($members as $member) {
                if (!$member->hasRole('accreditor_member')) {
                    return back()->withErrors(['assigned_members' => 'All selected members must have accreditor member role.']);
                }
            }
        }

        $accreditation->update([
            'assigned_lead_id' => $request->assigned_lead_id,
            'assigned_members' => $request->assigned_members ?? [],
        ]);

        // Log the assignment
        $this->activityLogger->logAccreditationTagging(
            $accreditation,
            'accreditors_assigned',
            [
                'assigned_lead_id' => $request->assigned_lead_id,
                'assigned_members' => $request->assigned_members ?? [],
                'assigned_by' => Auth::user()->name
            ]
        );

        return back()->with('success', 'Accreditors assigned successfully.');
    }

    /**
     * Show accreditor dashboard with assigned accreditations.
     */
    public function accreditorDashboard(Request $request)
    {
        // Only accreditors can access this dashboard
        if (!Auth::user()->hasAnyRole(['accreditor_lead', 'accreditor_member'])) {
            abort(403, 'Access denied. Only accreditors can access this dashboard.');
        }

        $userId = Auth::id();
        $query = Accreditation::with(['college', 'academicYear', 'assignedLead', 'assignedMembers'])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_lead_id', $userId)
                  ->orWhereJsonContains('assigned_members', $userId);
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
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
        
        // Get colleges for filter (only those where user is assigned)
        $assignedCollegeIds = Accreditation::where(function ($q) use ($userId) {
            $q->where('assigned_lead_id', $userId)
              ->orWhereJsonContains('assigned_members', $userId);
        })->pluck('college_id')->unique();
        
        $colleges = College::whereIn('id', $assignedCollegeIds)->orderBy('name')->get();

        return view('user.accreditations.accreditor-dashboard', compact(
            'accreditations',
            'colleges'
        ));
    }

    /**
     * Show parameter content tagging interface for an accreditation.
     */
    public function showTagging(Accreditation $accreditation)
    {
        // Only overall coordinators and assigned accreditors can access
        if (!Auth::user()->hasRole('overall_coordinator') && 
            !$accreditation->isUserAssigned(Auth::id())) {
            abort(403, 'Access denied.');
        }

        $accreditation->load(['college', 'academicYear', 'assignedLead', 'assignedMembers']);
        
        // Get parameter contents for the college and academic year
        $parameterContents = ParameterContent::with(['parameter', 'uploadedBy'])
            ->where('college_id', $accreditation->college_id)
            ->where('academic_year_id', $accreditation->academic_year_id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(20);

        // Get already tagged contents
        $taggedContentIds = $accreditation->accreditationTags()->pluck('parameter_content_id')->toArray();

        return view('user.accreditations.show-tagging', compact(
            'accreditation',
            'parameterContents',
            'taggedContentIds'
        ));
    }

    /**
     * Tag parameter content to an accreditation.
     */
    public function tagContent(Request $request, Accreditation $accreditation)
    {
        // Only overall coordinators and assigned accreditors can tag
        if (!Auth::user()->hasRole('overall_coordinator') && 
            !$accreditation->isUserAssigned(Auth::id())) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'parameter_content_id' => 'required|exists:parameter_contents,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if content is already tagged
        $existingTag = $accreditation->accreditationTags()
            ->where('parameter_content_id', $request->parameter_content_id)
            ->first();

        if ($existingTag) {
            return back()->withErrors(['parameter_content_id' => 'This content is already tagged to this accreditation.']);
        }

        // Create the tag
        $accreditation->accreditationTags()->create([
            'parameter_content_id' => $request->parameter_content_id,
            'tagged_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        // Log the tagging
        $this->activityLogger->logAccreditationTagging(
            $accreditation,
            'content_tagged',
            [
                'parameter_content_id' => $request->parameter_content_id,
                'notes' => $request->notes,
                'tagged_by' => Auth::user()->name
            ]
        );

        return back()->with('success', 'Content tagged successfully.');
    }

    /**
     * Remove tag from parameter content.
     */
    public function untagContent(Request $request, Accreditation $accreditation)
    {
        // Only overall coordinators and assigned accreditors can untag
        if (!Auth::user()->hasRole('overall_coordinator') && 
            !$accreditation->isUserAssigned(Auth::id())) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'parameter_content_id' => 'required|exists:parameter_contents,id',
        ]);

        $tag = $accreditation->accreditationTags()
            ->where('parameter_content_id', $request->parameter_content_id)
            ->first();

        if (!$tag) {
            return back()->withErrors(['parameter_content_id' => 'This content is not tagged to this accreditation.']);
        }

        $tag->delete();

        // Log the untagging
        $this->activityLogger->logAccreditationTagging(
            $accreditation,
            'content_untagged',
            [
                'parameter_content_id' => $request->parameter_content_id,
                'untagged_by' => Auth::user()->name
            ]
        );

        return back()->with('success', 'Content untagged successfully.');
    }
}