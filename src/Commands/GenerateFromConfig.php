<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Services\ConfigManager;
use Laravel\McpBuilder\Generators\ConfigGenerator;

class GenerateFromConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:generate-config 
                            {config : Path to the configuration file (YAML or JSON)}
                            {--name= : Server name (optional, defaults to config filename)}';

    /**
     * The console command description.
     */
    protected $description = 'Generate MCP server configuration from a YAML or JSON config file';

    protected ConfigManager $configManager;
    protected ConfigGenerator $configGenerator;

    public function __construct(ConfigManager $configManager, ConfigGenerator $configGenerator)
    {
        parent::__construct();
        $this->configManager = $configManager;
        $this->configGenerator = $configGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $configPath = $this->argument('config');
        $serverName = $this->option('name') ?? pathinfo($configPath, PATHINFO_FILENAME);

        $this->info("Generating MCP server from config: {$configPath}");

        try {
            // Validate config file
            if (!file_exists($configPath)) {
                $this->error("Config file not found: {$configPath}");
                return Command::FAILURE;
            }

            // Generate and validate configuration
            $config = $this->configGenerator->generateFromFile($configPath);
            $this->configGenerator->validateConfig($config);

            // Generate server config
            $serverConfig = $this->configGenerator->generateServerConfig($config);

            // Save configuration
            $saved = $this->configManager->saveConfig($serverName, $serverConfig);

            if ($saved) {
                $this->info("✓ MCP server '{$serverName}' generated successfully!");
                $this->line("Configuration saved to: " . config('mcp-builder.storage_path') . "/{$serverName}.json");
                return Command::SUCCESS;
            } else {
                $this->error("Failed to save configuration");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

