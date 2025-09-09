<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class CollegeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of colleges.
     */
    public function index(Request $request)
    {
        $query = College::with('coordinator');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('coordinator', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $colleges = $query->paginate(10);

        return view('admin.colleges.index', compact('colleges'));
    }

    /**
     * Show the form for creating a new college.
     */
    public function create()
    {
        $coordinators = User::role('overall_coordinator')->get();
        return view('admin.colleges.create', compact('coordinators'));
    }

    /**
     * Store a newly created college in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:colleges,code',
            'address' => 'nullable|string|max:1000',
            'contact' => 'nullable|string|max:255',
            'coordinator_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $college = College::create($request->only([
            'name', 'code', 'address', 'contact', 'coordinator_id'
        ]));

        activity()
            ->performedOn($college)
            ->causedBy(auth()->user())
            ->log('College created');

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College created successfully.');
    }

    /**
     * Display the specified college.
     */
    public function show(College $college)
    {
        $college->load('coordinator', 'users');
        return view('admin.colleges.show', compact('college'));
    }

    /**
     * Show the form for editing the specified college.
     */
    public function edit(College $college)
    {
        $coordinators = User::role('overall_coordinator')->get();
        return view('admin.colleges.edit', compact('college', 'coordinators'));
    }

    /**
     * Update the specified college in storage.
     */
    public function update(Request $request, College $college)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:colleges,code,' . $college->id,
            'address' => 'nullable|string|max:1000',
            'contact' => 'nullable|string|max:255',
            'coordinator_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldData = $college->toArray();
        $college->update($request->only([
            'name', 'code', 'address', 'contact', 'coordinator_id'
        ]));

        activity()
            ->performedOn($college)
            ->causedBy(auth()->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $college->fresh()->toArray()
            ])
            ->log('College updated');

        return redirect()->route('admin.colleges.index')
            ->with('success', 'College updated successfully.');
    }

    /**
     * Remove the specified college from storage.
     */
    public function destroy(College $college)
    {
        $collegeName = $college->name;
        
        activity()
            ->performedOn($college)
            ->causedBy(auth()->user())
            ->withProperties(['deleted_college' => $college->toArray()])
            ->log('College deleted');

        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('success', "College '{$collegeName}' deleted successfully.");
    }
}