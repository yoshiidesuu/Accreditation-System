<?php

namespace App\Http\Controllers\Accreditor;

use App\Http\Controllers\Controller;
use App\Models\SwotEntry;
use App\Models\College;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SwotReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:accreditor');
    }

    /**
     * Display the SWOT review queue
     */
    public function index(Request $request)
    {
        $query = SwotEntry::with(['college', 'area', 'creator'])
            ->where('status', SwotEntry::STATUS_PENDING);

        // Filter by college
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

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $swotEntries = $query->orderBy('created_at', 'asc')->paginate(20);
        $colleges = College::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('accreditor.swot-review.index', compact('swotEntries', 'colleges', 'areas'));
    }

    /**
     * Show detailed view of a SWOT entry for review
     */
    public function show(SwotEntry $swotEntry)
    {
        $this->authorize('view', $swotEntry);
        
        $swotEntry->load(['college', 'area', 'creator', 'reviewer']);
        
        return view('accreditor.swot-review.show', compact('swotEntry'));
    }

    /**
     * Approve a SWOT entry
     */
    public function approve(Request $request, SwotEntry $swotEntry)
    {
        $this->authorize('review', $swotEntry);

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($swotEntry, $request) {
            $swotEntry->update([
                'status' => SwotEntry::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'notes' => $request->notes
            ]);
        });

        return redirect()->route('accreditor.swot-review.index')
            ->with('success', 'SWOT entry approved successfully.');
    }

    /**
     * Reject a SWOT entry
     */
    public function reject(Request $request, SwotEntry $swotEntry)
    {
        $this->authorize('review', $swotEntry);

        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        DB::transaction(function () use ($swotEntry, $request) {
            $swotEntry->update([
                'status' => SwotEntry::STATUS_REJECTED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'notes' => $request->notes
            ]);
        });

        return redirect()->route('accreditor.swot-review.index')
            ->with('success', 'SWOT entry rejected with feedback.');
    }

    /**
     * Bulk approve multiple SWOT entries
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'swot_ids' => 'required|array',
            'swot_ids.*' => 'exists:swot_entries,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $swotEntries = SwotEntry::whereIn('id', $request->swot_ids)
            ->where('status', SwotEntry::STATUS_PENDING)
            ->get();

        foreach ($swotEntries as $entry) {
            $this->authorize('review', $entry);
        }

        DB::transaction(function () use ($swotEntries, $request) {
            SwotEntry::whereIn('id', $swotEntries->pluck('id'))
                ->update([
                    'status' => SwotEntry::STATUS_APPROVED,
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'notes' => $request->notes
                ]);
        });

        return redirect()->route('accreditor.swot-review.index')
            ->with('success', count($swotEntries) . ' SWOT entries approved successfully.');
    }

    /**
     * Get statistics for the review dashboard
     */
    public function stats()
    {
        $stats = [
            'pending' => SwotEntry::where('status', SwotEntry::STATUS_PENDING)->count(),
            'approved' => SwotEntry::where('status', SwotEntry::STATUS_APPROVED)->count(),
            'rejected' => SwotEntry::where('status', SwotEntry::STATUS_REJECTED)->count(),
            'by_type' => SwotEntry::select('type', DB::raw('count(*) as count'))
                ->where('status', SwotEntry::STATUS_PENDING)
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_college' => SwotEntry::with('college')
                ->select('college_id', DB::raw('count(*) as count'))
                ->where('status', SwotEntry::STATUS_PENDING)
                ->groupBy('college_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->college->name => $item->count];
                })
                ->toArray()
        ];

        return response()->json($stats);
    }
}
