<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\College;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of areas.
     */
    public function index(Request $request): View
    {
        $query = Area::with(['college', 'academicYear', 'parentArea', 'childAreas'])
            ->orderBy('college_id')
            ->orderBy('academic_year_id')
            ->orderBy('parent_area_id')
            ->orderBy('code');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('college', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('academicYear', function ($q) use ($search) {
                      $q->where('label', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->get('college_id'));
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->get('academic_year_id'));
        }

        // Filter by parent area (root areas only)
        if ($request->filled('root_only')) {
            $query->whereNull('parent_area_id');
        }

        $areas = $query->paginate(15)->withQueryString();
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.areas.index', compact('areas', 'colleges', 'academicYears'));
    }

    /**
     * Show the form for creating a new area.
     */
    public function create(Request $request): View
    {
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get parent areas based on selected college and academic year
        $parentAreas = collect();
        if ($request->filled(['college_id', 'academic_year_id'])) {
            $parentAreas = Area::where('college_id', $request->get('college_id'))
                ->where('academic_year_id', $request->get('academic_year_id'))
                ->orderBy('title')
                ->get();
        }

        return view('admin.areas.create', compact('colleges', 'academicYears', 'parentAreas'));
    }

    /**
     * Store a newly created area in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:areas,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_area_id' => ['nullable', 'exists:areas,id'],
            'college_id' => ['required', 'exists:colleges,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        // Validate parent area belongs to same college and academic year
        if ($validated['parent_area_id']) {
            $parentArea = Area::find($validated['parent_area_id']);
            if ($parentArea->college_id != $validated['college_id'] || 
                $parentArea->academic_year_id != $validated['academic_year_id']) {
                return back()->withErrors([
                    'parent_area_id' => 'Parent area must belong to the same college and academic year.'
                ])->withInput();
            }
        }

        $area = Area::create($validated);

        activity()
            ->performedOn($area)
            ->causedBy(auth()->user())
            ->log('Area created');

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area created successfully.');
    }

    /**
     * Display the specified area.
     */
    public function show(Area $area): View
    {
        $area->load(['college', 'academicYear', 'parentArea', 'childAreas.childAreas', 'parameters']);
        
        return view('admin.areas.show', compact('area'));
    }

    /**
     * Show the form for editing the specified area.
     */
    public function edit(Area $area): View
    {
        $colleges = College::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        
        // Get potential parent areas (exclude self and descendants)
        $excludeIds = collect([$area->id]);
        $this->addDescendantIds($area, $excludeIds);
        
        $parentAreas = Area::where('college_id', $area->college_id)
            ->where('academic_year_id', $area->academic_year_id)
            ->whereNotIn('id', $excludeIds->toArray())
            ->orderBy('title')
            ->get();

        return view('admin.areas.edit', compact('area', 'colleges', 'academicYears', 'parentAreas'));
    }

    /**
     * Update the specified area in storage.
     */
    public function update(Request $request, Area $area): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('areas')->ignore($area->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_area_id' => ['nullable', 'exists:areas,id'],
            'college_id' => ['required', 'exists:colleges,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        // Validate parent area
        if ($validated['parent_area_id']) {
            // Cannot be parent of itself
            if ($validated['parent_area_id'] == $area->id) {
                return back()->withErrors([
                    'parent_area_id' => 'Area cannot be parent of itself.'
                ])->withInput();
            }

            // Parent must belong to same college and academic year
            $parentArea = Area::find($validated['parent_area_id']);
            if ($parentArea->college_id != $validated['college_id'] || 
                $parentArea->academic_year_id != $validated['academic_year_id']) {
                return back()->withErrors([
                    'parent_area_id' => 'Parent area must belong to the same college and academic year.'
                ])->withInput();
            }

            // Cannot create circular reference
            if ($this->wouldCreateCircularReference($area, $validated['parent_area_id'])) {
                return back()->withErrors([
                    'parent_area_id' => 'This would create a circular reference.'
                ])->withInput();
            }
        }

        $area->update($validated);

        activity()
            ->performedOn($area)
            ->causedBy(auth()->user())
            ->log('Area updated');

        return redirect()->route('admin.areas.show', $area)
            ->with('success', 'Area updated successfully.');
    }

    /**
     * Remove the specified area from storage.
     */
    public function destroy(Area $area): RedirectResponse
    {
        // Check if area has children
        if ($area->hasChildren()) {
            return back()->with('error', 'Cannot delete area that has child areas. Please delete or reassign child areas first.');
        }

        // Check if area has parameters
        if ($area->parameters()->exists()) {
            return back()->with('error', 'Cannot delete area that has parameters. Please delete parameters first.');
        }

        activity()
            ->performedOn($area)
            ->causedBy(auth()->user())
            ->log('Area deleted');

        $area->delete();

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area deleted successfully.');
    }

    /**
     * Get areas by college and academic year (AJAX).
     */
    public function getByCollegeAndYear(Request $request)
    {
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $areas = Area::where('college_id', $request->college_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->orderBy('title')
            ->get(['id', 'title', 'code', 'parent_area_id']);

        return response()->json($areas);
    }

    /**
     * Recursively add descendant IDs to exclude list.
     */
    private function addDescendantIds(Area $area, &$excludeIds): void
    {
        $children = $area->childAreas;
        foreach ($children as $child) {
            $excludeIds->push($child->id);
            $this->addDescendantIds($child, $excludeIds);
        }
    }

    /**
     * Check if setting parent would create circular reference.
     */
    private function wouldCreateCircularReference(Area $area, int $parentId): bool
    {
        $current = Area::find($parentId);
        
        while ($current) {
            if ($current->id === $area->id) {
                return true;
            }
            $current = $current->parentArea;
        }
        
        return false;
    }
}