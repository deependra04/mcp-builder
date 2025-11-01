# Laravel MCP Builder - Complete Documentation

Complete documentation for the Laravel MCP Builder package.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Commands](#commands)
- [Generating MCP Servers](#generating-mcp-servers)
- [Generating Tools](#generating-tools)
- [Web Dashboard](#web-dashboard)
- [API Usage](#api-usage)
- [Laravel Boost Integration](#laravel-boost-integration)
- [Advanced Features](#advanced-features)
- [Troubleshooting](#troubleshooting)

---

## Installation

### Step 1: Add Repository

Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/deependra04/mcp-builder"
        }
    ]
}
```

### Step 2: Install Package

```bash
composer require deependra04/mcp-builder:dev-main
```

### Step 3: Publish & Migrate

```bash
php artisan vendor:publish --tag=mcp-builder-config
php artisan vendor:publish --tag=mcp-builder-migrations
php artisan migrate
```

### Step 4: Optional - Publish Views/Assets

```bash
# To customize dashboard views
php artisan vendor:publish --tag=mcp-builder-views

# To customize assets
php artisan vendor:publish --tag=mcp-builder-assets
```

---

## Configuration

### Configuration File

Located at `config/mcp-builder.php` after publishing.

### Key Settings

```php
return [
    // Storage paths
    'storage_path' => storage_path('mcp'),
    'config_path' => base_path('mcp-configs'),
    'tools_path' => app_path('Mcp/Tools'),
    
    // Dashboard settings
    'dashboard' => [
        'enabled' => true,
        'prefix' => 'mcp-builder', // Change to customize dashboard URL
        'middleware' => ['web'],
        'auth' => [
            'enabled' => false,
            'permission' => null, // Set permission name if auth enabled
        ],
    ],
    
    // Auto-generation (not recommended for production)
    'auto_generate' => [
        'models' => false,
        'routes' => false,
    ],
    
    // Tool generation defaults
    'tool_defaults' => [
        'namespace' => 'App\\Mcp\\Tools',
        'suffix' => 'Tool',
    ],
];
```

### Environment Variables

```env
MCP_BUILDER_DASHBOARD_ENABLED=true
MCP_BUILDER_DASHBOARD_PREFIX=mcp-builder
MCP_BUILDER_DASHBOARD_AUTH_ENABLED=false
MCP_BUILDER_DASHBOARD_AUTH_PERMISSION=null
MCP_BUILDER_AUTO_GENERATE_MODELS=false
MCP_BUILDER_AUTO_GENERATE_ROUTES=false
```

---

## Commands

### Setup Wizard

Interactive setup for creating MCP servers:

```bash
php artisan mcp:setup
```

**Options:**
- `--quick` - Skip interactive prompts, use defaults

### Generate from Model

Generate CRUD tools from Eloquent models:

```bash
php artisan mcp:generate-model App\Models\User
php artisan mcp:generate-model App\Models\Post --save --server=my-server
```

**Options:**
- `--save` - Save tools to server configuration
- `--server=name` - Target server name

**Generated Tools:**
- `{model}_list` - List all records
- `{model}_show` - Show single record
- `{model}_create` - Create new record
- `{model}_update` - Update record
- `{model}_delete` - Delete record

### Generate from Routes

Generate tools from Laravel routes:

```bash
php artisan mcp:generate-routes
php artisan mcp:generate-routes --save --server=my-server
php artisan mcp:generate-routes --filter=api --save
```

**Options:**
- `--save` - Save tools to server
- `--server=name` - Target server
- `--filter=pattern` - Filter routes by pattern

### Generate from Config

Generate server from YAML/JSON configuration:

```bash
php artisan mcp:generate-config config/mcp-server.yaml
php artisan mcp:generate-config config/mcp-server.json --name=my-server
```

**Options:**
- `--name=name` - Custom server name

### Generate Custom Tool

Create a custom MCP tool:

```bash
php artisan mcp:make-tool ProcessPayment
php artisan mcp:make-tool SendEmail --description="Send email" --namespace="App\\Mcp\\Tools"
```

**Options:**
- `--description="text"` - Tool description
- `--namespace="path"` - Custom namespace

### Batch Generation

Generate tools from multiple models:

```bash
php artisan mcp:generate-batch "App\Models\User,App\Models\Post,App\Models\Comment" --save --server=my-server
```

**Options:**
- `--save` - Save all tools
- `--server=name` - Target server

### Integrate Laravel Boost

Auto-install and integrate Boost tools:

```bash
php artisan mcp:integrate-boost my-server
```

**Options:**
- `--auto-install` - Automatically install Boost if missing
- `--skip-install` - Skip installation if not found

### Export/Import

```bash
# Export server configuration
php artisan mcp:export-server my-server

# Import server configuration
php artisan mcp:import-server backup.json --name=restored-server
```

### Validate Configuration

```bash
php artisan mcp:validate-config my-server
php artisan mcp:validate-config config/mcp-server.json
```

---

## Generating MCP Servers

### Method 1: Interactive Setup

```bash
php artisan mcp:setup
```

Follows wizard prompts to create server.

### Method 2: From Configuration File

**YAML Example (`config/mcp-server.yaml`):**

```yaml
name: my-api-server
version: 1.0.0
description: My API MCP Server
tools:
  - name: get_user
    description: Get user by ID
    inputSchema:
      type: object
      properties:
        id:
          type: integer
          description: User ID
```

**JSON Example (`config/mcp-server.json`):**

```json
{
  "name": "my-api-server",
  "version": "1.0.0",
  "description": "My API MCP Server",
  "tools": [
    {
      "name": "get_user",
      "description": "Get user by ID",
      "inputSchema": {
        "type": "object",
        "properties": {
          "id": {
            "type": "integer",
            "description": "User ID"
          }
        }
      }
    }
  ]
}
```

Generate:

```bash
php artisan mcp:generate-config config/mcp-server.yaml
```

### Method 3: From Models

```bash
php artisan mcp:generate-model App\Models\User --save --server=my-api
```

### Method 4: From Routes

```bash
php artisan mcp:generate-routes --filter=api --save --server=my-api
```

---

## Generating Tools

### From Eloquent Models

Automatically generates 5 CRUD tools per model:

```bash
php artisan mcp:generate-model App\Models\User --save --server=my-server
```

**What gets generated:**

1. **List Tool** (`user_list`)
   - Lists all users with pagination
   - Input: `page`, `per_page`
   - Output: Paginated user collection

2. **Show Tool** (`user_show`)
   - Shows single user by ID
   - Input: `id`
   - Output: User model

3. **Create Tool** (`user_create`)
   - Creates new user
   - Input: All fillable attributes
   - Output: Created user

4. **Update Tool** (`user_update`)
   - Updates existing user
   - Input: `id` + attributes to update
   - Output: Updated user

5. **Delete Tool** (`user_delete`)
   - Deletes user
   - Input: `id`
   - Output: Success confirmation

### From Routes

Converts Laravel routes to MCP tools:

```bash
php artisan mcp:generate-routes --filter=api --save
```

- Extracts route parameters
- Generates input schemas
- Maps to controller methods

### Custom Tools

Create custom tools manually:

```bash
php artisan mcp:make-tool ProcessPayment --description="Process payment transaction"
```

This creates a stub file at `app/Mcp/Tools/ProcessPaymentTool.php`.

**Example Custom Tool:**

```php
<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Tool;

class ProcessPaymentTool extends Tool
{
    public function name(): string
    {
        return 'process_payment';
    }

    public function description(): string
    {
        return 'Process a payment transaction';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'amount' => [
                    'type' => 'number',
                    'description' => 'Payment amount',
                ],
                'currency' => [
                    'type' => 'string',
                    'description' => 'Currency code',
                ],
            ],
            'required' => ['amount', 'currency'],
        ];
    }

    public function execute(array $arguments): array
    {
        // Your logic here
        return ['status' => 'success'];
    }
}
```

---

## Web Dashboard

### Accessing Dashboard

Default URL: `/mcp-builder` (configurable via `dashboard.prefix`)

### Features

1. **Overview Dashboard**
   - Server statistics
   - Recent servers and tools
   - Quick actions

2. **Server Management**
   - Create, edit, delete servers
   - View server details
   - Manage tools

3. **Tool Management**
   - Browse all tools
   - View tool details
   - Filter by server

### Routes

**Web Routes:**
- `GET /mcp-builder` - Dashboard home
- `GET /mcp-builder/servers` - List servers
- `GET /mcp-builder/servers/create` - Create server
- `GET /mcp-builder/servers/{id}` - View server
- `GET /mcp-builder/servers/{id}/edit` - Edit server
- `POST /mcp-builder/servers` - Store server
- `PUT /mcp-builder/servers/{id}` - Update server
- `DELETE /mcp-builder/servers/{id}` - Delete server
- `GET /mcp-builder/tools` - List tools
- `GET /mcp-builder/tools/{id}` - View tool

**API Routes:**
- All web routes have corresponding API endpoints at `/api/mcp-builder/...`

### Authentication

Enable authentication in config:

```php
'dashboard' => [
    'auth' => [
        'enabled' => true,
        'permission' => 'manage-mcp-servers', // Optional permission check
    ],
],
```

---

## API Usage

### Using Facade

```php
use Laravel\McpBuilder\Facades\McpBuilder;

// Get services
$serverManager = McpBuilder::serverManager();
$configManager = McpBuilder::configManager();
$codeGenerator = McpBuilder::codeGenerator();

// Examples
$servers = $serverManager->getAll();
$config = $configManager->loadConfig('my-server');
$tools = $codeGenerator->generateFromModel('App\Models\User');
```

### Dependency Injection

```php
use Laravel\McpBuilder\Services\ServerManager;
use Laravel\McpBuilder\Generators\ModelGenerator;

class MyController extends Controller
{
    public function __construct(
        private ServerManager $serverManager,
        private ModelGenerator $modelGenerator
    ) {}

    public function index()
    {
        $servers = $this->serverManager->getAll();
        $tools = $this->modelGenerator->generateFromModel('App\Models\User');
    }
}
```

### Available Services

- `ServerManager` - CRUD operations for servers
- `ConfigManager` - Configuration file management
- `CodeGeneratorService` - Generate tools and code
- `DashboardService` - Dashboard data and statistics
- `ValidationService` - Validate configurations
- `ExportService` - Export/import functionality
- `ErrorHandlerService` - Error handling
- `CacheService` - Caching layer

---

## Laravel Boost Integration

### What is Laravel Boost?

Laravel Boost provides 15+ additional MCP tools for enhanced AI-assisted development.

### Auto-Integration

The package automatically detects and integrates Boost:

```bash
php artisan mcp:integrate-boost my-server
```

**What happens:**
1. Checks if Boost is installed
2. Offers installation if missing
3. Merges Boost tools with your tools
4. Updates server configuration

### Boost Tools Included

- Database query tools
- Schema inspection
- Tinker execution
- Documentation access (17,000+ pieces)
- Code analysis
- Route/model inspection

### Manual Integration

If Boost is already installed:

```bash
php artisan mcp:integrate-boost my-server --skip-install
```

---

## Advanced Features

### Custom Validation

Validate server configurations:

```php
use Laravel\McpBuilder\Services\ValidationService;

$validator = app(ValidationService::class);
$result = $validator->validateServerConfig($config);
```

### Error Handling

Custom error handling:

```php
use Laravel\McpBuilder\Services\ErrorHandlerService;

$errorHandler = app(ErrorHandlerService::class);
$formatted = $errorHandler->formatError($exception);
```

### Caching

Configuration caching:

```php
use Laravel\McpBuilder\Services\CacheService;

$cache = app(CacheService::class);
$cache->remember('key', 3600, function() {
    return expensiveOperation();
});
```

### Export/Import

```php
use Laravel\McpBuilder\Services\ExportService;

$export = app(ExportService::class);
$json = $export->exportServer('my-server');
$export->importServer($json, 'restored-server');
```

---

## Troubleshooting

### Common Issues

**Issue: Views not found**
```bash
php artisan view:clear
php artisan config:clear
```

**Issue: Routes not working**
```bash
php artisan route:clear
php artisan config:clear
```

**Issue: Migration errors**
```bash
php artisan migrate:refresh
# Or rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

**Issue: Commands not found**
```bash
composer dump-autoload
php artisan config:clear
```

### Debug Mode

Enable debug in `.env`:

```env
APP_DEBUG=true
```

### Clear All Caches

```bash
php artisan optimize:clear
```

---

## Best Practices

1. **Always validate** configurations before deployment
2. **Use batch operations** for multiple models
3. **Export configurations** before major changes
4. **Enable authentication** for production dashboards
5. **Use namespaces** properly for custom tools
6. **Test generated tools** before deploying

---

## Support

- **GitHub Issues**: [Report bugs](https://github.com/deependra04/mcp-builder/issues)
- **GitHub Discussions**: [Ask questions](https://github.com/deependra04/mcp-builder/discussions)

---

## License

MIT License - See [LICENSE.md](LICENSE.md)

