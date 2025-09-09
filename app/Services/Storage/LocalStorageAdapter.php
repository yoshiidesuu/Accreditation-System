<?php

namespace App\Services\Storage;

use App\Contracts\StorageAdapterInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalStorageAdapter implements StorageAdapterInterface
{
    protected string $disk;
    protected string $basePath;

    public function __construct(string $disk = 'public', string $basePath = '')
    {
        $this->disk = $disk;
        $this->basePath = $basePath;
    }

    public function store($file, string $path, array $options = []): array
    {
        $fullPath = $this->basePath ? $this->basePath . '/' . $path : $path;
        
        if ($file instanceof UploadedFile) {
            $filename = $options['filename'] ?? $this->generateFilename($file);
            $storedPath = $file->storeAs($fullPath, $filename, $this->disk);
            
            $metadata = [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ];
        } else {
            // Handle string content
            $filename = $options['filename'] ?? Str::random(40);
            $storedPath = $fullPath . '/' . $filename;
            Storage::disk($this->disk)->put($storedPath, $file);
            
            $metadata = [
                'mime_type' => $options['mime_type'] ?? 'text/plain',
                'size' => strlen($file),
            ];
        }

        return [
            'id' => $storedPath,
            'url' => Storage::disk($this->disk)->url($storedPath),
            'metadata' => $metadata,
        ];
    }

    public function get(string $identifier): ?string
    {
        if (!Storage::disk($this->disk)->exists($identifier)) {
            return null;
        }

        return Storage::disk($this->disk)->get($identifier);
    }

    public function url(string $identifier, array $options = []): ?string
    {
        if (!Storage::disk($this->disk)->exists($identifier)) {
            return null;
        }

        return Storage::disk($this->disk)->url($identifier);
    }

    public function delete(string $identifier): bool
    {
        return Storage::disk($this->disk)->delete($identifier);
    }

    public function exists(string $identifier): bool
    {
        return Storage::disk($this->disk)->exists($identifier);
    }

    public function metadata(string $identifier): ?array
    {
        if (!Storage::disk($this->disk)->exists($identifier)) {
            return null;
        }

        $size = Storage::disk($this->disk)->size($identifier);
        $lastModified = Storage::disk($this->disk)->lastModified($identifier);
        $mimeType = Storage::disk($this->disk)->mimeType($identifier);

        return [
            'size' => $size,
            'last_modified' => $lastModified,
            'mime_type' => $mimeType,
        ];
    }

    public function copy(string $from, string $to): ?array
    {
        if (!Storage::disk($this->disk)->exists($from)) {
            return null;
        }

        if (Storage::disk($this->disk)->copy($from, $to)) {
            return [
                'id' => $to,
                'url' => Storage::disk($this->disk)->url($to),
                'metadata' => $this->metadata($to),
            ];
        }

        return null;
    }

    public function move(string $from, string $to): ?array
    {
        if (!Storage::disk($this->disk)->exists($from)) {
            return null;
        }

        if (Storage::disk($this->disk)->move($from, $to)) {
            return [
                'id' => $to,
                'url' => Storage::disk($this->disk)->url($to),
                'metadata' => $this->metadata($to),
            ];
        }

        return null;
    }

    public function getDriverName(): string
    {
        return 'local';
    }

    protected function generateFilename(UploadedFile $file): string
    {
        return time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
    }
}