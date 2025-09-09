<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display audit logs.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Event filter
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Subject type filter
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        $activities = $query->paginate(20);

        return view('admin.audit.index', compact('activities'));
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        $activities = $query->limit(5000)->get(); // Limit to prevent memory issues

        $csvData = [];
        $csvData[] = [
            'Date/Time',
            'User Name',
            'User Email',
            'Event',
            'Subject Type',
            'Subject ID',
            'Description',
            'IP Address',
            'Changes'
        ];

        foreach ($activities as $activity) {
            $changes = '';
            if ($activity->properties && $activity->properties->has('attributes')) {
                $attributes = $activity->properties['attributes'];
                $old = $activity->properties['old'] ?? [];
                
                $changeDetails = [];
                foreach ($attributes as $key => $value) {
                    $oldValue = $old[$key] ?? 'N/A';
                    $changeDetails[] = "{$key}: {$oldValue} → {$value}";
                }
                $changes = implode('; ', $changeDetails);
            }

            $csvData[] = [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->causer ? $activity->causer->name : 'System',
                $activity->causer ? $activity->causer->email : 'N/A',
                ucfirst($activity->event),
                class_basename($activity->subject_type),
                $activity->subject_id,
                $activity->description,
                $activity->properties['ip'] ?? 'N/A',
                $changes
            ];
        }

        $filename = 'audit_logs_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Show detailed activity log.
     */
    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);
        
        return view('admin.audit.show', compact('activity'));
    }

    /**
     * Get activity statistics for dashboard.
     */
    public function stats()
    {
        $stats = [
            'total_activities' => Activity::count(),
            'today_activities' => Activity::whereDate('created_at', Carbon::today())->count(),
            'week_activities' => Activity::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'month_activities' => Activity::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        // Recent activities by event type
        $eventStats = Activity::selectRaw('event, COUNT(*) as count')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('event')
            ->pluck('count', 'event')
            ->toArray();

        // Most active users
        $activeUsers = Activity::with('causer')
            ->whereNotNull('causer_id')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('causer_id, COUNT(*) as activity_count')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'event_stats' => $eventStats,
            'active_users' => $activeUsers
        ]);
    }

    /**
     * Clean old audit logs.
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365'
        ]);

        $cutoffDate = Carbon::now()->subDays($request->days);
        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Cleaned up {$deletedCount} audit log entries older than {$request->days} days");

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} old audit log entries.",
            'deleted_count' => $deletedCount
        ]);
    }
}