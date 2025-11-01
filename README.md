# Laravel MCP Builder

<div align="center">

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)
![Laravel Version](https://img.shields.io/badge/laravel-%5E10.0%7C%5E11.0-red.svg)

**Transform weeks of MCP development into minutes**

Automated MCP server and tool generation with web dashboard

[Installation](#installation) • [Quick Start](#quick-start) • [Documentation](DOCUMENTATION.md) • [Support](#support)

</div>

---

## Features

**Core**
- Auto-generate servers from YAML/JSON configs
- Generate CRUD tools from Eloquent models
- Generate tools from Laravel routes
- Batch operations for multiple models
- Custom tool scaffolding

**Web Dashboard**
- Manage servers and tools via UI
- Real-time statistics
- Search and filtering
- Professional error handling

**Developer Experience**
- Interactive CLI wizard
- TypeScript frontend
- Zero conflicts with existing projects
- Auto-integration with Laravel Boost

---

## Installation

### 1. Add Repository

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

### 2. Install Package

```bash
composer require deependra04/mcp-builder:dev-main
```

### 3. Publish & Migrate

```bash
php artisan vendor:publish --tag=mcp-builder-config
php artisan vendor:publish --tag=mcp-builder-migrations
php artisan migrate
```

Access dashboard at `/mcp-builder` or run `php artisan mcp:setup`

---

## Quick Start

### Interactive Setup

```bash
php artisan mcp:setup
```

### Generate from Model

```bash
php artisan mcp:generate-model App\Models\User --save --server=my-api
```

### Generate from Routes

```bash
php artisan mcp:generate-routes --save --server=my-api
```

### Generate from Config

```bash
php artisan mcp:generate-config config/mcp-server.yaml
```

### Batch Generation

```bash
php artisan mcp:generate-batch "App\Models\User,App\Models\Post" --save --server=my-api
```

---

## Configuration

Publish config: `php artisan vendor:publish --tag=mcp-builder-config`

**Key Settings:**

```php
return [
    'dashboard' => [
        'prefix' => 'mcp-builder', // Dashboard URL prefix
        'auth' => [
            'enabled' => false,
        ],
    ],
    'tool_defaults' => [
        'namespace' => 'App\\Mcp\\Tools',
    ],
];
```

---

## Web Dashboard

Access at `/mcp-builder` (or configured prefix).

**Features:**
- Server and tool management
- Statistics and analytics
- Search and filtering

---

## Laravel Boost Integration

Auto-install and integrate 15+ Boost tools:

```bash
php artisan mcp:integrate-boost my-server
```

**Includes:**
- Database query tools
- Schema inspection
- Tinker execution
- Documentation access
- Code analysis

---

## API Usage

### Facade

```php
use Laravel\McpBuilder\Facades\McpBuilder;

$serverManager = McpBuilder::serverManager();
$servers = $serverManager->getAll();
```

### Dependency Injection

```php
use Laravel\McpBuilder\Services\ServerManager;

class MyController extends Controller
{
    public function __construct(private ServerManager $serverManager) {}
}
```

---

## TypeScript

Frontend uses TypeScript for type safety.

**Build:**
```bash
npm install
npm run build
```

---

## Safety & Isolation

**100% isolated** - Zero conflicts with your project:

- Routes prefixed with `mcp-builder`
- Views namespaced `mcp-builder::`
- Database tables prefixed `mcp_`
- All assets in `vendor/mcp-builder`

Can be completely removed without affecting your project.

---

## Requirements

- PHP 8.2+
- Laravel 10.0+ or 11.0+
- Laravel MCP (auto-installed)

**Optional:**
- Laravel Boost (for enhanced tools)
- Node.js 18+ (for TypeScript)

---

## Documentation

📖 **[Complete Documentation](DOCUMENTATION.md)** - Full guide with examples, API reference, and troubleshooting.

---

## Commands

- `mcp:setup` - Interactive server setup
- `mcp:generate-model` - Generate tools from model
- `mcp:generate-routes` - Generate tools from routes
- `mcp:generate-config` - Generate from config file
- `mcp:generate-batch` - Batch generate from multiple models
- `mcp:make-tool` - Create custom tool
- `mcp:integrate-boost` - Integrate Laravel Boost
- `mcp:export-server` - Export server config
- `mcp:import-server` - Import server config
- `mcp:validate-config` - Validate configuration

---

## Roadmap

**v1.0.0** ✅
- Core features, dashboard, Boost integration, TypeScript

**v1.1.0** (Q2 2024)
- Advanced validation, testing framework, real-time monitoring

**v1.2.0** (Q3 2024)
- Template system, hooks, performance optimizations

**v2.0.0** (Q4 2024)
- Multi-tenant, marketplace, AI-assisted generation

---

## Contributing

Contributions welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

**Quick Start:**
```bash
git clone https://github.com/deependra04/mcp-builder.git
cd mcp-builder
composer install
npm install
```

---

## FAQ

**Will this affect my project?** No - completely isolated.

**Can I uninstall safely?** Yes - `composer remove deependra04/mcp-builder`

**Does it require Boost?** No - Boost is optional but recommended.

**Production ready?** Yes - stable and tested.

---

## Support

- [GitHub Issues](https://github.com/deependra04/mcp-builder/issues)
- [GitHub Discussions](https://github.com/deependra04/mcp-builder/discussions)
- [Documentation](DOCUMENTATION.md)

---

## License

MIT License - See [LICENSE.md](LICENSE.md)

---

<div align="center">

**Made with ❤️ for the Laravel community**

[⭐ Star on GitHub](https://github.com/deependra04/mcp-builder) • [📖 Documentation](DOCUMENTATION.md) • [🐛 Report Bug](https://github.com/deependra04/mcp-builder/issues)

</div>
