<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollegeController extends Controller
{
    /**
     * Display a listing of colleges based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get colleges based on user role
        if ($user->hasRole('coordinator')) {
            // Coordinators can see colleges they coordinate
            $colleges = College::where('coordinator_id', $user->id)
                ->with(['coordinator', 'academicYear'])
                ->paginate(10);
        } else {
            // Faculty can see all colleges in their academic year
            $colleges = College::with(['coordinator', 'academicYear'])
                ->whereHas('academicYear', function($query) {
                    $query->where('active', true);
                })
                ->paginate(10);
        }
        
        return view('user.colleges.index', compact('colleges'));
    }

    /**
     * Display the specified college
     */
    public function show(College $college)
    {
        $this->authorize('view', $college);
        
        $college->load(['coordinator', 'academicYear', 'areas']);
        
        return view('user.colleges.show', compact('college'));
    }

    /**
     * Show the form for editing the specified college
     */
    public function edit(College $college)
    {
        $this->authorize('update', $college);
        
        $college->load(['coordinator', 'academicYear']);
        
        return view('user.colleges.edit', compact('college'));
    }

    /**
     * Update the specified college
     */
    public function update(Request $request, College $college)
    {
        $this->authorize('update', $college);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'meta' => 'nullable|array'
        ]);
        
        $college->update($validated);
        
        return redirect()->route('user.colleges.show', $college)
            ->with('success', 'College updated successfully.');
    }
}