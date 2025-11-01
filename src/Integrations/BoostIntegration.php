<?php

namespace Laravel\McpBuilder\Integrations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\Exceptions\ProcessFailedException;

class BoostIntegration
{
    /**
     * Check if Laravel Boost is installed.
     */
    public function isInstalled(): bool
    {
        return class_exists(\Laravel\Boost\BoostServiceProvider::class) ||
               File::exists(base_path('vendor/laravel/boost')) ||
               $this->checkComposerPackage('laravel/boost');
    }

    /**
     * Check if a composer package is installed.
     */
    protected function checkComposerPackage(string $package): bool
    {
        $composerLock = base_path('composer.lock');
        
        if (!File::exists($composerLock)) {
            return false;
        }

        $lockContent = json_decode(File::get($composerLock), true);
        
        if (!$lockContent) {
            return false;
        }

        $packages = array_merge(
            $lockContent['packages'] ?? [],
            $lockContent['packages-dev'] ?? []
        );

        foreach ($packages as $installedPackage) {
            if (($installedPackage['name'] ?? '') === $package) {
                return true;
            }
        }

        return false;
    }

    /**
     * Try to install Laravel Boost automatically.
     */
    public function installBoost(callable $outputCallback = null): bool
    {
        if ($this->isInstalled()) {
            return true;
        }

        if ($outputCallback) {
            $outputCallback('Laravel Boost is not installed. Attempting to install...');
        }
        
        try {
            // Use Laravel Process facade instead of shell_exec
            $result = Process::timeout(300)
                ->path(base_path())
                ->run(['composer', 'require', 'laravel/boost', '--dev']);
            
            if ($result->successful() && $this->isInstalled()) {
                if ($outputCallback) {
                    $outputCallback('✓ Laravel Boost installed successfully');
                    $outputCallback('Run: php artisan boost:install');
                }
                
                return true;
            }
            
            $error = $result->errorOutput() ?: $result->output();
            if ($outputCallback) {
                $outputCallback('Failed to install Laravel Boost automatically.');
                $outputCallback('Please install manually: composer require laravel/boost --dev');
                if ($error) {
                    $outputCallback("Error: {$error}");
                }
            }
            
            return false;
        } catch (ProcessFailedException $e) {
            if ($outputCallback) {
                $outputCallback('Error installing Boost: ' . $e->getMessage());
            }
            return false;
        } catch (\Exception $e) {
            if ($outputCallback) {
                $outputCallback('Error installing Boost: ' . $e->getMessage());
            }
            return false;
        }
    }


    /**
     * Get Boost MCP tools configuration.
     */
    public function getBoostTools(): array
    {
        if (!$this->isInstalled()) {
            return [];
        }

        // Laravel Boost provides tools through its MCP server
        // These are the typical tools Boost offers
        $boostTools = [
            [
                'name' => 'boost_query_database',
                'description' => 'Query the Laravel database using Boost',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'The database query to execute',
                        ],
                    ],
                    'required' => ['query'],
                ],
                'source' => 'laravel-boost',
            ],
            [
                'name' => 'boost_tinker',
                'description' => 'Execute PHP code in Laravel Tinker',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'code' => [
                            'type' => 'string',
                            'description' => 'The PHP code to execute',
                        ],
                    ],
                    'required' => ['code'],
                ],
                'source' => 'laravel-boost',
            ],
            [
                'name' => 'boost_search_docs',
                'description' => 'Search Laravel documentation',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search query',
                        ],
                    ],
                    'required' => ['query'],
                ],
                'source' => 'laravel-boost',
            ],
            [
                'name' => 'boost_inspect_schema',
                'description' => 'Inspect database schema',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'table' => [
                            'type' => 'string',
                            'description' => 'Table name to inspect',
                        ],
                    ],
                ],
                'source' => 'laravel-boost',
            ],
        ];

        // Try to get actual tools from Boost if available
        try {
            if (class_exists(\Laravel\Boost\McpServer::class)) {
                // Boost MCP server would have actual tool definitions
                // For now, we return the common tools
            }
        } catch (\Exception $e) {
            // Fall back to default tools
        }

        return $boostTools;
    }

    /**
     * Merge Boost tools into a server configuration.
     * Automatically installs Boost if not present and autoInstall is true.
     */
    public function mergeBoostTools(array $serverConfig, bool $autoInstall = true, callable $outputCallback = null): array
    {
        // Check if Boost is installed, if not and autoInstall is true, try to install
        if (!$this->isInstalled() && $autoInstall) {
            $this->installBoost($outputCallback);
        }

        $boostTools = $this->getBoostTools();
        
        if (empty($boostTools)) {
            return $serverConfig;
        }

        // Add Boost tools to existing tools
        $existingTools = $serverConfig['tools'] ?? [];
        $serverConfig['tools'] = array_merge($existingTools, $boostTools);

        // Add metadata about Boost integration
        $serverConfig['integrations'] = $serverConfig['integrations'] ?? [];
        $serverConfig['integrations']['laravel-boost'] = [
            'enabled' => true,
            'tools_count' => count($boostTools),
            'installed' => $this->isInstalled(),
        ];

        return $serverConfig;
    }

    /**
     * Check if Boost tools are already in the config.
     */
    public function hasBoostTools(array $tools): bool
    {
        foreach ($tools as $tool) {
            if (isset($tool['source']) && $tool['source'] === 'laravel-boost') {
                return true;
            }
        }

        return false;
    }
}

