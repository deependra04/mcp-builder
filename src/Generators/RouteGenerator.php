<?php

namespace Laravel\McpBuilder\Generators;

use Illuminate\Routing\Route as RouteInstance;
use Laravel\McpBuilder\Analyzers\RouteAnalyzer;
use Illuminate\Support\Str;

class RouteGenerator
{
    protected RouteAnalyzer $analyzer;

    public function __construct(RouteAnalyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Generate MCP tools from Laravel routes.
     */
    public function generateFromRoutes(array $routes = null): array
    {
        if ($routes === null) {
            $analyzedRoutes = $this->analyzer->analyze();
        } else {
            $analyzedRoutes = array_map(function ($route) {
                return $this->analyzer->analyzeRoute($route);
            }, $routes);
        }

        $tools = [];

        foreach ($analyzedRoutes as $routeInfo) {
            $tool = $this->generateToolFromRoute($routeInfo);
            if ($tool) {
                $tools[] = $tool;
            }
        }

        return $tools;
    }

    /**
     * Generate a single MCP tool from route information.
     */
    protected function generateToolFromRoute(array $routeInfo): ?array
    {
        if (empty($routeInfo['methods'])) {
            return null;
        }

        $method = strtolower($routeInfo['methods'][0]);
        $toolName = $this->generateToolName($routeInfo);
        $description = $this->generateDescription($routeInfo);

        $inputSchema = [
            'type' => 'object',
            'properties' => $this->buildPropertiesFromRoute($routeInfo),
        ];

        // Add required fields
        $required = $this->extractRequiredFields($routeInfo);
        if (!empty($required)) {
            $inputSchema['required'] = $required;
        }

        return [
            'name' => $toolName,
            'description' => $description,
            'inputSchema' => $inputSchema,
            'metadata' => [
                'uri' => $routeInfo['uri'],
                'method' => $method,
                'controller' => $routeInfo['controller'] ?? null,
                'action' => $routeInfo['method'] ?? null,
            ],
        ];
    }

    /**
     * Generate tool name from route information.
     */
    protected function generateToolName(array $routeInfo): string
    {
        if (!empty($routeInfo['name'])) {
            return str_replace('.', '_', $routeInfo['name']);
        }

        $uri = Str::slug($routeInfo['uri'], '_');
        $method = strtolower($routeInfo['methods'][0] ?? 'get');
        
        return "{$method}_{$uri}";
    }

    /**
     * Generate description from route information.
     */
    protected function generateDescription(array $routeInfo): string
    {
        $method = strtoupper($routeInfo['methods'][0] ?? 'GET');
        $uri = $routeInfo['uri'];
        
        if (!empty($routeInfo['name'])) {
            return Str::title(str_replace(['.', '_', '-'], ' ', $routeInfo['name']));
        }

        return "{$method} request to {$uri}";
    }

    /**
     * Build properties schema from route parameters.
     */
    protected function buildPropertiesFromRoute(array $routeInfo): array
    {
        $properties = [];
        $parameters = $routeInfo['parameters'] ?? [];

        // Extract route parameters from URI
        preg_match_all('/\{(\w+)\}/', $routeInfo['uri'], $matches);
        $routeParams = $matches[1] ?? [];

        foreach ($routeParams as $param) {
            $properties[$param] = [
                'type' => 'string',
                'description' => Str::title(str_replace('_', ' ', $param)),
            ];
        }

        // Add controller method parameters
        foreach ($parameters as $param) {
            $name = $param['name'];
            
            // Skip route model binding parameters already added
            if (isset($properties[$name])) {
                continue;
            }

            $type = $this->mapPhpTypeToJsonSchema($param['type'] ?? 'string');
            
            $properties[$name] = [
                'type' => $type,
                'description' => Str::title(str_replace('_', ' ', $name)),
            ];

            if ($param['nullable'] ?? false) {
                $properties[$name]['nullable'] = true;
            }

            if (isset($param['default'])) {
                $properties[$name]['default'] = $param['default'];
            }
        }

        return $properties;
    }

    /**
     * Extract required fields from route parameters.
     */
    protected function extractRequiredFields(array $routeInfo): array
    {
        $required = [];

        // Route parameters are always required
        preg_match_all('/\{(\w+)\}/', $routeInfo['uri'], $matches);
        $routeParams = $matches[1] ?? [];
        $required = array_merge($required, $routeParams);

        // Check controller parameters
        $parameters = $routeInfo['parameters'] ?? [];
        foreach ($parameters as $param) {
            if (!($param['nullable'] ?? false) && !isset($param['default'])) {
                $required[] = $param['name'];
            }
        }

        return array_unique($required);
    }

    /**
     * Map PHP type to JSON schema type.
     */
    protected function mapPhpTypeToJsonSchema(?string $phpType): string
    {
        if (!$phpType) {
            return 'string';
        }

        return match (strtolower($phpType)) {
            'int', 'integer' => 'integer',
            'bool', 'boolean' => 'boolean',
            'float', 'double' => 'number',
            'array' => 'array',
            'object' => 'object',
            default => 'string',
        };
    }
}

