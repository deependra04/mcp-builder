<?php

namespace Laravel\McpBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravel\McpBuilder\Services\ServerManager serverManager()
 * @method static \Laravel\McpBuilder\Services\ConfigManager configManager()
 * @method static \Laravel\McpBuilder\Services\CodeGeneratorService codeGenerator()
 */
class McpBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mcp-builder';
    }
}

