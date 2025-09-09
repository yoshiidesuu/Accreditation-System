<?php

namespace App\Services;

use App\Models\ParameterContent;
use App\Services\Storage\StorageManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DriveFileManager
{
    protected $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * Store Google Drive file link and metadata
     */
    public function storeDriveFile(string $shareLink, array $metadata = []): ?array
    {
        try {
            // Use Google Drive adapter to process the link
            $result = $this->storageManager->store($shareLink, 'gdrive');
            
            if (!$result) {
                return null;
            }

            return [
                'drive_file_id' => $result['metadata']['drive_file_id'] ?? null,
                'share_link' => $shareLink,
                'storage_driver' => 'gdrive',
                'file_metadata' => json_encode(array_merge($result['metadata'] ?? [], $metadata)),
                'requires_permission' => $this->requiresPermission($shareLink),
                'permission_status' => 'none'
            ];
        } catch (\Exception $e) {
            Log::error('Drive file storage failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a Google Drive link requires permission
     */
    public function requiresPermission(string $shareLink): bool
    {
        // Check if the link is publicly accessible
        // This is a basic check - in a real implementation, you might want to
        // make an HTTP request to check the actual accessibility
        
        // Links with 'view' parameter are usually public
        if (strpos($shareLink, '/view') !== false) {
            return false;
        }
        
        // Links with 'edit' parameter usually require permission
        if (strpos($shareLink, '/edit') !== false) {
            return true;
        }
        
        // Default to requiring permission for safety
        return true;
    }

    /**
     * Request permission for a Google Drive file
     */
    public function requestPermission(ParameterContent $parameterContent, string $requesterEmail, string $message = ''): bool
    {
        try {
            // Update the parameter content record
            $parameterContent->update([
                'requires_permission' => true,
                'permission_requested_at' => now(),
                'permission_status' => 'requested'
            ]);

            // Send email notification (implement based on your email system)
            $this->sendPermissionRequestEmail($parameterContent, $requesterEmail, $message);

            Log::info('Permission requested for Drive file', [
                'parameter_content_id' => $parameterContent->id,
                'drive_file_id' => $parameterContent->drive_file_id,
                'requester_email' => $requesterEmail
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Permission request failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Grant permission for a Google Drive file
     */
    public function grantPermission(ParameterContent $parameterContent): bool
    {
        try {
            $parameterContent->update([
                'permission_status' => 'granted'
            ]);

            Log::info('Permission granted for Drive file', [
                'parameter_content_id' => $parameterContent->id,
                'drive_file_id' => $parameterContent->drive_file_id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Permission grant failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deny permission for a Google Drive file
     */
    public function denyPermission(ParameterContent $parameterContent, string $reason = ''): bool
    {
        try {
            $parameterContent->update([
                'permission_status' => 'denied'
            ]);

            Log::info('Permission denied for Drive file', [
                'parameter_content_id' => $parameterContent->id,
                'drive_file_id' => $parameterContent->drive_file_id,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Permission denial failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has permission to access a Drive file
     */
    public function hasPermission(ParameterContent $parameterContent, $user = null): bool
    {
        // If file doesn't require permission, allow access
        if (!$parameterContent->requires_permission) {
            return true;
        }

        // Check permission status
        return $parameterContent->permission_status === 'granted';
    }

    /**
     * Get Drive file access URL (proxy URL)
     */
    public function getAccessUrl(ParameterContent $parameterContent): ?string
    {
        if (!$this->hasPermission($parameterContent)) {
            return null;
        }

        try {
            return $this->storageManager->url($parameterContent->drive_file_id ?? $parameterContent->share_link, 'gdrive');
        } catch (\Exception $e) {
            Log::error('Failed to generate access URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Drive files that require permission approval
     */
    public function getPendingPermissionRequests(): \Illuminate\Database\Eloquent\Collection
    {
        return ParameterContent::where('storage_driver', 'gdrive')
            ->where('permission_status', 'requested')
            ->orderBy('permission_requested_at', 'desc')
            ->get();
    }

    /**
     * Send permission request email
     */
    protected function sendPermissionRequestEmail(ParameterContent $parameterContent, string $requesterEmail, string $message): void
    {
        // This is a placeholder - implement based on your email system
        // You might want to create a Mailable class for this
        
        $adminEmail = config('mail.admin_email', 'admin@example.com');
        $subject = 'Google Drive File Access Request';
        $body = "A user has requested access to a Google Drive file.\n\n";
        $body .= "Requester: {$requesterEmail}\n";
        $body .= "File ID: {$parameterContent->drive_file_id}\n";
        $body .= "Share Link: {$parameterContent->share_link}\n";
        $body .= "Message: {$message}\n\n";
        $body .= "Please review and approve/deny this request in the admin panel.";

        // Log the email request for now
        Log::info('Permission request email', [
            'to' => $adminEmail,
            'subject' => $subject,
            'requester' => $requesterEmail,
            'parameter_content_id' => $parameterContent->id
        ]);
    }

    /**
     * Clean up old permission requests
     */
    public function cleanupOldRequests(int $daysOld = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        return ParameterContent::where('storage_driver', 'gdrive')
            ->where('permission_status', 'requested')
            ->where('permission_requested_at', '<', $cutoffDate)
            ->update(['permission_status' => 'expired']);
    }
}