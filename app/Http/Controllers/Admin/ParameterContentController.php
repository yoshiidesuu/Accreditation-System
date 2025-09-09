<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\User;
use App\Models\College;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParameterContentController extends Controller
{
    /**
     * Display a listing of parameter contents.
     */
    public function index(Request $request)
    {
        $query = ParameterContent::with(['parameter', 'user', 'college', 'academicYear']);

        // Filter by parameter if specified
        if ($request->filled('parameter_id')) {
            $query->where('parameter_id', $request->parameter_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('parameter', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $parameterContents = $query->latest()->paginate(15);
        
        // Get data for filter dropdowns
        $parameters = Parameter::where('is_active', true)
            ->orderBy('title')
            ->get();
            
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.parameter-contents.index', compact(
            'parameterContents',
            'parameters', 
            'colleges',
            'academicYears'
        ));
    }

    /**
     * Display the specified parameter content.
     */
    public function show(ParameterContent $parameterContent)
    {
        $parameterContent->load(['parameter', 'user', 'college', 'academicYear', 'reviewer']);

        return view('admin.parameter-contents.show', compact('parameterContent'));
    }

    /**
     * Show the form for editing parameter content.
     */
    public function edit(ParameterContent $parameterContent)
    {
        $parameterContent->load(['parameter', 'academicYear']);
        
        $academicYears = AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.parameter-contents.edit', compact(
            'parameterContent',
            'academicYears'
        ));
    }

    /**
     * Update the specified parameter content.
     */
    public function update(Request $request, ParameterContent $parameterContent)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'content' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,submitted,approved,rejected',
        ]);

        $parameterContent->update([
            'academic_year_id' => $request->academic_year_id,
            'content' => $request->content,
            'notes' => $request->notes,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content updated successfully.');
    }

    /**
     * Remove the specified parameter content.
     */
    public function destroy(ParameterContent $parameterContent)
    {
        // Delete associated files
        if ($parameterContent->attachments) {
            foreach ($parameterContent->attachments as $attachment) {
                \Storage::disk('public')->delete($attachment['path']);
            }
        }

        $parameterContent->delete();

        return redirect()->route('admin.parameter-contents.index')
            ->with('success', 'Parameter content deleted successfully.');
    }

    /**
     * Approve parameter content.
     */
    public function approve(Request $request, ParameterContent $parameterContent)
    {
        $parameterContent->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content approved successfully.');
    }

    /**
     * Reject parameter content.
     */
    public function reject(Request $request, ParameterContent $parameterContent)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $parameterContent->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
            'notes' => $parameterContent->notes . "\n\nRejection Reason: " . $request->rejection_reason,
        ]);

        return redirect()->route('admin.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content rejected successfully.');
    }
}