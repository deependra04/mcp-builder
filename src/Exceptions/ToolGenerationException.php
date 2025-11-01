<?php

namespace Laravel\McpBuilder\Exceptions;

class ToolGenerationException extends McpBuilderException
{
    public static function saveFailed(string $toolName, string $path): self
    {
        return new self("Failed to save tool '{$toolName}' to path: {$path}");
    }

    public static function invalidName(string $toolName): self
    {
        return new self("Invalid tool name: '{$toolName}'. Tool names must be valid identifiers.");
    }
}

