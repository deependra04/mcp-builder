<?php

namespace Laravel\McpBuilder\Exceptions;

class ConfigFileException extends McpBuilderException
{
    public static function notFound(string $path): self
    {
        return new self("Configuration file not found: {$path}");
    }

    public static function invalidFormat(string $format): self
    {
        return new self("Unsupported config file format: {$format}. Supported formats: YAML, JSON");
    }

    public static function parseError(string $format, string $message): self
    {
        return new self("Failed to parse {$format} configuration: {$message}");
    }

    public static function invalidStructure(string $field): self
    {
        return new self("Invalid configuration structure: missing required field '{$field}'");
    }
}

