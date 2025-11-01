<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Generators\ModelGenerator;
use Laravel\McpBuilder\Services\ConfigManager;

class GenerateBatch extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:generate-batch 
                            {models : Comma-separated list of model classes}
                            {--server= : Server name to add tools to}
                            {--save : Save the generated tools}';

    /**
     * The console command description.
     */
    protected $description = 'Generate MCP tools from multiple models at once';

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
        $modelsInput = $this->argument('models');
        $models = array_map('trim', explode(',', $modelsInput));
        $serverName = $this->option('server');
        $shouldSave = $this->option('save');

        $this->info("Generating MCP tools from " . count($models) . " models...");

        $allTools = [];
        $errors = [];

        $bar = $this->output->createProgressBar(count($models));
        $bar->start();

        foreach ($models as $modelClass) {
            try {
                $tools = $this->modelGenerator->generateFromModel($modelClass);
                $allTools = array_merge($allTools, array_values($tools));
                $bar->advance();
            } catch (\Exception $e) {
                $errors[] = ['model' => $modelClass, 'error' => $e->getMessage()];
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line('');

        if (!empty($errors)) {
            $this->warn("Errors encountered:");
            foreach ($errors as $error) {
                $this->line("  - {$error['model']}: {$error['error']}");
            }
        }

        $this->info("✓ Generated " . count($allTools) . " tools from " . (count($models) - count($errors)) . " models");

        // Save if requested
        if ($shouldSave || $serverName) {
            if (!$serverName) {
                $serverName = $this->ask('Enter server name');
            }

            $existingConfig = $this->configManager->loadConfig($serverName);
            
            if ($existingConfig) {
                $existingConfig['tools'] = array_merge($existingConfig['tools'] ?? [], $allTools);
            } else {
                $existingConfig = [
                    'name' => $serverName,
                    'version' => '1.0.0',
                    'description' => "MCP server for " . count($models) . " models",
                    'tools' => $allTools,
                ];
            }

            $saved = $this->configManager->saveConfig($serverName, $existingConfig);

            if ($saved) {
                $this->info("✓ Tools saved to server '{$serverName}'");
                return Command::SUCCESS;
            }

            $this->error("Failed to save tools");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

