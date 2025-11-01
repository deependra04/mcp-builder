<?php

namespace Laravel\McpBuilder\Exceptions;

class ServerNotFoundException extends McpBuilderException
{
    public static function forId(int $id): self
    {
        return new self("MCP server with ID {$id} not found.");
    }

    public static function forName(string $name): self
    {
        return new self("MCP server '{$name}' not found.");
    }
}

