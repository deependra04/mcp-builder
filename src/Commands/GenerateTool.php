<?php

namespace Laravel\McpBuilder\Commands;

use Illuminate\Console\Command;
use Laravel\McpBuilder\Services\CodeGeneratorService;
use Illuminate\Support\Str;

class GenerateTool extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcp:make-tool 
                            {name : The name of the tool}
                            {--description= : Tool description}
                            {--namespace= : Custom namespace for the tool}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a custom MCP tool class';

    protected CodeGeneratorService $codeGenerator;

    public function __construct(CodeGeneratorService $codeGenerator)
    {
        parent::__construct();
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $toolName = $this->argument('name');
        $description = $this->option('description') ?? "MCP tool for {$toolName}";
        $namespace = $this->option('namespace');

        $this->info("Generating MCP tool: {$toolName}");

        try {
            $options = [
                'description' => $description,
                'namespace' => $namespace,
            ];

            // Generate tool code
            $code = $this->codeGenerator->generateTool($toolName, $options);

            // Save tool to file
            $filePath = $this->codeGenerator->saveTool($toolName, $code, $namespace);

            $this->info("✓ Tool generated successfully!");
            $this->line("File created: {$filePath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

