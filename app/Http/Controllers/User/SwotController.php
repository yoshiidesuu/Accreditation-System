<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SwotEntry;
use App\Models\College;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SwotController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware(['auth', 'role:chairperson|faculty']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SwotEntry::with(['college', 'area', 'creator'])
            ->where('created_by', Auth::id());

        // Filter by college if user has specific college access
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Filter by area
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $swotEntries = $query->latest()->paginate(15);
        $colleges = College::all();
        $areas = Area::all();

        return view('user.swot.index', compact('swotEntries', 'colleges', 'areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colleges = College::all();
        $areas = Area::all();
        $types = SwotEntry::getTypes();

        return view('user.swot.create', compact('colleges', 'areas', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'area_id' => 'required|exists:areas,id',
            'type' => 'required|in:S,W,O,T',
            'description' => 'required|string|max:2000',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = SwotEntry::STATUS_PENDING;

        SwotEntry::create($validated);

        return redirect()->route('user.swot.index')
            ->with('success', 'SWOT entry created successfully and is pending review.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SwotEntry $swot)
    {
        $this->authorize('view', $swot);
        
        return view('user.swot.show', compact('swot'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SwotEntry $swot)
    {
        $this->authorize('update', $swot);
        
        $colleges = College::all();
        $areas = Area::all();
        $types = SwotEntry::getTypes();

        return view('user.swot.edit', compact('swot', 'colleges', 'areas', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SwotEntry $swot)
    {
        $this->authorize('update', $swot);
        
        $validated = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'area_id' => 'required|exists:areas,id',
            'type' => 'required|in:S,W,O,T',
            'description' => 'required|string|max:2000',
        ]);

        // Reset status to pending if content changed
        if ($swot->description !== $validated['description'] || 
            $swot->type !== $validated['type'] ||
            $swot->college_id != $validated['college_id'] ||
            $swot->area_id != $validated['area_id']) {
            $validated['status'] = SwotEntry::STATUS_PENDING;
            $validated['reviewed_by'] = null;
            $validated['reviewed_at'] = null;
            $validated['notes'] = null;
        }

        $swot->update($validated);

        return redirect()->route('user.swot.index')
            ->with('success', 'SWOT entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SwotEntry $swot)
    {
        $this->authorize('delete', $swot);
        
        $swot->delete();

        return redirect()->route('user.swot.index')
            ->with('success', 'SWOT entry deleted successfully.');
    }
}
