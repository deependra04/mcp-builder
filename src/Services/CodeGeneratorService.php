<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Laravel\McpBuilder\Generators\ModelGenerator;
use Laravel\McpBuilder\Generators\RouteGenerator;
use Laravel\McpBuilder\Generators\ToolGenerator;
use Laravel\McpBuilder\Exceptions\ToolGenerationException;

class CodeGeneratorService
{
    protected Application $app;
    protected ModelGenerator $modelGenerator;
    protected RouteGenerator $routeGenerator;
    protected ToolGenerator $toolGenerator;

    public function __construct(
        Application $app,
        ModelGenerator $modelGenerator,
        RouteGenerator $routeGenerator,
        ToolGenerator $toolGenerator
    ) {
        $this->app = $app;
        $this->modelGenerator = $modelGenerator;
        $this->routeGenerator = $routeGenerator;
        $this->toolGenerator = $toolGenerator;
    }

    /**
     * Generate tools from a model.
     */
    public function generateFromModel(string $modelClass): array
    {
        return $this->modelGenerator->generateFromModel($modelClass);
    }

    /**
     * Generate tools from routes.
     */
    public function generateFromRoutes(array $routes = null): array
    {
        return $this->routeGenerator->generateFromRoutes($routes);
    }

    /**
     * Generate custom tool code.
     */
    public function generateTool(string $toolName, array $options = []): string
    {
        return $this->toolGenerator->generate($toolName, $options);
    }

    /**
     * Save generated tool to file.
     */
    public function saveTool(string $toolName, string $code, string $namespace = null): string
    {
        try {
            // Validate tool name
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $toolName)) {
                throw ToolGenerationException::invalidName($toolName);
            }

            $namespace = $namespace ?? config('mcp-builder.tool_defaults.namespace');
            $toolsPath = config('mcp-builder.tools_path');
            
            // Convert namespace to path (sanitize to prevent directory traversal)
            $path = str_replace('\\', '/', $namespace);
            $path = str_replace(['../', './'], '', $path); // Security: prevent path traversal
            $fullPath = base_path($path);
            
            // Ensure directory exists
            File::ensureDirectoryExists($fullPath);

            $filename = "{$toolName}.php";
            $filePath = "{$fullPath}/{$filename}";

            $saved = File::put($filePath, $code);

            if ($saved === false) {
                throw ToolGenerationException::saveFailed($toolName, $filePath);
            }

            Log::info('MCP Builder: Tool generated successfully', [
                'tool' => $toolName,
                'path' => $filePath
            ]);

            return $filePath;
        } catch (ToolGenerationException $e) {
            Log::error('MCP Builder: Tool generation exception', [
                'tool' => $toolName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Unexpected exception generating tool', [
                'tool' => $toolName,
                'error' => $e->getMessage()
            ]);
            throw ToolGenerationException::saveFailed($toolName, $filePath ?? 'unknown');
        }
    }
}

