<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Models\McpServer;
use Laravel\McpBuilder\Services\ExportService;
use Laravel\McpBuilder\Services\ServerManager;

class ExportServer extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:export-server 
                            {server : Server ID or name}
                            {--path= : Export directory path}';

    /**
     * The console command description.
     */
    protected $description = 'Export an MCP server configuration to JSON file';

    protected ExportService $exportService;
    protected ServerManager $serverManager;

    public function __construct(ExportService $exportService, ServerManager $serverManager)
    {
        parent::__construct();
        $this->exportService = $exportService;
        $this->serverManager = $serverManager;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('server');
        $exportPath = $this->option('path');

        // Try to find server by ID or name
        $server = is_numeric($identifier)
            ? $this->serverManager->get((int) $identifier)
            : McpServer::where('name', $identifier)->first();

        if (!$server) {
            $this->error("Server not found: {$identifier}");
            return Command::FAILURE;
        }

        try {
            $filePath = $this->exportService->exportServer($server, $exportPath);

            $this->info("✓ Server '{$server->name}' exported successfully!");
            $this->line("Export saved to: {$filePath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

