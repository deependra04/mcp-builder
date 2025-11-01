<?php

namespace Laravel\McpBuilder\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ToolGenerator
{
    /**
     * Generate custom MCP tool code.
     */
    public function generate(string $toolName, array $options = []): string
    {
        $stubPath = __DIR__ . '/../../stubs/tool.stub';
        
        if (!File::exists($stubPath)) {
            // Create default stub content
            $stub = $this->getDefaultToolStub();
        } else {
            $stub = File::get($stubPath);
        }

        $namespace = $options['namespace'] ?? config('mcp-builder.tool_defaults.namespace');
        $className = Str::studly($toolName) . (config('mcp-builder.tool_defaults.suffix') ?? 'Tool');
        $description = $options['description'] ?? "MCP tool for {$toolName}";

        $replacements = [
            '{{namespace}}' => $namespace,
            '{{class}}' => $className,
            '{{name}}' => Str::snake($toolName),
            '{{description}}' => $description,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Get default tool stub if file doesn't exist.
     */
    protected function getDefaultToolStub(): string
    {
        return <<<'STUB'
<?php

namespace {{namespace}};

use Laravel\Mcp\Tools\Tool;
use Laravel\Mcp\Types\TextContent;

class {{class}} extends Tool
{
    /**
     * The name of the tool.
     */
    public string $name = '{{name}}';

    /**
     * The description of the tool.
     */
    public string $description = '{{description}}';

    /**
     * Execute the tool.
     */
    public function execute(array $arguments): TextContent
    {
        // TODO: Implement tool logic
        
        return new TextContent(
            text: 'Tool executed successfully'
        );
    }
}
STUB;
    }
}

