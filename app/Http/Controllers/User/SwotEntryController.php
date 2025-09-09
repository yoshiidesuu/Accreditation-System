<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SwotEntry;
use App\Models\College;
use App\Models\AcademicYear;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SwotEntryController extends Controller
{
    /**
     * Display a listing of SWOT entries.
     */
    public function index(Request $request)
    {
        $query = SwotEntry::with(['college', 'academicYear', 'area', 'creator']);

        // Role-based filtering
        if (Auth::user()->hasRole('coordinator')) {
            // Coordinators can see entries for their college
            $query->where('college_id', Auth::user()->college_id);
        } elseif (Auth::user()->hasRole('faculty')) {
            // Faculty can see entries they created or for their college
            $query->where(function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhere('college_id', Auth::user()->college_id);
            });
        }

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by area
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Filter by SWOT type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('impact', 'like', "%{$search}%")
                  ->orWhere('action_plan', 'like', "%{$search}%");
            });
        }

        $swotEntries = $query->latest()->paginate(15);
        
        // Get filter options based on user role
        $colleges = $this->getCollegesForUser();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('user.swot-entries.index', compact(
            'swotEntries',
            'colleges',
            'academicYears',
            'areas'
        ));
    }

    /**
     * Show the form for creating a new SWOT entry.
     */
    public function create()
    {
        $colleges = $this->getCollegesForUser();
        $academicYears = AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('user.swot-entries.create', compact(
            'colleges',
            'academicYears',
            'areas'
        ));
    }

    /**
     * Store a newly created SWOT entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'area_id' => 'nullable|exists:areas,id',
            'type' => 'required|in:strength,weakness,opportunity,threat',
            'impact' => 'required|in:low,medium,high,critical',
            'priority' => 'required|in:low,medium,high,urgent',
            'action_plan' => 'nullable|string',
            'responsible_person' => 'nullable|string|max:255',
            'target_date' => 'nullable|date|after:today',
            'resources_needed' => 'nullable|string',
            'success_metrics' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validate college access based on user role
        $this->validateCollegeAccess($request->college_id);

        $swotEntry = SwotEntry::create([
            'title' => $request->title,
            'description' => $request->description,
            'college_id' => $request->college_id,
            'academic_year_id' => $request->academic_year_id,
            'area_id' => $request->area_id,
            'type' => $request->type,
            'impact' => $request->impact,
            'priority' => $request->priority,
            'action_plan' => $request->action_plan,
            'responsible_person' => $request->responsible_person,
            'target_date' => $request->target_date,
            'resources_needed' => $request->resources_needed,
            'success_metrics' => $request->success_metrics,
            'status' => 'draft',
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
            'meta' => $request->meta ?? [],
        ]);

        return redirect()->route('user.swot-entries.show', $swotEntry)
            ->with('success', 'SWOT entry created successfully.');
    }

    /**
     * Display the specified SWOT entry.
     */
    public function show(SwotEntry $swotEntry)
    {
        // Check if user can view this SWOT entry
        if (!Gate::allows('view', $swotEntry)) {
            abort(403, 'You do not have permission to view this SWOT entry.');
        }

        $swotEntry->load(['college', 'academicYear', 'area', 'creator']);

        return view('user.swot-entries.show', compact('swotEntry'));
    }

    /**
     * Show the form for editing the specified SWOT entry.
     */
    public function edit(SwotEntry $swotEntry)
    {
        // Check if user can edit this SWOT entry
        if (!Gate::allows('update', $swotEntry)) {
            abort(403, 'You do not have permission to edit this SWOT entry.');
        }

        $colleges = $this->getCollegesForUser();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('user.swot-entries.edit', compact(
            'swotEntry',
            'colleges',
            'academicYears',
            'areas'
        ));
    }

    /**
     * Update the specified SWOT entry.
     */
    public function update(Request $request, SwotEntry $swotEntry)
    {
        // Check if user can update this SWOT entry
        if (!Gate::allows('update', $swotEntry)) {
            abort(403, 'You do not have permission to update this SWOT entry.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'area_id' => 'nullable|exists:areas,id',
            'type' => 'required|in:strength,weakness,opportunity,threat',
            'impact' => 'required|in:low,medium,high,critical',
            'priority' => 'required|in:low,medium,high,urgent',
            'action_plan' => 'nullable|string',
            'responsible_person' => 'nullable|string|max:255',
            'target_date' => 'nullable|date|after:today',
            'resources_needed' => 'nullable|string',
            'success_metrics' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validate college access based on user role
        $this->validateCollegeAccess($request->college_id);

        $swotEntry->update([
            'title' => $request->title,
            'description' => $request->description,
            'college_id' => $request->college_id,
            'academic_year_id' => $request->academic_year_id,
            'area_id' => $request->area_id,
            'type' => $request->type,
            'impact' => $request->impact,
            'priority' => $request->priority,
            'action_plan' => $request->action_plan,
            'responsible_person' => $request->responsible_person,
            'target_date' => $request->target_date,
            'resources_needed' => $request->resources_needed,
            'success_metrics' => $request->success_metrics,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => Auth::id(),
            'meta' => $request->meta ?? [],
        ]);

        return redirect()->route('user.swot-entries.show', $swotEntry)
            ->with('success', 'SWOT entry updated successfully.');
    }

    /**
     * Remove the specified SWOT entry.
     */
    public function destroy(SwotEntry $swotEntry)
    {
        // Check if user can delete this SWOT entry
        if (!Gate::allows('delete', $swotEntry)) {
            abort(403, 'You do not have permission to delete this SWOT entry.');
        }

        // Cannot delete SWOT entries that are approved or in progress
        if (in_array($swotEntry->status, ['approved', 'in_progress', 'completed'])) {
            return redirect()->route('user.swot-entries.index')
                ->with('error', 'Cannot delete SWOT entries that are approved or in progress.');
        }

        $swotEntry->delete();

        return redirect()->route('user.swot-entries.index')
            ->with('success', 'SWOT entry deleted successfully.');
    }

    /**
     * Submit SWOT entry for review.
     */
    public function submit(SwotEntry $swotEntry)
    {
        // Check if user can submit this SWOT entry
        if (!Gate::allows('update', $swotEntry)) {
            abort(403, 'You do not have permission to submit this SWOT entry.');
        }

        // Can only submit draft entries
        if ($swotEntry->status !== 'draft') {
            return redirect()->route('user.swot-entries.show', $swotEntry)
                ->with('error', 'Only draft SWOT entries can be submitted for review.');
        }

        $swotEntry->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
        ]);

        return redirect()->route('user.swot-entries.show', $swotEntry)
            ->with('success', 'SWOT entry submitted for review successfully.');
    }

    /**
     * Approve or reject SWOT entry (coordinators only).
     */
    public function review(Request $request, SwotEntry $swotEntry)
    {
        // Only coordinators can review SWOT entries
        if (!Auth::user()->hasRole('coordinator')) {
            abort(403, 'Only coordinators can review SWOT entries.');
        }

        // Check if user can review this SWOT entry (same college)
        if ($swotEntry->college_id !== Auth::user()->college_id) {
            abort(403, 'You can only review SWOT entries for your college.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string',
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';

        $swotEntry->update([
            'status' => $status,
            'review_notes' => $request->review_notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        $message = $request->action === 'approve' 
            ? 'SWOT entry approved successfully.' 
            : 'SWOT entry rejected.';

        return redirect()->route('user.swot-entries.show', $swotEntry)
            ->with('success', $message);
    }

    /**
     * Get colleges based on user role.
     */
    private function getCollegesForUser()
    {
        if (Auth::user()->hasRole('admin')) {
            return College::orderBy('name')->get();
        }
        
        return College::where('id', Auth::user()->college_id)->get();
    }

    /**
     * Validate college access based on user role.
     */
    private function validateCollegeAccess($collegeId)
    {
        if (!Auth::user()->hasRole('admin') && $collegeId != Auth::user()->college_id) {
            abort(403, 'You can only create/update SWOT entries for your college.');
        }
    }
}