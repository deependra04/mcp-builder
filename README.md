# Laravel MCP Builder

A comprehensive Laravel package that automates the creation and management of Model Context Protocol (MCP) servers and tools. This package extends Laravel MCP with powerful auto-generation features, a web dashboard, and interactive setup wizards.

**🎯 Transform weeks of MCP development into minutes! Generate MCP servers and tools automatically from your Laravel models, routes, and configurations.**

## Features

- 🚀 **Auto-Generate from Configuration**: Create MCP servers from YAML/JSON configuration files
- 🎯 **Model Integration**: Automatically generate CRUD tools from Eloquent models
- 🛣️ **Route Integration**: Generate MCP tools from existing Laravel routes
- 🎨 **Web Dashboard**: Beautiful web interface for managing servers and tools
- ⚡ **Interactive Setup**: CLI wizard for guided MCP server creation
- 🔧 **Code Generation**: Scaffold custom MCP tools with artisan commands
- 📦 **Extends Laravel MCP**: Built on top of the official Laravel MCP package

## Installation

### Quick Install (One Command!)

1. **Add the GitHub repository to your `composer.json`:**

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

2. **Install the package:**

```bash
composer require deependra04/mcp-builder:dev-main
```

That's it! 🎉 The package will automatically install Laravel MCP (its dependency) and be ready to use.

### Post-Installation Setup

After installation, publish the configuration and run migrations:

```bash
php artisan vendor:publish --tag=mcp-builder-config
php artisan vendor:publish --tag=mcp-builder-migrations
php artisan migrate
```

You're all set! Access the dashboard at `/mcp-builder` or start creating servers with `php artisan mcp:setup`.

> **Note:** See [INSTALLATION.md](INSTALLATION.md) for more details and troubleshooting.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=mcp-builder-config
```

This will create `config/mcp-builder.php` with customizable settings.

## Setup

### 1. Publish Migrations

```bash
php artisan vendor:publish --tag=mcp-builder-migrations
php artisan migrate
```

### 2. Publish Views (Optional)

If you want to customize the dashboard views:

```bash
php artisan vendor:publish --tag=mcp-builder-views
```

### 3. Publish Assets (Optional)

If you want to customize the dashboard CSS/JS:

```bash
php artisan vendor:publish --tag=mcp-builder-assets
```

## Usage

### Interactive Setup Wizard

The easiest way to create an MCP server is using the interactive setup wizard:

```bash
php artisan mcp:setup
```

This will guide you through:
- Server basic information (name, version, description)
- Generation method selection
- Configuration based on your choice

### Quick Setup

For a faster setup with defaults:

```bash
php artisan mcp:setup --quick
```

### Generate from Configuration File

Create an MCP server from a YAML or JSON configuration file:

```bash
php artisan mcp:generate-config config/mcp-server.yaml
php artisan mcp:generate-config config/mcp-server.json --name=my-server
```

Example YAML configuration:

```yaml
name: my-mcp-server
version: 1.0.0
description: My MCP server description
tools:
  - name: example_tool
    description: An example tool
    inputSchema:
      type: object
      properties:
        message:
          type: string
          description: The message to process
```

### Generate from Model

Generate CRUD tools from an Eloquent model:

```bash
php artisan mcp:generate-model App\\Models\\User
php artisan mcp:generate-model App\\Models\\Post --save --server=my-server
```

This will generate:
- `{model}_list` - List all records
- `{model}_show` - Show a specific record
- `{model}_create` - Create a new record
- `{model}_update` - Update a record
- `{model}_delete` - Delete a record

### Generate from Routes

Generate MCP tools from your Laravel routes:

```bash
php artisan mcp:generate-routes
php artisan mcp:generate-routes --save --server=my-server
php artisan mcp:generate-routes --filter=api --save
```

### Create Custom Tools

Generate a custom MCP tool:

```bash
php artisan mcp:make-tool ProcessPayment
php artisan mcp:make-tool SendEmail --description="Send an email" --namespace="App\\Mcp\\Tools"
```

## Web Dashboard

Access the web dashboard at `/mcp-builder` (or your configured prefix).

The dashboard provides:
- **Overview**: Statistics and recent servers/tools
- **Server Management**: Create, edit, view, and delete MCP servers
- **Tool Management**: Browse and manage generated tools

### Dashboard Routes

- `GET /mcp-builder` - Dashboard home
- `GET /mcp-builder/servers` - List all servers
- `GET /mcp-builder/servers/create` - Create new server
- `GET /mcp-builder/servers/{id}` - View server details
- `GET /mcp-builder/tools` - List all tools

## Configuration Options

Edit `config/mcp-builder.php` to customize:

```php
return [
    'storage_path' => storage_path('mcp'),
    'config_path' => base_path('mcp-configs'),
    'tools_path' => app_path('Mcp/Tools'),
    
    'dashboard' => [
        'enabled' => true,
        'prefix' => 'mcp-builder',
        'middleware' => ['web'],
    ],
    
    'auto_generate' => [
        'models' => false,
        'routes' => false,
    ],
    
    'tool_defaults' => [
        'namespace' => 'App\\Mcp\\Tools',
        'suffix' => 'Tool',
    ],
];
```

## Programmatic Usage

### Using the Facade

```php
use Laravel\McpBuilder\Facades\McpBuilder;

// Get server manager
$serverManager = McpBuilder::serverManager();
$servers = $serverManager->getAll();

// Get config manager
$configManager = McpBuilder::configManager();
$config = $configManager->loadConfig('my-server');

// Get code generator
$codeGenerator = McpBuilder::codeGenerator();
$tools = $codeGenerator->generateFromModel('App\\Models\\User');
```

### Using Dependency Injection

```php
use Laravel\McpBuilder\Services\ServerManager;
use Laravel\McpBuilder\Generators\ModelGenerator;

class MyController extends Controller
{
    public function __construct(
        private ServerManager $serverManager,
        private ModelGenerator $modelGenerator
    ) {}
}
```

## Extending Laravel MCP

This package extends Laravel MCP and works seamlessly with it. All generated tools follow Laravel MCP conventions and can be used alongside manually created tools.

## Requirements

- PHP 8.2+
- Laravel 10.0+ or 11.0+
- Laravel MCP package (installed automatically)
- Symfony YAML component (installed automatically)

### Optional Dependencies

- **Laravel Boost** - For enhanced MCP tools and AI-assisted development:
  ```bash
  composer require laravel/boost --dev
  ```
  Then integrate Boost tools: `php artisan mcp:integrate-boost my-server`

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The Laravel MCP Builder package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/deependra04/mcp-builder).

## Laravel Boost Integration ✨

This package integrates seamlessly with [Laravel Boost](https://github.com/laravel/boost), providing you with **15+ additional MCP tools** out of the box.

### 🎁 What You Get with Boost

**Laravel Boost automatically installs when needed and provides:**

- 🗄️ **Database Tools**: Query database, inspect schema, analyze tables
- 💻 **Tinker Execution**: Run PHP code directly in Laravel environment
- 📚 **Documentation**: Access 17,000+ Laravel documentation pieces
- 🔍 **Code Analysis**: Inspect routes, models, configs, and services
- 🎯 **Laravel Guidelines**: AI follows Laravel best practices automatically
- 🚀 **Enhanced AI**: Context-aware assistance that understands your app

> **📖 See [BOOST_BENEFITS.md](BOOST_BENEFITS.md) for detailed benefits and examples**

### Quick Integration (Auto-Install)

```bash
# Just integrate - Boost will auto-install if not present!
php artisan mcp:integrate-boost my-server

# Or include during setup
php artisan mcp:setup
# (Wizard will ask to install Boost automatically)
```

**The package automatically:**
- ✅ Checks if Boost is installed
- ✅ Offers to install if missing
- ✅ Integrates all Boost tools
- ✅ Merges with your existing tools

## Why Use This Package?

**Before MCP Builder:**
- ❌ Write 500+ lines of code per model for MCP tools
- ❌ Manually create MCP servers (weeks of work)
- ❌ No visual management interface
- ❌ Hard to maintain and update

**With MCP Builder:**
- ✅ Generate 5 CRUD tools in 30 seconds
- ✅ Create complete MCP servers in minutes
- ✅ Beautiful web dashboard for management
- ✅ Easy updates and maintenance

**Result:** 90% less code, 99% faster development! 🚀

> **📖 See [BENEFITS.md](BENEFITS.md) for detailed benefits for your project and developers**

## Roadmap

- [x] Laravel Boost integration
- [ ] Advanced tool validation
- [ ] Tool testing framework
- [ ] Export/import configurations
- [ ] Real-time server status monitoring
- [ ] Tool versioning
- [ ] Advanced filtering and search in dashboard

