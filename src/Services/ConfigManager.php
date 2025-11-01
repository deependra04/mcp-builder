<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Laravel\McpBuilder\Generators\ConfigGenerator;
use Laravel\McpBuilder\Services\CacheService;
use Laravel\McpBuilder\Exceptions\ConfigFileException;

class ConfigManager
{
    protected Application $app;
    protected ConfigGenerator $configGenerator;
    protected CacheService $cache;

    public function __construct(Application $app, ConfigGenerator $configGenerator, CacheService $cache)
    {
        $this->app = $app;
        $this->configGenerator = $configGenerator;
        $this->cache = $cache;
    }

    /**
     * Save MCP server configuration to file.
     */
    public function saveConfig(string $serverName, array $config): bool
    {
        $storagePath = config('mcp-builder.storage_path');
        $filePath = "{$storagePath}/{$serverName}.json";

        try {
            // Ensure directory exists
            File::ensureDirectoryExists($storagePath);

            $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            
            if ($jsonContent === false) {
                Log::error('MCP Builder: Failed to encode config to JSON', ['server' => $serverName]);
                return false;
            }

            $saved = File::put($filePath, $jsonContent) !== false;

            if ($saved) {
                // Clear cache for this server
                $this->cache->forget("config_{$serverName}");
                $this->cache->forget("file_hash_{$filePath}");
                
                Log::info('MCP Builder: Config saved successfully', ['server' => $serverName]);
            } else {
                Log::error('MCP Builder: Failed to save config file', ['server' => $serverName, 'path' => $filePath]);
            }

            return $saved;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Exception saving config', [
                'server' => $serverName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Load MCP server configuration from file.
     */
    public function loadConfig(string $serverName): ?array
    {
        $storagePath = config('mcp-builder.storage_path');
        $filePath = "{$storagePath}/{$serverName}.json";

        // Check cache first
        return $this->cache->remember(
            "config_{$serverName}",
            function () use ($filePath, $serverName) {
                if (!File::exists($filePath)) {
                    Log::debug('MCP Builder: Config file not found', ['server' => $serverName, 'path' => $filePath]);
                    return null;
                }

                try {
                    $content = File::get($filePath);
                    $config = json_decode($content, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('MCP Builder: Invalid JSON in config file', [
                            'server' => $serverName,
                            'error' => json_last_error_msg()
                        ]);
                        return null;
                    }

                    return $config;
                } catch (\Exception $e) {
                    Log::error('MCP Builder: Exception loading config', [
                        'server' => $serverName,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            },
            3600 // Cache for 1 hour
        );
    }

    /**
     * Get all configuration files.
     */
    public function getAllConfigs(): array
    {
        $storagePath = config('mcp-builder.storage_path');
        
        return $this->cache->remember(
            'all_configs_list',
            function () use ($storagePath) {
                if (!File::exists($storagePath) || !File::isDirectory($storagePath)) {
                    return [];
                }

                $files = File::glob("{$storagePath}/*.{json,yaml,yml}", GLOB_BRACE);
                $configs = [];

                foreach ($files as $file) {
                    $serverName = File::name($file);
                    $configs[] = [
                        'name' => $serverName,
                        'path' => $file,
                        'modified' => File::lastModified($file),
                    ];
                }

                return $configs;
            },
            1800 // Cache for 30 minutes
        );
    }

    /**
     * Delete a configuration file.
     */
    public function deleteConfig(string $serverName): bool
    {
        $storagePath = config('mcp-builder.storage_path');
        $filePath = "{$storagePath}/{$serverName}.json";

        if (!File::exists($filePath)) {
            return false;
        }

        return File::delete($filePath);
    }

    /**
     * Generate and save configuration from a config file.
     */
    public function generateFromFile(string $configPath, string $serverName): bool
    {
        try {
            $config = $this->configGenerator->generateFromFile($configPath);
            $serverConfig = $this->configGenerator->generateServerConfig($config);
            
            return $this->saveConfig($serverName, $serverConfig);
        } catch (ConfigFileException $e) {
            Log::error('MCP Builder: Config file exception', [
                'path' => $configPath,
                'server' => $serverName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Unexpected exception generating from file', [
                'path' => $configPath,
                'server' => $serverName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

