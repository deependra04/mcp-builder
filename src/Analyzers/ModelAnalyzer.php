<?php

namespace Laravel\McpBuilder\Analyzers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class ModelAnalyzer
{
    /**
     * Analyze an Eloquent model and extract information for MCP tool generation.
     */
    public function analyze(Model $model): array
    {
        $reflection = new ReflectionClass($model);
        $tableName = $model->getTable();
        
        return [
            'model' => get_class($model),
            'model_name' => $reflection->getShortName(),
            'table' => $tableName,
            'fillable' => $model->getFillable(),
            'casts' => $model->getCasts(),
            'relationships' => $this->analyzeRelationships($reflection),
            'columns' => $this->getTableColumns($tableName),
            'primary_key' => $model->getKeyName(),
            'timestamps' => $model->usesTimestamps(),
        ];
    }

    /**
     * Get table columns information.
     */
    protected function getTableColumns(string $tableName): array
    {
        $columns = Schema::getColumnListing($tableName);
        $details = [];

        foreach ($columns as $column) {
            $details[$column] = [
                'type' => Schema::getColumnType($tableName, $column),
                'nullable' => Schema::isNullable($tableName, $column),
            ];
        }

        return $details;
    }

    /**
     * Analyze model relationships.
     */
    protected function analyzeRelationships(ReflectionClass $reflection): array
    {
        $relationships = [];
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            if (preg_match('/^(hasOne|hasMany|belongsTo|belongsToMany|hasManyThrough|morphTo|morphMany)$/', $method->getName())) {
                continue;
            }

            $docComment = $method->getDocComment();
            if ($docComment && preg_match('/@return\s+(\w+)/', $docComment, $matches)) {
                $returnType = $matches[1];
                if (in_array($returnType, ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphTo', 'MorphMany'])) {
                    $relationships[] = [
                        'method' => $method->getName(),
                        'type' => $returnType,
                    ];
                }
            }
        }

        // Check for relationship methods manually
        $relationshipMethods = [];
        foreach ($methods as $method) {
            $name = $method->getName();
            if (method_exists(Model::class, $name)) {
                continue;
            }

            try {
                $returnValue = $method->invoke($reflection->newInstanceWithoutConstructor());
                if (is_object($returnValue) && in_array(get_class($returnValue), [
                    'Illuminate\Database\Eloquent\Relations\HasOne',
                    'Illuminate\Database\Eloquent\Relations\HasMany',
                    'Illuminate\Database\Eloquent\Relations\BelongsTo',
                    'Illuminate\Database\Eloquent\Relations\BelongsToMany',
                    'Illuminate\Database\Eloquent\Relations\MorphTo',
                    'Illuminate\Database\Eloquent\Relations\MorphMany',
                ])) {
                    $relationshipMethods[] = $name;
                }
            } catch (\Exception $e) {
                // Ignore methods that can't be invoked
            }
        }

        return $relationshipMethods;
    }

    /**
     * Generate tool name from model name.
     */
    public function generateToolName(string $modelName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
    }
}

