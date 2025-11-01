<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Integrations\BoostIntegration;
use Laravel\McpBuilder\Services\ConfigManager;

class IntegrateBoost extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:integrate-boost 
                            {server? : Server name to add Boost tools to}
                            {--check : Check if Boost is installed}
                            {--install : Install Laravel Boost if not installed}';

    /**
     * The console command description.
     */
    protected $description = 'Integrate Laravel Boost tools into an MCP server';

    protected BoostIntegration $boostIntegration;
    protected ConfigManager $configManager;

    public function __construct(BoostIntegration $boostIntegration, ConfigManager $configManager)
    {
        parent::__construct();
        $this->boostIntegration = $boostIntegration;
        $this->configManager = $configManager;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if Boost is installed
        if ($this->option('check')) {
            if ($this->boostIntegration->isInstalled()) {
                $this->info('✓ Laravel Boost is installed');
                $tools = $this->boostIntegration->getBoostTools();
                $this->info('Available Boost tools: ' . count($tools));
                return Command::SUCCESS;
            } else {
                $this->warn('✗ Laravel Boost is not installed');
                $this->line('');
                $this->line('Install it with: composer require laravel/boost --dev');
                return Command::FAILURE;
            }
        }

        // Install Boost if requested
        if ($this->option('install')) {
            if ($this->boostIntegration->isInstalled()) {
                $this->info('Laravel Boost is already installed');
            } else {
                $this->info('Installing Laravel Boost...');
                $this->call('composer', ['require', 'laravel/boost', '--dev']);
                
                if ($this->boostIntegration->isInstalled()) {
                    $this->info('✓ Laravel Boost installed successfully');
                    $this->line('Run: php artisan boost:install');
                } else {
                    $this->error('Failed to install Laravel Boost');
                    return Command::FAILURE;
                }
            }
            return Command::SUCCESS;
        }

        // Auto-check and install Boost if not present
        if (!$this->boostIntegration->isInstalled()) {
            $this->warn('Laravel Boost is not installed');
            
            if ($this->confirm('Would you like to install Laravel Boost automatically?', true)) {
                $this->info('Installing Laravel Boost...');
                
                $installed = $this->boostIntegration->installBoost(function ($message) {
                    $this->line($message);
                });
                
                if (!$installed) {
                    $this->error('Failed to install Laravel Boost automatically');
                    $this->line('');
                    $this->line('Please install manually with:');
                    $this->line('  composer require laravel/boost --dev');
                    $this->line('  php artisan boost:install');
                    return Command::FAILURE;
                }
            } else {
                $this->line('');
                $this->line('Install it first with:');
                $this->line('  composer require laravel/boost --dev');
                $this->line('  php artisan boost:install');
                return Command::FAILURE;
            }
        }

        // Get server name
        $serverName = $this->argument('server');

        if (!$serverName) {
            $serverName = $this->ask('Enter server name to integrate Boost tools');
        }

        // Load existing config
        $serverConfig = $this->configManager->loadConfig($serverName);

        if (!$serverConfig) {
            $this->error("Server '{$serverName}' not found");
            
            if ($this->confirm('Would you like to create a new server?', true)) {
                $serverConfig = [
                    'name' => $serverName,
                    'version' => '1.0.0',
                    'description' => 'MCP server with Laravel Boost integration',
                    'tools' => [],
                ];
            } else {
                return Command::FAILURE;
            }
        }

        // Check if Boost tools already exist
        if ($this->boostIntegration->hasBoostTools($serverConfig['tools'] ?? [])) {
            if (!$this->confirm('Boost tools already exist. Merge them again?', false)) {
                $this->info('Skipped');
                return Command::SUCCESS;
            }
        }

        // Merge Boost tools
        $this->info("Integrating Laravel Boost tools into '{$serverName}'...");
        
        $updatedConfig = $this->boostIntegration->mergeBoostTools($serverConfig);
        $boostTools = $this->boostIntegration->getBoostTools();

        // Save configuration
        $saved = $this->configManager->saveConfig($serverName, $updatedConfig);

        if ($saved) {
            $this->info("✓ Successfully integrated " . count($boostTools) . " Boost tools!");
            $this->line("Configuration saved to: " . config('mcp-builder.storage_path') . "/{$serverName}.json");
            return Command::SUCCESS;
        } else {
            $this->error('Failed to save configuration');
            return Command::FAILURE;
        }
    }
}

