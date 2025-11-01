<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Generators\ModelGenerator;
use Laravel\McpBuilder\Services\ConfigManager;
use Laravel\McpBuilder\Commands\Concerns\HandlesErrors;

class GenerateFromModel extends Command
{
    use HandlesErrors;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:generate-model 
                            {model : The model class name}
                            {--server= : Server name to add tools to (optional)}
                            {--save : Save the generated tools to a server configuration}
                            {--verbose : Show detailed error information}';

    /**
     * The console command description.
     */
    protected $description = 'Generate MCP tools from an Eloquent model';

    protected ModelGenerator $modelGenerator;
    protected ConfigManager $configManager;

    public function __construct(ModelGenerator $modelGenerator, ConfigManager $configManager)
    {
        parent::__construct();
        $this->modelGenerator = $modelGenerator;
        $this->configManager = $configManager;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->handleWithErrors(function() {
            $modelClass = $this->argument('model');
            $serverName = $this->option('server');
            $shouldSave = $this->option('save');

            $this->info("Generating MCP tools from model: {$modelClass}");

            // Generate tools from model
            $tools = $this->modelGenerator->generateFromModel($modelClass);

            // Display generated tools
            $this->info("✓ Generated " . count($tools) . " tools:");
            foreach ($tools as $toolType => $tool) {
                $this->line("  - {$tool['name']}: {$tool['description']}");
            }

            // Save to server configuration if requested
            if ($shouldSave || $serverName) {
                if (!$serverName) {
                    $serverName = $this->ask('Enter server name');
                }

                $existingConfig = $this->configManager->loadConfig($serverName);
                
                if ($existingConfig) {
                    // Merge with existing tools
                    $existingConfig['tools'] = array_merge($existingConfig['tools'] ?? [], array_values($tools));
                } else {
                    // Create new configuration
                    $existingConfig = [
                        'name' => $serverName,
                        'version' => '1.0.0',
                        'description' => "MCP server for {$modelClass}",
                        'tools' => array_values($tools),
                    ];
                }

                $saved = $this->configManager->saveConfig($serverName, $existingConfig);

                if (!$saved) {
                    throw new \RuntimeException("Failed to save tools to server '{$serverName}'");
                }

                $this->info("✓ Tools saved to server '{$serverName}'");
            } else {
                $this->line("\nUse --save or --server option to save these tools to a server configuration");
            }

            return Command::SUCCESS;
        });
    }
}

