<?php

namespace App\Services\Storage;

use App\Contracts\StorageAdapterInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleDriveStorageAdapter implements StorageAdapterInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'proxy_downloads' => true,
            'validate_links' => true,
        ], $config);
    }

    public function store($file, string $path, array $options = []): array
    {
        // For Google Drive link-based storage, we expect a Google Drive share link
        // instead of an actual file upload
        if (is_string($file) && $this->isGoogleDriveLink($file)) {
            $driveFileId = $this->extractFileIdFromLink($file);
            
            if (!$driveFileId) {
                throw new \InvalidArgumentException('Invalid Google Drive link provided');
            }

            // Validate the link if configured
            if ($this->config['validate_links']) {
                $metadata = $this->getFileMetadataFromLink($driveFileId);
                if (!$metadata) {
                    throw new \InvalidArgumentException('Google Drive file is not accessible or does not exist');
                }
            } else {
                $metadata = [
                    'name' => $options['filename'] ?? 'Google Drive File',
                    'mime_type' => 'application/octet-stream',
                    'size' => 0,
                ];
            }

            $metadata['drive_file_id'] = $driveFileId;
            $metadata['drive_share_link'] = $file;
            $metadata['storage_type'] = 'google_drive_link';

            return [
                'id' => $driveFileId,
                'url' => $this->generateProxyUrl($driveFileId),
                'metadata' => $metadata,
            ];
        }

        throw new \InvalidArgumentException('Google Drive adapter only supports Google Drive share links, not file uploads');
    }

    public function get(string $identifier): ?string
    {
        // For link-based storage, we can't directly get file content
        // This would require the proxy endpoint to handle the actual download
        Log::warning('Direct file content retrieval not supported for Google Drive links');
        return null;
    }

    public function url(string $identifier, array $options = []): ?string
    {
        // Return proxy URL for secure access
        return $this->generateProxyUrl($identifier, $options);
    }

    public function delete(string $identifier): bool
    {
        // For link-based storage, we can't delete the actual file
        // This would only remove the reference from our system
        Log::info('Delete operation for Google Drive links only removes local reference');
        return true;
    }

    public function exists(string $identifier): bool
    {
        if ($this->config['validate_links']) {
            return $this->getFileMetadataFromLink($identifier) !== null;
        }
        
        // If validation is disabled, assume it exists
        return true;
    }

    public function metadata(string $identifier): ?array
    {
        return $this->getFileMetadataFromLink($identifier);
    }

    public function copy(string $from, string $to): bool
    {
        // For link-based storage, copying means creating a new reference
        Log::info('Copy operation for Google Drive links creates new reference');
        return true;
    }

    public function move(string $from, string $to): bool
    {
        // For link-based storage, moving means updating the reference
        Log::info('Move operation for Google Drive links updates reference');
        return true;
    }

    public function getDriverName(): string
    {
        return 'google_drive';
    }

    protected function setDomainPermissions(string $fileId): void
    {
        try {
            $permission = new Permission();
            $permission->setType('domain');
            $permission->setRole('reader');
            $permission->setDomain($this->config['share_with_domain']);

            $this->service->permissions->create($fileId, $permission);
        } catch (\Exception $e) {
            Log::warning('Failed to set domain permissions: ' . $e->getMessage());
        }
    }

    /**
     * Extract file ID from Google Drive share link
     */
    private function extractFileIdFromLink(string $link): ?string
    {
        // Handle different Google Drive link formats:
        // https://drive.google.com/file/d/FILE_ID/view
        // https://drive.google.com/open?id=FILE_ID
        // https://docs.google.com/document/d/FILE_ID/edit
        // https://docs.google.com/spreadsheets/d/FILE_ID/edit
        // https://docs.google.com/presentation/d/FILE_ID/edit
        
        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9-_]+)/',
            '/[?&]id=([a-zA-Z0-9-_]+)/',
            '/\/document\/d\/([a-zA-Z0-9-_]+)/',
            '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/',
            '/\/presentation\/d\/([a-zA-Z0-9-_]+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $link, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    /**
     * Check if a string is a Google Drive link
     */
    private function isGoogleDriveLink(string $url): bool
    {
        return strpos($url, 'drive.google.com') !== false || 
               strpos($url, 'docs.google.com') !== false;
    }

    /**
     * Get file metadata from Google Drive link (basic info only)
     */
    private function getFileMetadataFromLink(string $identifier): ?array
    {
        $fileId = $this->extractFileIdFromLink($identifier) ?? $identifier;
        if (!$fileId) {
            return null;
        }
        
        // For link-based approach, we can only provide basic metadata
        return [
            'id' => $fileId,
            'name' => 'Google Drive File',
            'size' => null,
            'mime_type' => 'application/octet-stream',
            'created_time' => null,
            'modified_time' => null,
            'web_view_link' => $identifier,
            'web_content_link' => null,
            'parents' => [],
            'drive_file_id' => $fileId,
            'share_link' => $identifier,
        ];
    }

    /**
     * Generate proxy URL for secure file access
     */
    private function generateProxyUrl(string $identifier, array $options = []): string
    {
        $fileId = $this->extractFileIdFromLink($identifier) ?? $identifier;
        
        $params = array_merge([
            'file_id' => $fileId,
            'driver' => 'gdrive'
        ], $options);
        
        return route('storage.proxy', $params);
    }

    /**
     * Get direct download URL (requires authentication)
     */
    public function getDirectDownloadUrl(string $identifier): ?string
    {
        try {
            $file = $this->service->files->get($identifier, ['fields' => 'webContentLink']);
            return $file->getWebContentLink();
        } catch (\Exception $e) {
            Log::error('Failed to get direct download URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Share file with specific email
     */
    public function shareWithEmail(string $fileId, string $email, string $role = 'reader'): bool
    {
        try {
            $permission = new Permission();
            $permission->setType('user');
            $permission->setRole($role);
            $permission->setEmailAddress($email);

            $this->service->permissions->create($fileId, $permission, [
                'sendNotificationEmail' => false
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to share file with email: ' . $e->getMessage());
            return false;
        }
    }
}