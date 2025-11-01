<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Builder Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Laravel MCP Builder package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Storage Path
    |--------------------------------------------------------------------------
    |
    | The default path where MCP server configurations and generated files
    | will be stored.
    |
    */

    'storage_path' => storage_path('mcp'),

    /*
    |--------------------------------------------------------------------------
    | Configuration Files Path
    |--------------------------------------------------------------------------
    |
    | The path where YAML/JSON configuration files for MCP servers are located.
    |
    */

    'config_path' => base_path('mcp-configs'),

    /*
    |--------------------------------------------------------------------------
    | Generated Tools Path
    |--------------------------------------------------------------------------
    |
    | The path where generated MCP tools will be stored.
    |
    */

    'tools_path' => app_path('Mcp/Tools'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the web dashboard.
    |
    */

    'dashboard' => [
        'enabled' => env('MCP_BUILDER_DASHBOARD_ENABLED', true),
        'prefix' => env('MCP_BUILDER_DASHBOARD_PREFIX', 'mcp-builder'),
        'middleware' => ['web'],
        'auth' => [
            'enabled' => env('MCP_BUILDER_DASHBOARD_AUTH_ENABLED', false),
            'permission' => env('MCP_BUILDER_DASHBOARD_AUTH_PERMISSION', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Generation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic generation of MCP tools from models and routes.
    |
    */

    'auto_generate' => [
        'models' => env('MCP_BUILDER_AUTO_GENERATE_MODELS', false),
        'routes' => env('MCP_BUILDER_AUTO_GENERATE_ROUTES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tool Generation Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for generated MCP tools.
    |
    */

    'tool_defaults' => [
        'namespace' => 'App\\Mcp\\Tools',
        'suffix' => 'Tool',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation URL
    |--------------------------------------------------------------------------
    |
    | Base URL for documentation links in error messages.
    |
    */

    'documentation_url' => env('MCP_BUILDER_DOCS_URL', 'https://docs.example.com'),
];

