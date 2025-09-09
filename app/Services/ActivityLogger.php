<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    protected $request;
    protected $defaultLogName = 'default';

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Log an activity.
     */
    public function log(string $description, Model $subject = null, array $properties = [], string $event = null, string $logName = null): ActivityLog
    {
        $logName = $logName ?? $this->defaultLogName;
        $user = Auth::user();
        
        // Merge request context with custom properties
        $contextProperties = array_merge(
            $this->getRequestContext(),
            $properties
        );

        return ActivityLog::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'event' => $event,
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => $user ? $user->getKey() : null,
            'properties' => $contextProperties,
            'batch_uuid' => $this->getBatchUuid(),
        ]);
    }

    /**
     * Log file upload activity.
     */
    public function logFileUpload(Model $file, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'file_name' => $metadata['file_name'] ?? 'Unknown',
            'file_size' => $metadata['file_size'] ?? null,
            'file_type' => $metadata['file_type'] ?? null,
            'storage_path' => $metadata['storage_path'] ?? null,
        ], $metadata);

        return $this->log(
            'File uploaded: {file_name}',
            $file,
            $properties,
            'uploaded',
            'file_management'
        );
    }

    /**
     * Log file download activity.
     */
    public function logFileDownload(Model $file, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'file_name' => $metadata['file_name'] ?? 'Unknown',
            'download_method' => $metadata['download_method'] ?? 'direct',
        ], $metadata);

        return $this->log(
            'File downloaded: {file_name}',
            $file,
            $properties,
            'downloaded',
            'file_management'
        );
    }

    /**
     * Log file edit activity.
     */
    public function logFileEdit(Model $file, array $changes = [], array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'file_name' => $metadata['file_name'] ?? 'Unknown',
            'changes' => $changes,
        ], $metadata);

        return $this->log(
            'File edited: {file_name}',
            $file,
            $properties,
            'updated',
            'file_management'
        );
    }

    /**
     * Log file deletion activity.
     */
    public function logFileDelete(Model $file, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'file_name' => $metadata['file_name'] ?? 'Unknown',
            'deletion_reason' => $metadata['deletion_reason'] ?? null,
        ], $metadata);

        return $this->log(
            'File deleted: {file_name}',
            $file,
            $properties,
            'deleted',
            'file_management'
        );
    }

    /**
     * Log access request activity.
     */
    public function logAccessRequest(Model $request, string $action, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'request_type' => $metadata['request_type'] ?? 'file_access',
            'status' => $metadata['status'] ?? 'pending',
        ], $metadata);

        $descriptions = [
            'created' => 'Access request created',
            'approved' => 'Access request approved',
            'denied' => 'Access request denied',
            'cancelled' => 'Access request cancelled',
        ];

        return $this->log(
            $descriptions[$action] ?? "Access request {$action}",
            $request,
            $properties,
            $action,
            'access_management'
        );
    }

    /**
     * Log role/permission changes.
     */
    public function logRoleChange(User $user, string $action, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'target_user' => $user->name,
            'target_email' => $user->email,
        ], $metadata);

        $descriptions = [
            'role_assigned' => 'Role assigned to user: {target_user}',
            'role_removed' => 'Role removed from user: {target_user}',
            'permission_granted' => 'Permission granted to user: {target_user}',
            'permission_revoked' => 'Permission revoked from user: {target_user}',
        ];

        return $this->log(
            $descriptions[$action] ?? "Role/Permission {$action} for user: {target_user}",
            $user,
            $properties,
            $action,
            'user_management'
        );
    }

    /**
     * Log accreditation tagging activity.
     */
    public function logAccreditationTag(Model $model, string $action, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'tags' => $metadata['tags'] ?? [],
            'model_type' => get_class($model),
        ], $metadata);

        $descriptions = [
            'tagged' => 'Accreditation tags added',
            'untagged' => 'Accreditation tags removed',
            'updated' => 'Accreditation tags updated',
        ];

        return $this->log(
            $descriptions[$action] ?? "Accreditation tags {$action}",
            $model,
            $properties,
            $action,
            'accreditation_management'
        );
    }

    /**
     * Log login activity.
     */
    public function logLogin(User $user, bool $successful = true, array $metadata = []): ActivityLog
    {
        $properties = array_merge([
            'successful' => $successful,
            'login_method' => $metadata['login_method'] ?? 'standard',
        ], $metadata);

        $description = $successful ? 'User logged in successfully' : 'Failed login attempt';
        $event = $successful ? 'login_success' : 'login_failed';

        return $this->log(
            $description,
            $user,
            $properties,
            $event,
            'authentication'
        );
    }

    /**
     * Log failed login activity.
     */
    public function logFailedLogin(array $metadata = []): ActivityLog
    {
        return $this->log(
            'Failed login attempt',
            null, // No user since login failed
            $metadata,
            'login_failed',
            'authentication'
        );
    }

    /**
     * Log logout activity.
     */
    public function logLogout(User $user, array $metadata = []): ActivityLog
    {
        return $this->log(
            'User logged out',
            $user,
            $metadata,
            'logout',
            'authentication'
        );
    }

    /**
     * Get request context information.
     */
    protected function getRequestContext(): array
    {
        if (!$this->request) {
            return [];
        }

        return [
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'url' => $this->request->fullUrl(),
            'method' => $this->request->method(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get or generate batch UUID for grouping related activities.
     */
    protected function getBatchUuid(): string
    {
        // Check if there's already a batch UUID in the session for this request
        $sessionKey = 'activity_batch_uuid';
        
        if ($this->request && $this->request->hasSession()) {
            $batchUuid = $this->request->session()->get($sessionKey);
            
            if (!$batchUuid) {
                $batchUuid = Str::uuid()->toString();
                $this->request->session()->put($sessionKey, $batchUuid);
            }
            
            return $batchUuid;
        }
        
        return Str::uuid()->toString();
    }

    /**
     * Set the default log name.
     */
    public function setDefaultLogName(string $logName): self
    {
        $this->defaultLogName = $logName;
        return $this;
    }

    /**
     * Create a new logger instance with a specific log name.
     */
    public function withLogName(string $logName): self
    {
        $logger = new static($this->request);
        $logger->setDefaultLogName($logName);
        return $logger;
    }
}