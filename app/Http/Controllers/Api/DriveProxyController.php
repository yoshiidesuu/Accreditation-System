<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParameterContent;
use App\Services\DriveFileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DriveProxyController extends Controller
{
    protected $driveFileManager;

    public function __construct(DriveFileManager $driveFileManager)
    {
        $this->driveFileManager = $driveFileManager;
    }

    /**
     * Proxy Google Drive file downloads with permission validation
     */
    public function proxy(Request $request, string $fileId)
    {
        try {
            // Find the parameter content record
            $parameterContent = ParameterContent::where('drive_file_id', $fileId)
                ->orWhere('share_link', 'like', '%' . $fileId . '%')
                ->first();

            if (!$parameterContent) {
                return response()->json(['error' => 'File not found'], 404);
            }

            // Check permissions
            if (!$this->driveFileManager->hasPermission($parameterContent, auth()->user())) {
                return response()->json([
                    'error' => 'Access denied',
                    'requires_permission' => $parameterContent->requires_permission,
                    'permission_status' => $parameterContent->permission_status
                ], 403);
            }

            // Get the actual Google Drive download URL
            $downloadUrl = $this->getGoogleDriveDownloadUrl($parameterContent->share_link);
            
            if (!$downloadUrl) {
                return response()->json(['error' => 'Unable to generate download URL'], 500);
            }

            // Cache the download URL for a short time to avoid repeated processing
            $cacheKey = 'drive_download_' . $fileId;
            Cache::put($cacheKey, $downloadUrl, now()->addMinutes(5));

            // Redirect to the actual file or stream it
            if ($request->has('redirect')) {
                return redirect($downloadUrl);
            }

            // Stream the file content
            return $this->streamFile($downloadUrl, $parameterContent);

        } catch (\Exception $e) {
            Log::error('Drive proxy error: ' . $e->getMessage(), [
                'file_id' => $fileId,
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Request permission for a Google Drive file
     */
    public function requestPermission(Request $request, string $fileId)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:500'
        ]);

        try {
            $parameterContent = ParameterContent::where('drive_file_id', $fileId)
                ->orWhere('share_link', 'like', '%' . $fileId . '%')
                ->first();

            if (!$parameterContent) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $success = $this->driveFileManager->requestPermission(
                $parameterContent,
                $request->email,
                $request->message ?? ''
            );

            if ($success) {
                return response()->json([
                    'message' => 'Permission request submitted successfully',
                    'status' => 'requested'
                ]);
            }

            return response()->json(['error' => 'Failed to submit permission request'], 500);

        } catch (\Exception $e) {
            Log::error('Permission request error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get file information
     */
    public function info(string $fileId)
    {
        try {
            $parameterContent = ParameterContent::where('drive_file_id', $fileId)
                ->orWhere('share_link', 'like', '%' . $fileId . '%')
                ->first();

            if (!$parameterContent) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $metadata = json_decode($parameterContent->file_metadata, true) ?? [];

            return response()->json([
                'id' => $parameterContent->drive_file_id,
                'name' => $metadata['name'] ?? 'Google Drive File',
                'size' => $metadata['size'] ?? null,
                'mime_type' => $metadata['mime_type'] ?? 'application/octet-stream',
                'requires_permission' => $parameterContent->requires_permission,
                'permission_status' => $parameterContent->permission_status,
                'share_link' => $parameterContent->share_link,
                'has_access' => $this->driveFileManager->hasPermission($parameterContent, auth()->user())
            ]);

        } catch (\Exception $e) {
            Log::error('File info error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Convert Google Drive share link to download URL
     */
    protected function getGoogleDriveDownloadUrl(string $shareLink): ?string
    {
        // Extract file ID from share link
        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9-_]+)/',
            '/[?&]id=([a-zA-Z0-9-_]+)/',
            '/\/document\/d\/([a-zA-Z0-9-_]+)/',
            '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/',
            '/\/presentation\/d\/([a-zA-Z0-9-_]+)/',
        ];
        
        $fileId = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $shareLink, $matches)) {
                $fileId = $matches[1];
                break;
            }
        }
        
        if (!$fileId) {
            return null;
        }

        // Generate direct download URL
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }

    /**
     * Stream file content from Google Drive
     */
    protected function streamFile(string $downloadUrl, ParameterContent $parameterContent)
    {
        try {
            // Get file metadata for headers
            $metadata = json_decode($parameterContent->file_metadata, true) ?? [];
            $fileName = $metadata['name'] ?? 'download';
            $mimeType = $metadata['mime_type'] ?? 'application/octet-stream';

            // Make HTTP request to Google Drive
            $response = Http::timeout(30)->get($downloadUrl);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch file from Google Drive'], 502);
            }

            // Return the file content with appropriate headers
            return response($response->body())
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        } catch (\Exception $e) {
            Log::error('File streaming error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to stream file'], 500);
        }
    }

    /**
     * Check if a Google Drive link is accessible
     */
    public function checkAccess(Request $request)
    {
        $request->validate([
            'share_link' => 'required|url'
        ]);

        try {
            $shareLink = $request->share_link;
            $downloadUrl = $this->getGoogleDriveDownloadUrl($shareLink);
            
            if (!$downloadUrl) {
                return response()->json([
                    'accessible' => false,
                    'error' => 'Invalid Google Drive link'
                ]);
            }

            // Try to access the file (HEAD request)
            $response = Http::timeout(10)->head($downloadUrl);
            
            return response()->json([
                'accessible' => $response->successful(),
                'status_code' => $response->status(),
                'requires_permission' => !$response->successful()
            ]);

        } catch (\Exception $e) {
            Log::error('Access check error: ' . $e->getMessage());
            return response()->json([
                'accessible' => false,
                'error' => 'Failed to check access'
            ]);
        }
    }
}