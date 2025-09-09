<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\ParameterContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ParameterContentController extends Controller
{
    /**
     * Display a listing of parameter contents.
     */
    public function index(Request $request)
    {
        $query = ParameterContent::with(['parameter', 'user', 'college', 'academicYear'])
            ->where('user_id', Auth::id());

        // Filter by parameter if specified
        if ($request->filled('parameter_id')) {
            $query->where('parameter_id', $request->parameter_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
                  });
            });
        }

        $parameterContents = $query->latest()->paginate(15);
        
        // Get parameters for filter dropdown
        $parameters = Parameter::where('is_active', true)
            ->orderBy('title')
            ->get();
            
        // Get academic years for filter
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();

        return view('user.parameter-contents.index', compact(
            'parameterContents',
            'parameters', 
            'academicYears'
        ));
    }

    /**
     * Show the form for creating new parameter content.
     */
    public function create(Request $request)
    {
        $parameter = null;
        if ($request->filled('parameter_id')) {
            $parameter = Parameter::findOrFail($request->parameter_id);
        }

        $parameters = Parameter::where('is_active', true)
            ->orderBy('title')
            ->get();
            
        $academicYears = \App\Models\AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('user.parameter-contents.create', compact(
            'parameter',
            'parameters',
            'academicYears'
        ));
    }

    /**
     * Store a newly created parameter content.
     */
    public function store(Request $request)
    {
        $request->validate([
            'parameter_id' => 'required|exists:parameters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'content' => 'required|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        $parameter = Parameter::findOrFail($request->parameter_id);
        
        // Check if user can create content for this parameter
        if (!Gate::allows('create-parameter-content', $parameter)) {
            abort(403, 'You do not have permission to create content for this parameter.');
        }

        // Check if content already exists for this parameter and academic year
        $existingContent = ParameterContent::where([
            'parameter_id' => $request->parameter_id,
            'academic_year_id' => $request->academic_year_id,
            'user_id' => Auth::id(),
        ])->first();

        if ($existingContent) {
            return redirect()->back()
                ->withErrors(['parameter_id' => 'Content already exists for this parameter and academic year.'])
                ->withInput();
        }

        $parameterContent = ParameterContent::create([
            'parameter_id' => $request->parameter_id,
            'academic_year_id' => $request->academic_year_id,
            'user_id' => Auth::id(),
            'college_id' => Auth::user()->college_id,
            'content' => $request->content,
            'notes' => $request->notes,
            'status' => 'draft',
            'meta' => $request->meta ?? [],
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('parameter-contents', $filename, 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
            $parameterContent->update(['attachments' => $attachments]);
        }

        return redirect()->route('user.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content created successfully.');
    }

    /**
     * Display the specified parameter content.
     */
    public function show(ParameterContent $parameterContent)
    {
        // Check if user can view this content
        if (!Gate::allows('view', $parameterContent)) {
            abort(403, 'You do not have permission to view this content.');
        }

        $parameterContent->load(['parameter', 'user', 'college', 'academicYear']);

        return view('user.parameter-contents.show', compact('parameterContent'));
    }

    /**
     * Show the form for editing parameter content.
     */
    public function edit(ParameterContent $parameterContent)
    {
        // Check if user can edit this content
        if (!Gate::allows('update', $parameterContent)) {
            abort(403, 'You do not have permission to edit this content.');
        }

        // Cannot edit submitted content
        if ($parameterContent->status === 'submitted') {
            return redirect()->route('user.parameter-contents.show', $parameterContent)
                ->with('error', 'Cannot edit submitted content.');
        }

        $parameterContent->load(['parameter', 'academicYear']);
        
        $academicYears = \App\Models\AcademicYear::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('user.parameter-contents.edit', compact(
            'parameterContent',
            'academicYears'
        ));
    }

    /**
     * Update the specified parameter content.
     */
    public function update(Request $request, ParameterContent $parameterContent)
    {
        // Check if user can update this content
        if (!Gate::allows('update', $parameterContent)) {
            abort(403, 'You do not have permission to update this content.');
        }

        // Cannot update submitted content
        if ($parameterContent->status === 'submitted') {
            return redirect()->route('user.parameter-contents.show', $parameterContent)
                ->with('error', 'Cannot update submitted content.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'content' => 'required|string',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
            'remove_attachments' => 'nullable|array',
        ]);

        $parameterContent->update([
            'academic_year_id' => $request->academic_year_id,
            'content' => $request->content,
            'notes' => $request->notes,
            'meta' => $request->meta ?? [],
            'updated_at' => now(),
        ]);

        // Handle attachment removal
        if ($request->filled('remove_attachments')) {
            $attachments = $parameterContent->attachments ?? [];
            foreach ($request->remove_attachments as $index) {
                if (isset($attachments[$index])) {
                    // Delete file from storage
                    \Storage::disk('public')->delete($attachments[$index]['path']);
                    unset($attachments[$index]);
                }
            }
            $parameterContent->update(['attachments' => array_values($attachments)]);
        }

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            $existingAttachments = $parameterContent->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('parameter-contents', $filename, 'public');
                $existingAttachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
            $parameterContent->update(['attachments' => $existingAttachments]);
        }

        return redirect()->route('user.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content updated successfully.');
    }

    /**
     * Remove the specified parameter content.
     */
    public function destroy(ParameterContent $parameterContent)
    {
        // Check if user can delete this content
        if (!Gate::allows('delete', $parameterContent)) {
            abort(403, 'You do not have permission to delete this content.');
        }

        // Cannot delete submitted content
        if ($parameterContent->status === 'submitted') {
            return redirect()->route('user.parameter-contents.index')
                ->with('error', 'Cannot delete submitted content.');
        }

        // Delete associated files
        if ($parameterContent->attachments) {
            foreach ($parameterContent->attachments as $attachment) {
                \Storage::disk('public')->delete($attachment['path']);
            }
        }

        $parameterContent->delete();

        return redirect()->route('user.parameter-contents.index')
            ->with('success', 'Parameter content deleted successfully.');
    }

    /**
     * Submit parameter content for review.
     */
    public function submit(Request $request, ParameterContent $parameterContent)
    {
        // Check if user can submit this content
        if (!Gate::allows('update', $parameterContent)) {
            abort(403, 'You do not have permission to submit this content.');
        }

        // Can only submit draft content
        if ($parameterContent->status !== 'draft') {
            return redirect()->route('user.parameter-contents.show', $parameterContent)
                ->with('error', 'Only draft content can be submitted.');
        }

        // Validate that content is complete
        if (empty($parameterContent->content)) {
            return redirect()->route('user.parameter-contents.edit', $parameterContent)
                ->with('error', 'Content cannot be empty when submitting.');
        }

        $parameterContent->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('user.parameter-contents.show', $parameterContent)
            ->with('success', 'Parameter content submitted successfully.');
    }
}