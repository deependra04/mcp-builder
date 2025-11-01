<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Services\ValidationService;
use Laravel\McpBuilder\Services\ConfigManager;

class ValidateConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:validate-config 
                            {server : Server name or path to config file}
                            {--fix : Attempt to fix validation errors}';

    /**
     * The console command description.
     */
    protected $description = 'Validate an MCP server configuration';

    protected ValidationService $validator;
    protected ConfigManager $configManager;

    public function __construct(ValidationService $validator, ConfigManager $configManager)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->configManager = $configManager;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('server');

        $this->info("Validating configuration: {$identifier}");

        // Check if it's a file path or server name
        if (file_exists($identifier)) {
            $config = json_decode(file_get_contents($identifier), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Invalid JSON file");
                return Command::FAILURE;
            }
        } else {
            $config = $this->configManager->loadConfig($identifier);
            if (!$config) {
                $this->error("Server '{$identifier}' not found");
                return Command::FAILURE;
            }
        }

        $errors = $this->validator->validateServerConfig($config);

        if (empty($errors)) {
            $this->info("✓ Configuration is valid!");
            return Command::SUCCESS;
        }

        $this->error("✗ Configuration has " . count($errors) . " error(s):");
        foreach ($errors as $error) {
            $this->line("  - {$error}");
        }

        return Command::FAILURE;
    }
}

