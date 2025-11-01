<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Services\ExportService;

class ImportServer extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:import-server 
                            {file : Path to the import file}
                            {--name= : Server name (overrides imported name)}';

    /**
     * The console command description.
     */
    protected $description = 'Import an MCP server configuration from JSON file';

    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $serverName = $this->option('name');

        $this->info("Importing server from: {$filePath}");

        try {
            $server = $this->exportService->importServer($filePath, $serverName);

            $this->info("✓ Server '{$server->name}' imported successfully!");
            $this->line("  ID: {$server->id}");
            $this->line("  Tools: " . $server->tools->count());

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

