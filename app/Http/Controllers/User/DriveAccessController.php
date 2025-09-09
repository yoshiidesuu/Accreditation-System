<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ParameterContent;
use App\Services\DriveFileManager;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DriveAccessController extends Controller
{
    protected $driveFileManager;
    protected $activityLogger;

    public function __construct(DriveFileManager $driveFileManager, ActivityLogger $activityLogger)
    {
        $this->driveFileManager = $driveFileManager;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display user's Google Drive files
     */
    public function index(Request $request)
    {
        $query = ParameterContent::where('storage_driver', 'gdrive')
            ->where('user_id', Auth::id())
            ->with(['parameter', 'college', 'academicYear']);

        // Filter by permission status
        if ($request->filled('status')) {
            $query->where('permission_status', $request->status);
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
                  });
            });
        }

        $driveFiles = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get data for filters
        $colleges = Auth::user()->colleges ?? collect();
        $statusOptions = [
            'requested' => 'Access Requested',
            'granted' => 'Access Granted',
            'denied' => 'Access Denied',
            'expired' => 'Access Expired'
        ];

        return view('user.drive-access.index', compact(
            'driveFiles',
            'colleges',
            'statusOptions'
        ));
    }

    /**
     * Show details of a Google Drive file
     */
    public function show(ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            abort(404, 'Not a Google Drive file');
        }

        $parameterContent->load(['parameter', 'college', 'academicYear']);
        
        // Get file metadata
        $metadata = $parameterContent->file_metadata ?? [];

        // Check if file is accessible
        $isAccessible = $this->driveFileManager->checkPermission($parameterContent);

        return view('user.drive-access.show', compact(
            'parameterContent',
            'metadata',
            'isAccessible'
        ));
    }

    /**
     * Request access to a Google Drive file
     */
    public function requestAccess(Request $request, ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $request->validate([
            'message' => 'nullable|string|max:500'
        ]);

        $success = $this->driveFileManager->requestPermission(
            $parameterContent,
            $request->message ?? ''
        );

        if ($success) {
            // Log the access request activity
            $this->activityLogger->logAccessRequest(
                $parameterContent,
                'requested',
                ['message' => $request->message ?? '']
            );

            return response()->json([
                'success' => true,
                'message' => 'Access request submitted successfully'
            ]);
        }

        return response()->json(['error' => 'Failed to submit access request'], 500);
    }

    /**
     * Upload a new Google Drive file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'parameter_id' => 'required|exists:parameters,id',
            'college_id' => 'required|exists:colleges,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'share_link' => 'required|url',
            'content' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            // Create parameter content with Google Drive link
            $parameterContent = new ParameterContent([
                'parameter_id' => $request->parameter_id,
                'user_id' => Auth::id(),
                'college_id' => $request->college_id,
                'academic_year_id' => $request->academic_year_id,
                'content' => $request->content,
                'notes' => $request->notes,
                'storage_driver' => 'gdrive',
                'share_link' => $request->share_link,
                'status' => 'draft'
            ]);

            // Store the file using DriveFileManager
            $result = $this->driveFileManager->storeFile(
                $parameterContent,
                $request->share_link
            );

            if ($result) {
                // Log the file upload activity
                $this->activityLogger->logFileUpload(
                    $parameterContent,
                    'gdrive',
                    ['share_link' => $request->share_link]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Google Drive file uploaded successfully',
                    'parameter_content_id' => $parameterContent->id
                ]);
            }

            return response()->json(['error' => 'Failed to upload Google Drive file'], 500);
        } catch (\Exception $e) {
            Log::error('Google Drive file upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Google Drive file metadata
     */
    public function update(Request $request, ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $request->validate([
            'content' => 'nullable|string',
            'notes' => 'nullable|string',
            'share_link' => 'nullable|url',
        ]);

        try {
            // Update basic fields
            if ($request->filled('content')) {
                $parameterContent->content = $request->content;
            }

            if ($request->filled('notes')) {
                $parameterContent->notes = $request->notes;
            }

            // If share link is updated, refresh metadata
            if ($request->filled('share_link') && $request->share_link !== $parameterContent->share_link) {
                $parameterContent->share_link = $request->share_link;
                
                // Update file metadata
                $result = $this->driveFileManager->storeFile(
                    $parameterContent,
                    $request->share_link
                );

                if (!$result) {
                    return response()->json(['error' => 'Failed to update Google Drive file metadata'], 500);
                }
            } else {
                $parameterContent->save();
            }

            // Log the file edit activity
            $this->activityLogger->logFileEdit(
                $parameterContent,
                'gdrive',
                $request->only(['content', 'notes', 'share_link'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Google Drive file updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Google Drive file update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a Google Drive file reference
     */
    public function destroy(ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        try {
            // Log the file deletion activity before deleting
            $this->activityLogger->logFileDelete(
                $parameterContent,
                'gdrive',
                ['drive_file_id' => $parameterContent->drive_file_id]
            );

            $parameterContent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Google Drive file reference deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Google Drive file deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Deletion failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get access URL for a Google Drive file
     */
    public function getAccessUrl(ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $accessUrl = $this->driveFileManager->getAccessUrl($parameterContent);

        if ($accessUrl) {
            // Log the file download/access activity
            $this->activityLogger->logFileDownload(
                $parameterContent,
                'gdrive',
                ['access_url_generated' => true]
            );

            return response()->json([
                'success' => true,
                'access_url' => $accessUrl
            ]);
        }

        return response()->json(['error' => 'Unable to generate access URL'], 500);
    }

    /**
     * Check file accessibility status
     */
    public function checkAccess(ParameterContent $parameterContent)
    {
        // Check if user owns this file
        if ($parameterContent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        if ($parameterContent->storage_driver !== 'gdrive') {
            return response()->json(['error' => 'Not a Google Drive file'], 400);
        }

        $isAccessible = $this->driveFileManager->checkPermission($parameterContent);

        return response()->json([
            'success' => true,
            'is_accessible' => $isAccessible,
            'permission_status' => $parameterContent->permission_status,
            'requires_permission' => $parameterContent->requires_permission
        ]);
    }
}