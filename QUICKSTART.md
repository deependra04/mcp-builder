# Quick Start Guide

Get up and running with Laravel MCP Builder in 3 simple steps!

## Step 1: Install (One Command!)

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

Then install:

```bash
composer require deependra04/mcp-builder:dev-main
```

**Done!** Laravel MCP and all dependencies are installed automatically.

## Step 2: Configure

```bash
php artisan vendor:publish --tag=mcp-builder-config
php artisan vendor:publish --tag=mcp-builder-migrations
php artisan migrate
```

## Step 3: Create Your First Server

```bash
php artisan mcp:setup
```

Follow the wizard, or visit `/mcp-builder` in your browser to use the dashboard!

## That's It! 🎉

You now have:
- ✅ MCP Builder installed
- ✅ Laravel MCP installed (automatically)
- ✅ Dashboard at `/mcp-builder`
- ✅ All artisan commands ready to use

## Next Steps

- Generate tools from models: `php artisan mcp:generate-model App\\Models\\User`
- Generate tools from routes: `php artisan mcp:generate-routes`
- Create custom tools: `php artisan mcp:make-tool MyTool`

Read the [README.md](README.md) for more features and options!

