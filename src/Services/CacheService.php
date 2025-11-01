<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheService
{
    /**
     * Cache prefix for MCP Builder
     */
    protected string $prefix = 'mcp_builder_';

    /**
     * Cache duration in seconds (1 hour default)
     */
    protected int $duration = 3600;

    /**
     * Get cached value or execute callback and cache result.
     */
    public function remember(string $key, callable $callback, ?int $duration = null): mixed
    {
        $cacheKey = $this->prefix . $key;
        $duration = $duration ?? $this->duration;

        return Cache::remember($cacheKey, $duration, $callback);
    }

    /**
     * Get cached value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->prefix . $key;
        return Cache::get($cacheKey, $default);
    }

    /**
     * Put value in cache.
     */
    public function put(string $key, mixed $value, ?int $duration = null): bool
    {
        $cacheKey = $this->prefix . $key;
        $duration = $duration ?? $this->duration;
        
        return Cache::put($cacheKey, $value, $duration);
    }

    /**
     * Forget cached value.
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->prefix . $key;
        return Cache::forget($cacheKey);
    }

    /**
     * Clear all MCP Builder cache.
     */
    public function clear(): bool
    {
        return Cache::flush(); // In production, you might want to be more selective
    }

    /**
     * Cache file hash to detect changes.
     */
    public function getFileHash(string $filePath): ?string
    {
        if (!File::exists($filePath)) {
            return null;
        }

        $cachedHash = $this->get("file_hash_{$filePath}");
        $currentHash = File::hash($filePath);

        if ($cachedHash !== $currentHash) {
            $this->put("file_hash_{$filePath}", $currentHash);
            return null; // File changed
        }

        return $currentHash;
    }

    /**
     * Check if file has changed since last cache.
     */
    public function hasFileChanged(string $filePath): bool
    {
        return $this->getFileHash($filePath) === null;
    }
}

