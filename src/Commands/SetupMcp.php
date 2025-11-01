<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Builders\ServerBuilder;
use Laravel\McpBuilder\Prompts\SetupPrompts;
use Laravel\McpBuilder\Integrations\BoostIntegration;

class SetupMcp extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:setup {--quick : Use quick setup with defaults}';

    /**
     * The console command description.
     */
    protected $description = 'Interactive setup wizard for creating MCP servers';

    protected SetupPrompts $prompts;
    protected ServerBuilder $builder;
    protected BoostIntegration $boostIntegration;

    public function __construct(SetupPrompts $prompts, ServerBuilder $builder, BoostIntegration $boostIntegration)
    {
        parent::__construct();
        $this->prompts = $prompts;
        $this->builder = $builder;
        $this->boostIntegration = $boostIntegration;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Laravel MCP Builder Setup Wizard');
        $this->line('');

        if ($this->option('quick')) {
            return $this->quickSetup();
        }

        return $this->interactiveSetup();
    }

    /**
     * Quick setup with defaults.
     */
    protected function quickSetup(): int
    {
        $this->info('Running quick setup...');

        $serverName = $this->ask('Server name', 'my-mcp-server');
        $version = $this->ask('Version', '1.0.0');
        $description = $this->ask('Description', '');

        $options = [
            'name' => $serverName,
            'version' => $version,
            'description' => $description,
            'include_routes' => $this->confirm('Generate tools from routes?', false),
        ];

        try {
            $serverConfig = $this->builder->build($options);
            $saved = $this->builder->save($serverName, $serverConfig);

            if ($saved) {
                $this->info("✓ MCP server '{$serverName}' created successfully!");
                return Command::SUCCESS;
            }

            $this->error('Failed to save server configuration');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Interactive setup wizard.
     */
    protected function interactiveSetup(): int
    {
        // Step 1: Basic server information
        $this->info('Step 1: Basic Server Information');
        $serverName = $this->ask('Server name', 'my-mcp-server');
        $version = $this->ask('Version', '1.0.0');
        $description = $this->ask('Description (optional)');

        // Step 2: Generation method
        $this->line('');
        $this->info('Step 2: Choose Generation Method');
        $method = $this->choice(
            'How would you like to generate MCP tools?',
            [
                'config' => 'From configuration file (YAML/JSON)',
                'model' => 'From Eloquent model(s)',
                'routes' => 'From Laravel routes',
                'manual' => 'Manual setup',
                'mixed' => 'Mixed (combine multiple methods)',
            ],
            'config'
        );

        $options = [
            'name' => $serverName,
            'version' => $version,
            'description' => $description ?? '',
        ];

        // Step 3: Process based on method
        $this->line('');
        $this->info("Step 3: Configure {$method} generation");

        switch ($method) {
            case 'config':
                $configFile = $this->ask('Path to configuration file (YAML or JSON)');
                if ($configFile && file_exists($configFile)) {
                    $options['config_file'] = $configFile;
                }
                break;

            case 'model':
                $models = [];
                while ($model = $this->ask('Enter model class name (press Enter to finish)', null)) {
                    if (class_exists($model)) {
                        $models[] = $model;
                    } else {
                        $this->warn("Model class not found: {$model}");
                    }
                }
                $options['models'] = $models;
                break;

            case 'routes':
                $options['include_routes'] = true;
                $filter = $this->ask('Filter routes by prefix or pattern (optional)', null);
                if ($filter) {
                    $options['route_filter'] = $filter;
                }
                break;

            case 'manual':
                $this->info('Manual setup: You can add tools later using mcp:make-tool');
                break;

            case 'mixed':
                // Allow multiple methods
                if ($this->confirm('Generate from configuration file?', false)) {
                    $configFile = $this->ask('Path to configuration file');
                    if ($configFile && file_exists($configFile)) {
                        $options['config_file'] = $configFile;
                    }
                }

                if ($this->confirm('Generate from models?', false)) {
                    $models = [];
                    while ($model = $this->ask('Enter model class name (press Enter to finish)', null)) {
                        if (class_exists($model)) {
                            $models[] = $model;
                        }
                    }
                    $options['models'] = $models;
                }

                if ($this->confirm('Generate from routes?', false)) {
                    $options['include_routes'] = true;
                }
                break;
        }

        // Step 4: Boost Integration
        $this->line('');
        $this->info('Step 4: Laravel Boost Integration');
        
        if ($this->boostIntegration->isInstalled()) {
            if ($this->confirm('Would you like to include Laravel Boost tools?', true)) {
                $options['include_boost'] = true;
            }
        } else {
            $this->line('💡 Laravel Boost provides 15+ powerful MCP tools:');
            $this->line('   • Database querying');
            $this->line('   • Tinker code execution');
            $this->line('   • Documentation search (17,000+ docs)');
            $this->line('   • Schema inspection');
            $this->line('   • And more...');
            $this->line('');
            
            if ($this->confirm('Would you like to install and include Laravel Boost tools?', true)) {
                $this->info('Installing Laravel Boost...');
                $installed = $this->boostIntegration->installBoost(function ($message) {
                    $this->line($message);
                });
                
                if ($installed) {
                    $options['include_boost'] = true;
                    $this->info('✓ Boost will be integrated into your server');
                } else {
                    $this->warn('Could not install automatically. You can install manually later.');
                }
            }
        }

        // Step 5: Build and save
        $this->line('');
        $this->info('Step 5: Building server configuration...');

        try {
            $serverConfig = $this->builder->build($options);
            $saved = $this->builder->save($serverName, $serverConfig);

            if ($saved) {
                $this->line('');
                $this->info("✓ MCP server '{$serverName}' created successfully!");
                $this->line("Configuration saved to: " . config('mcp-builder.storage_path') . "/{$serverName}.json");
                $this->line('');
                $this->info("Generated " . count($serverConfig['tools']) . " tools");
                return Command::SUCCESS;
            }

            $this->error('Failed to save server configuration');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

