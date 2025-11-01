<?php

namespace Laravel\McpBuilder\Exceptions;

class ModelNotFoundException extends McpBuilderException
{
    public static function forClass(string $className): self
    {
        return new self("Model class not found: {$className}");
    }

    public static function notEloquent(string $className): self
    {
        return new self("Class '{$className}' is not an Eloquent model.");
    }
}

