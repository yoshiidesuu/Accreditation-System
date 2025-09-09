<?php

namespace App\Contracts;

interface StorageAdapterInterface
{
    /**
     * Store a file and return its identifier
     *
     * @param \Illuminate\Http\UploadedFile|string $file
     * @param string $path
     * @param array $options
     * @return array ['id' => string, 'url' => string, 'metadata' => array]
     */
    public function store($file, string $path, array $options = []): array;

    /**
     * Retrieve file content
     *
     * @param string $identifier
     * @return string|null
     */
    public function get(string $identifier): ?string;

    /**
     * Get file URL
     *
     * @param string $identifier
     * @param array $options
     * @return string|null
     */
    public function url(string $identifier, array $options = []): ?string;

    /**
     * Delete a file
     *
     * @param string $identifier
     * @return bool
     */
    public function delete(string $identifier): bool;

    /**
     * Check if file exists
     *
     * @param string $identifier
     * @return bool
     */
    public function exists(string $identifier): bool;

    /**
     * Get file metadata
     *
     * @param string $identifier
     * @return array|null
     */
    public function metadata(string $identifier): ?array;

    /**
     * Copy file to another location
     *
     * @param string $from
     * @param string $to
     * @return array|null
     */
    public function copy(string $from, string $to): ?array;

    /**
     * Move file to another location
     *
     * @param string $from
     * @param string $to
     * @return array|null
     */
    public function move(string $from, string $to): ?array;

    /**
     * Get storage driver name
     *
     * @return string
     */
    public function getDriverName(): string;
}