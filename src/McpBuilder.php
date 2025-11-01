<?php

namespace Laravel\McpBuilder;

use Laravel\McpBuilder\Services\ServerManager;
use Laravel\McpBuilder\Services\ConfigManager;
use Laravel\McpBuilder\Services\CodeGeneratorService;

class McpBuilder
{
    /**
     * Get the server manager instance.
     */
    public function serverManager(): ServerManager
    {
        return app('mcp-builder.server-manager');
    }

    /**
     * Get the config manager instance.
     */
    public function configManager(): ConfigManager
    {
        return app('mcp-builder.config-manager');
    }

    /**
     * Get the code generator service instance.
     */
    public function codeGenerator(): CodeGeneratorService
    {
        return app('mcp-builder.code-generator');
    }
}

