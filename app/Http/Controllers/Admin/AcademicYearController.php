<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of academic years
     */
    public function index(Request $request)
    {
        $query = AcademicYear::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhere('start_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('active', false);
            } elseif ($request->status === 'current') {
                $query->current();
            }
        }

        $academicYears = $query->orderBy('start_date', 'desc')
                              ->paginate(10)
                              ->withQueryString();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new academic year
     */
    public function create()
    {
        return view('admin.academic-years.create');
    }

    /**
     * Store a newly created academic year
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255|unique:academic_years,label',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'boolean'
        ]);

        // Ensure dates don't overlap with existing academic years
        $this->validateDateOverlap($validated['start_date'], $validated['end_date']);

        DB::transaction(function () use ($validated) {
            $academicYear = AcademicYear::create($validated);
            
            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($academicYear)
                ->withProperties([
                    'attributes' => $academicYear->toArray()
                ])
                ->log('Academic year created');
        });

        return redirect()->route('admin.academic-years.index')
                        ->with('success', 'Academic year created successfully.');
    }

    /**
     * Display the specified academic year
     */
    public function show(AcademicYear $academicYear)
    {
        return view('admin.academic-years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified academic year
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_years', 'label')->ignore($academicYear->id)
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'boolean'
        ]);

        // Ensure dates don't overlap with existing academic years (except current one)
        $this->validateDateOverlap($validated['start_date'], $validated['end_date'], $academicYear->id);

        DB::transaction(function () use ($academicYear, $validated) {
            $oldAttributes = $academicYear->toArray();
            $academicYear->update($validated);
            
            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($academicYear)
                ->withProperties([
                    'old' => $oldAttributes,
                    'attributes' => $academicYear->fresh()->toArray()
                ])
                ->log('Academic year updated');
        });

        return redirect()->route('admin.academic-years.show', $academicYear)
                        ->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified academic year
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Prevent deletion of active academic year
        if ($academicYear->active) {
            return redirect()->route('admin.academic-years.index')
                           ->with('error', 'Cannot delete the active academic year.');
        }

        DB::transaction(function () use ($academicYear) {
            // Log the activity before deletion
            activity()
                ->causedBy(auth()->user())
                ->performedOn($academicYear)
                ->withProperties([
                    'old' => $academicYear->toArray()
                ])
                ->log('Academic year deleted');

            $academicYear->delete();
        });

        return redirect()->route('admin.academic-years.index')
                        ->with('success', 'Academic year deleted successfully.');
    }

    /**
     * Toggle active status of academic year
     */
    public function toggleActive(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            $oldStatus = $academicYear->active;
            $academicYear->update(['active' => !$oldStatus]);
            
            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($academicYear)
                ->withProperties([
                    'old' => ['active' => $oldStatus],
                    'attributes' => ['active' => !$oldStatus]
                ])
                ->log('Academic year status toggled');
        });

        $status = $academicYear->fresh()->active ? 'activated' : 'deactivated';
        return redirect()->back()
                        ->with('success', "Academic year {$status} successfully.");
    }

    /**
     * Validate date overlap with existing academic years
     */
    private function validateDateOverlap($startDate, $endDate, $excludeId = null)
    {
        $query = AcademicYear::where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => 'The date range overlaps with an existing academic year.',
                'end_date' => 'The date range overlaps with an existing academic year.'
            ]);
        }
    }
}