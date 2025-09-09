<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParameterController extends Controller
{
    /**
     * Display a listing of parameters
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Parameter::with(['area.college']);
        
        // Filter by user's accessible areas
        if ($user->hasRole('coordinator')) {
            $query->whereHas('area.college', function($q) use ($user) {
                $q->where('coordinator_id', $user->id);
            });
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('required')) {
            $query->where('required', $request->required === 'true');
        }
        
        $parameters = $query->orderBy('order')->paginate(15);
        
        // Get areas for filter dropdown
        $areas = $user->hasRole('coordinator') 
            ? Area::whereHas('college', function($q) use ($user) {
                $q->where('coordinator_id', $user->id);
              })->get()
            : Area::all();
        
        return view('user.parameters.index', compact('parameters', 'areas'));
    }

    /**
     * Show the form for creating a new parameter
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get areas based on user role
        $areas = $user->hasRole('coordinator') 
            ? Area::whereHas('college', function($q) use ($user) {
                $q->where('coordinator_id', $user->id);
              })->get()
            : Area::all();
        
        return view('user.parameters.create', compact('areas'));
    }

    /**
     * Store a newly created parameter
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'code' => 'required|string|max:50|unique:parameters,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,email,url,date,select,checkbox,radio,file',
            'required' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'active' => 'boolean'
        ]);
        
        // Verify user can create parameter for this area
        $area = Area::findOrFail($validated['area_id']);
        $this->authorize('create', [Parameter::class, $area]);
        
        $validated['required'] = $request->has('required');
        $validated['active'] = $request->has('active');
        
        // Handle JSON fields
        $validated['options'] = $request->input('options', []);
        $validated['validation_rules'] = $request->input('validation_rules', []);
        
        $parameter = Parameter::create($validated);
        
        return redirect()->route('user.parameters.show', $parameter)
            ->with('success', 'Parameter created successfully.');
    }

    /**
     * Display the specified parameter
     */
    public function show(Parameter $parameter)
    {
        $this->authorize('view', $parameter);
        
        $parameter->load(['area.college']);
        
        return view('user.parameters.show', compact('parameter'));
    }

    /**
     * Show the form for editing the specified parameter
     */
    public function edit(Parameter $parameter)
    {
        $this->authorize('update', $parameter);
        
        $user = Auth::user();
        
        $areas = $user->hasRole('coordinator') 
            ? Area::whereHas('college', function($q) use ($user) {
                $q->where('coordinator_id', $user->id);
              })->get()
            : Area::all();
        
        return view('user.parameters.edit', compact('parameter', 'areas'));
    }

    /**
     * Update the specified parameter
     */
    public function update(Request $request, Parameter $parameter)
    {
        $this->authorize('update', $parameter);
        
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'code' => 'required|string|max:50|unique:parameters,code,' . $parameter->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,email,url,date,select,checkbox,radio,file',
            'required' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'active' => 'boolean'
        ]);
        
        $validated['required'] = $request->has('required');
        $validated['active'] = $request->has('active');
        
        // Handle JSON fields
        $validated['options'] = $request->input('options', []);
        $validated['validation_rules'] = $request->input('validation_rules', []);
        
        $parameter->update($validated);
        
        return redirect()->route('user.parameters.show', $parameter)
            ->with('success', 'Parameter updated successfully.');
    }

    /**
     * Remove the specified parameter
     */
    public function destroy(Parameter $parameter)
    {
        $this->authorize('delete', $parameter);
        
        // Check if parameter has content
        if ($parameter->contents()->count() > 0) {
            return back()->with('error', 'Cannot delete parameter with existing content.');
        }
        
        $parameter->delete();
        
        return redirect()->route('user.parameters.index')
            ->with('success', 'Parameter deleted successfully.');
    }
}