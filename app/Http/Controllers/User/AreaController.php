<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\College;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    /**
     * Display a listing of areas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Area::with(['college', 'academicYear', 'parent']);
        
        // Filter by user's colleges if coordinator
        if ($user->hasRole('coordinator')) {
            $query->whereHas('college', function($q) use ($user) {
                $q->where('coordinator_id', $user->id);
            });
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }
        
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        $areas = $query->paginate(15);
        
        // Get colleges for filter dropdown
        $colleges = $user->hasRole('coordinator') 
            ? College::where('coordinator_id', $user->id)->get()
            : College::all();
        
        return view('user.areas.index', compact('areas', 'colleges'));
    }

    /**
     * Show the form for creating a new area
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get colleges based on user role
        $colleges = $user->hasRole('coordinator') 
            ? College::where('coordinator_id', $user->id)->get()
            : College::all();
            
        $academicYears = AcademicYear::where('active', true)->get();
        $parentAreas = Area::whereNull('parent_id')->get();
        
        return view('user.areas.create', compact('colleges', 'academicYears', 'parentAreas'));
    }

    /**
     * Store a newly created area
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'parent_id' => 'nullable|exists:areas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:areas,code',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1|max:5',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);
        
        // Verify user can create area for this college
        $college = College::findOrFail($validated['college_id']);
        $this->authorize('create', [Area::class, $college]);
        
        $validated['active'] = $request->has('active');
        
        $area = Area::create($validated);
        
        return redirect()->route('user.areas.show', $area)
            ->with('success', 'Area created successfully.');
    }

    /**
     * Display the specified area
     */
    public function show(Area $area)
    {
        $this->authorize('view', $area);
        
        $area->load(['college', 'academicYear', 'parent', 'children', 'parameters']);
        
        return view('user.areas.show', compact('area'));
    }

    /**
     * Show the form for editing the specified area
     */
    public function edit(Area $area)
    {
        $this->authorize('update', $area);
        
        $user = Auth::user();
        
        $colleges = $user->hasRole('coordinator') 
            ? College::where('coordinator_id', $user->id)->get()
            : College::all();
            
        $academicYears = AcademicYear::where('active', true)->get();
        $parentAreas = Area::whereNull('parent_id')->where('id', '!=', $area->id)->get();
        
        return view('user.areas.edit', compact('area', 'colleges', 'academicYears', 'parentAreas'));
    }

    /**
     * Update the specified area
     */
    public function update(Request $request, Area $area)
    {
        $this->authorize('update', $area);
        
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'parent_id' => 'nullable|exists:areas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:areas,code,' . $area->id,
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1|max:5',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);
        
        $validated['active'] = $request->has('active');
        
        $area->update($validated);
        
        return redirect()->route('user.areas.show', $area)
            ->with('success', 'Area updated successfully.');
    }

    /**
     * Remove the specified area
     */
    public function destroy(Area $area)
    {
        $this->authorize('delete', $area);
        
        // Check if area has children or parameters
        if ($area->children()->count() > 0) {
            return back()->with('error', 'Cannot delete area with sub-areas.');
        }
        
        if ($area->parameters()->count() > 0) {
            return back()->with('error', 'Cannot delete area with parameters.');
        }
        
        $area->delete();
        
        return redirect()->route('user.areas.index')
            ->with('success', 'Area deleted successfully.');
    }
}