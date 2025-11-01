<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Generators\RouteGenerator;
use Laravel\McpBuilder\Services\ConfigManager;

class GenerateFromRoutes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:generate-routes 
                            {--server= : Server name to add tools to (optional)}
                            {--save : Save the generated tools to a server configuration}
                            {--filter= : Filter routes by prefix or pattern}';

    /**
     * The console command description.
     */
    protected $description = 'Generate MCP tools from Laravel routes';

    protected RouteGenerator $routeGenerator;
    protected ConfigManager $configManager;

    public function __construct(RouteGenerator $routeGenerator, ConfigManager $configManager)
    {
        parent::__construct();
        $this->routeGenerator = $routeGenerator;
        $this->configManager = $configManager;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $serverName = $this->option('server');
        $shouldSave = $this->option('save');
        $filter = $this->option('filter');

        $this->info("Generating MCP tools from Laravel routes...");

        try {
            // Generate tools from routes
            $tools = $this->routeGenerator->generateFromRoutes();

            // Apply filter if provided
            if ($filter) {
                $tools = array_filter($tools, function ($tool) use ($filter) {
                    return str_contains($tool['name'], $filter) || 
                           str_contains($tool['metadata']['uri'] ?? '', $filter);
                });
            }

            $this->info("Generated " . count($tools) . " tools from routes");

            if ($this->output->isVerbose()) {
                foreach ($tools as $tool) {
                    $this->line("  - {$tool['name']}: {$tool['description']}");
                }
            }

            // Save to server configuration if requested
            if ($shouldSave || $serverName) {
                if (!$serverName) {
                    $serverName = $this->ask('Enter server name');
                }

                $existingConfig = $this->configManager->loadConfig($serverName);
                
                if ($existingConfig) {
                    // Merge with existing tools
                    $existingConfig['tools'] = array_merge($existingConfig['tools'] ?? [], $tools);
                } else {
                    // Create new configuration
                    $existingConfig = [
                        'name' => $serverName,
                        'version' => '1.0.0',
                        'description' => "MCP server generated from routes",
                        'tools' => $tools,
                    ];
                }

                $saved = $this->configManager->saveConfig($serverName, $existingConfig);

                if ($saved) {
                    $this->info("✓ Tools saved to server '{$serverName}'");
                } else {
                    $this->error("Failed to save tools");
                    return Command::FAILURE;
                }
            } else {
                $this->line("\nUse --save or --server option to save these tools to a server configuration");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

