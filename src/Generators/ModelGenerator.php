<?php

namespace Laravel\McpBuilder\Generators;

use Illuminate\Database\Eloquent\Model;
use Laravel\McpBuilder\Analyzers\ModelAnalyzer;
use Laravel\McpBuilder\Exceptions\ModelNotFoundException;
use Illuminate\Support\Str;

class ModelGenerator
{
    protected ModelAnalyzer $analyzer;

    public function __construct(ModelAnalyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Generate MCP tools from an Eloquent model.
     */
    public function generateFromModel(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            throw ModelNotFoundException::forClass($modelClass);
        }

        $model = app($modelClass);
        
        if (!($model instanceof Model)) {
            throw ModelNotFoundException::notEloquent($modelClass);
        }

        $analysis = $this->analyzer->analyze($model);
        
        return [
            'list_tool' => $this->generateListTool($analysis),
            'show_tool' => $this->generateShowTool($analysis),
            'create_tool' => $this->generateCreateTool($analysis),
            'update_tool' => $this->generateUpdateTool($analysis),
            'delete_tool' => $this->generateDeleteTool($analysis),
        ];
    }

    /**
     * Generate list tool configuration.
     */
    protected function generateListTool(array $analysis): array
    {
        $toolName = $this->analyzer->generateToolName($analysis['model_name']);
        
        return [
            'name' => "{$toolName}_list",
            'description' => "List all {$analysis['model_name']} records",
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number',
                        'default' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'description' => 'Items per page',
                        'default' => 15,
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate show tool configuration.
     */
    protected function generateShowTool(array $analysis): array
    {
        $toolName = $this->analyzer->generateToolName($analysis['model_name']);
        
        return [
            'name' => "{$toolName}_show",
            'description' => "Show a specific {$analysis['model_name']} record",
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => "{$analysis['model_name']} ID",
                    ],
                ],
                'required' => ['id'],
            ],
        ];
    }

    /**
     * Generate create tool configuration.
     */
    protected function generateCreateTool(array $analysis): array
    {
        $toolName = $this->analyzer->generateToolName($analysis['model_name']);
        $properties = $this->buildPropertiesFromFillable($analysis);
        
        return [
            'name' => "{$toolName}_create",
            'description' => "Create a new {$analysis['model_name']} record",
            'inputSchema' => [
                'type' => 'object',
                'properties' => $properties,
            ],
        ];
    }

    /**
     * Generate update tool configuration.
     */
    protected function generateUpdateTool(array $analysis): array
    {
        $toolName = $this->analyzer->generateToolName($analysis['model_name']);
        $properties = $this->buildPropertiesFromFillable($analysis);
        $properties['id'] = [
            'type' => 'integer',
            'description' => "{$analysis['model_name']} ID",
        ];
        
        return [
            'name' => "{$toolName}_update",
            'description' => "Update an existing {$analysis['model_name']} record",
            'inputSchema' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => ['id'],
            ],
        ];
    }

    /**
     * Generate delete tool configuration.
     */
    protected function generateDeleteTool(array $analysis): array
    {
        $toolName = $this->analyzer->generateToolName($analysis['model_name']);
        
        return [
            'name' => "{$toolName}_delete",
            'description' => "Delete a {$analysis['model_name']} record",
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => "{$analysis['model_name']} ID",
                    ],
                ],
                'required' => ['id'],
            ],
        ];
    }

    /**
     * Build properties schema from fillable fields.
     */
    protected function buildPropertiesFromFillable(array $analysis): array
    {
        $properties = [];
        $columns = $analysis['columns'];
        $casts = $analysis['casts'];

        foreach ($analysis['fillable'] as $field) {
            if (!isset($columns[$field])) {
                continue;
            }

            $columnInfo = $columns[$field];
            $type = $this->mapColumnTypeToJsonSchema($columnInfo['type'], $casts[$field] ?? null);

            $properties[$field] = [
                'type' => $type,
                'description' => Str::title(str_replace('_', ' ', $field)),
            ];

            if ($columnInfo['nullable']) {
                $properties[$field]['nullable'] = true;
            }
        }

        return $properties;
    }

    /**
     * Map database column type to JSON schema type.
     */
    protected function mapColumnTypeToJsonSchema(string $dbType, ?string $cast = null): string
    {
        if ($cast) {
            return match ($cast) {
                'int', 'integer' => 'integer',
                'bool', 'boolean' => 'boolean',
                'float', 'double' => 'number',
                'array', 'json' => 'object',
                'datetime', 'date' => 'string',
                default => 'string',
            };
        }

        return match (true) {
            str_contains($dbType, 'int') => 'integer',
            str_contains($dbType, 'bool') => 'boolean',
            str_contains($dbType, 'float') || str_contains($dbType, 'double') || str_contains($dbType, 'decimal') => 'number',
            str_contains($dbType, 'json') => 'object',
            default => 'string',
        };
    }
}

