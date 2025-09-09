<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParameterContent;
use App\Services\DriveFileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DrivePermissionController extends Controller
{
    protected $driveFileManager;

    public function __construct(DriveFileManager $driveFileManager)
    {
        $this->driveFileManager = $driveFileManager;
    }

    /**
     * Display pending permission requests
     */
    public function index(Request $request)
    {
        $query = ParameterContent::where('storage_driver', 'gdrive')
            ->with(['parameter', 'user', 'college', 'academicYear']);

        // Filter by permission status
        if ($request->filled('status')) {
            $query->where('permission_status', $request->status);
        } else {
            // Default to showing pending requests
            $query->where('permission_status', 'requested');
        }

        // Filter by college
        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('drive_file_id', 'like', "%{$search}%")
                  ->orWhere('share_link', 'like', "%{$search}%")
                  ->orWhereHas('parameter', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $permissionRequests = $query->orderBy('permission_requested_at', 'desc')
            ->paginate(15);

        // Get data for filters
        $colleges = \App\Models\College::orderBy('name')->get();
        $statusOptions = [
            'requested' => 'Requested',
            'granted' => 'Granted',
            'denied' => 'Denied',
            'expired' => 'Expired'
        ];

        return view('admin.drive-permissions.index', compact(
            'permissionRequests',
            'colleges',
            'statusOptions'
        ));
    }

    /**
     * Show details of a permission request
     */
    public function show(ParameterContent $parameterContent)
    {
        if ($parameterContent->storage_driver !== 'gdrive') {
            abort(404, 'Not a Google Drive file');
        }

        $parameterContent->load(['parameter', 'user', 'college', 'academicYear']);
        
        // Get file metadata
        $metadata = $parameterContent->file_metadata ?? [];

        return view('admin.drive-permissions.show', compact(
            'parameterContent',
            'metadata'
        ));
    }

    /**
     * Grant permission for a Google Drive file
     */
    public function grant(Request $request, ParameterContent $parameterContent)
    {
        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $success = $this->driveFileManager->grantPermission($parameterContent);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Permission granted successfully'
            ]);
        }

        return response()->json(['error' => 'Failed to grant permission'], 500);
    }

    /**
     * Deny permission for a Google Drive file
     */
    public function deny(Request $request, ParameterContent $parameterContent)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $success = $this->driveFileManager->denyPermission(
            $parameterContent,
            $request->reason ?? ''
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Permission denied successfully'
            ]);
        }

        return response()->json(['error' => 'Failed to deny permission'], 500);
    }

    /**
     * Bulk action for multiple permission requests
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:grant,deny',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:parameter_contents,id',
            'reason' => 'nullable|string|max:500'
        ]);

        $parameterContents = ParameterContent::whereIn('id', $request->ids)
            ->where('storage_driver', 'gdrive')
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($parameterContents as $parameterContent) {
            try {
                if ($request->action === 'grant') {
                    $success = $this->driveFileManager->grantPermission($parameterContent);
                } else {
                    $success = $this->driveFileManager->denyPermission(
                        $parameterContent,
                        $request->reason ?? ''
                    );
                }

                if ($success) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                Log::error('Bulk action failed for parameter content ' . $parameterContent->id . ': ' . $e->getMessage());
                $failCount++;
            }
        }

        $message = "Bulk action completed. {$successCount} successful";
        if ($failCount > 0) {
            $message .= ", {$failCount} failed";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);
    }

    /**
     * Get statistics for Drive permissions
     */
    public function stats()
    {
        $stats = [
            'total_drive_files' => ParameterContent::where('storage_driver', 'gdrive')->count(),
            'pending_requests' => ParameterContent::where('storage_driver', 'gdrive')
                ->where('permission_status', 'requested')->count(),
            'granted_permissions' => ParameterContent::where('storage_driver', 'gdrive')
                ->where('permission_status', 'granted')->count(),
            'denied_permissions' => ParameterContent::where('storage_driver', 'gdrive')
                ->where('permission_status', 'denied')->count(),
            'files_requiring_permission' => ParameterContent::where('storage_driver', 'gdrive')
                ->where('requires_permission', true)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Clean up old permission requests
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days_old' => 'nullable|integer|min:1|max:365'
        ]);

        $daysOld = $request->days_old ?? 30;
        $cleanedCount = $this->driveFileManager->cleanupOldRequests($daysOld);

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$cleanedCount} old permission requests",
            'cleaned_count' => $cleanedCount
        ]);
    }
}