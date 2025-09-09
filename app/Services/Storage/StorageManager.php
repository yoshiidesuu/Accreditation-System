<?php

namespace App\Services\Storage;

use App\Contracts\StorageAdapterInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class StorageManager
{
    protected array $adapters = [];
    protected ?StorageAdapterInterface $defaultAdapter = null;
    protected array $config;

    public function __construct()
    {
        $this->config = config('storage_adapters', []);
        $this->initializeAdapters();
    }

    protected function initializeAdapters(): void
    {
        // Initialize local adapter
        $this->adapters['local'] = new LocalStorageAdapter(
            config('storage_adapters.local.disk', 'public'),
            config('storage_adapters.local.base_path', '')
        );

        // Initialize S3 adapter if configured
        if (config('filesystems.disks.s3.key')) {
            $this->adapters['s3'] = new S3StorageAdapter(
                config('storage_adapters.s3.disk', 's3'),
                config('storage_adapters.s3.base_path', ''),
                config('storage_adapters.s3.bucket')
            );
        }

        // Initialize Google Drive adapter if configured
        if (file_exists(storage_path('app/google-drive-credentials.json'))) {
            $this->adapters['google_drive'] = new GoogleDriveStorageAdapter(
                config('storage_adapters.google_drive', [])
            );
        }

        // Set default adapter
        $defaultDriver = $this->getDefaultDriver();
        if (isset($this->adapters[$defaultDriver])) {
            $this->defaultAdapter = $this->adapters[$defaultDriver];
        } else {
            $this->defaultAdapter = $this->adapters['local'];
        }
    }

    public function driver(string $driver = null): StorageAdapterInterface
    {
        if ($driver === null) {
            return $this->defaultAdapter;
        }

        if (!isset($this->adapters[$driver])) {
            throw new InvalidArgumentException("Storage driver [{$driver}] not found.");
        }

        return $this->adapters[$driver];
    }

    public function getAvailableDrivers(): array
    {
        return array_keys($this->adapters);
    }

    public function getDefaultDriver(): string
    {
        return Cache::remember('default_storage_driver', 3600, function () {
            $setting = Setting::where('key', 'default_storage_driver')->first();
            return $setting ? $setting->value : 'local';
        });
    }

    public function setDefaultDriver(string $driver): void
    {
        if (!isset($this->adapters[$driver])) {
            throw new InvalidArgumentException("Storage driver [{$driver}] not found.");
        }

        Setting::updateOrCreate(
            ['key' => 'default_storage_driver'],
            ['value' => $driver]
        );

        Cache::forget('default_storage_driver');
        $this->defaultAdapter = $this->adapters[$driver];
    }

    /**
     * Store file using the default adapter
     */
    public function store($file, string $path, array $options = []): array
    {
        return $this->defaultAdapter->store($file, $path, $options);
    }

    /**
     * Store file using a specific adapter
     */
    public function storeUsing(string $driver, $file, string $path, array $options = []): array
    {
        return $this->driver($driver)->store($file, $path, $options);
    }

    /**
     * Get file content using the appropriate adapter based on identifier
     */
    public function get(string $identifier, string $driver = null): ?string
    {
        if ($driver) {
            return $this->driver($driver)->get($identifier);
        }

        // Try to determine driver from identifier or use default
        $adapter = $this->determineAdapterFromIdentifier($identifier);
        return $adapter->get($identifier);
    }

    /**
     * Get file URL using the appropriate adapter
     */
    public function url(string $identifier, string $driver = null, array $options = []): ?string
    {
        if ($driver) {
            return $this->driver($driver)->url($identifier, $options);
        }

        $adapter = $this->determineAdapterFromIdentifier($identifier);
        return $adapter->url($identifier, $options);
    }

    /**
     * Delete file using the appropriate adapter
     */
    public function delete(string $identifier, string $driver = null): bool
    {
        if ($driver) {
            return $this->driver($driver)->delete($identifier);
        }

        $adapter = $this->determineAdapterFromIdentifier($identifier);
        return $adapter->delete($identifier);
    }

    /**
     * Check if file exists using the appropriate adapter
     */
    public function exists(string $identifier, string $driver = null): bool
    {
        if ($driver) {
            return $this->driver($driver)->exists($identifier);
        }

        $adapter = $this->determineAdapterFromIdentifier($identifier);
        return $adapter->exists($identifier);
    }

    /**
     * Get file metadata using the appropriate adapter
     */
    public function metadata(string $identifier, string $driver = null): ?array
    {
        if ($driver) {
            return $this->driver($driver)->metadata($identifier);
        }

        $adapter = $this->determineAdapterFromIdentifier($identifier);
        return $adapter->metadata($identifier);
    }

    /**
     * Migrate file from one storage to another
     */
    public function migrate(string $identifier, string $fromDriver, string $toDriver, string $newPath = null): ?array
    {
        $fromAdapter = $this->driver($fromDriver);
        $toAdapter = $this->driver($toDriver);

        // Get file content
        $content = $fromAdapter->get($identifier);
        if (!$content) {
            return null;
        }

        // Get metadata for filename and mime type
        $metadata = $fromAdapter->metadata($identifier);
        $filename = $metadata['name'] ?? basename($identifier);
        $mimeType = $metadata['mime_type'] ?? 'application/octet-stream';

        // Store in new location
        $result = $toAdapter->store($content, $newPath ?? dirname($identifier), [
            'filename' => $filename,
            'mime_type' => $mimeType,
        ]);

        if ($result) {
            // Delete from old location
            $fromAdapter->delete($identifier);
        }

        return $result;
    }

    /**
     * Determine which adapter to use based on identifier pattern
     */
    protected function determineAdapterFromIdentifier(string $identifier): StorageAdapterInterface
    {
        // Google Drive file IDs are typically 33+ characters long and alphanumeric
        if (preg_match('/^[a-zA-Z0-9_-]{25,}$/', $identifier)) {
            return $this->adapters['google_drive'] ?? $this->defaultAdapter;
        }

        // S3 paths typically contain bucket info or specific patterns
        if (strpos($identifier, 's3://') === 0 || strpos($identifier, 'amazonaws.com') !== false) {
            return $this->adapters['s3'] ?? $this->defaultAdapter;
        }

        // Default to local for file paths
        return $this->adapters['local'] ?? $this->defaultAdapter;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        $stats = [];
        
        foreach ($this->adapters as $name => $adapter) {
            $stats[$name] = [
                'driver' => $adapter->getDriverName(),
                'available' => true,
            ];
        }

        return $stats;
    }
}