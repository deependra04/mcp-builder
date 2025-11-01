<?php

namespace Laravel\McpBuilder;

use Illuminate\Support\ServiceProvider;
use Laravel\McpBuilder\Commands\GenerateFromConfig;
use Laravel\McpBuilder\Commands\GenerateFromModel;
use Laravel\McpBuilder\Commands\GenerateFromRoutes;
use Laravel\McpBuilder\Commands\GenerateTool;
use Laravel\McpBuilder\Commands\GenerateBatch;
use Laravel\McpBuilder\Commands\IntegrateBoost;
use Laravel\McpBuilder\Commands\SetupMcp;
use Laravel\McpBuilder\Commands\ExportServer;
use Laravel\McpBuilder\Commands\ImportServer;
use Laravel\McpBuilder\Commands\ValidateConfig;

class McpBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mcp-builder.php',
            'mcp-builder'
        );

        $this->app->singleton('mcp-builder', function ($app) {
            return new McpBuilder();
        });

        // Register services
        $this->app->singleton('mcp-builder.server-manager', function ($app) {
            return new Services\ServerManager($app);
        });

        $this->app->singleton('mcp-builder.config-manager', function ($app) {
            return new Services\ConfigManager(
                $app,
                $app->make(Generators\ConfigGenerator::class),
                $app->make(Services\CacheService::class)
            );
        });

        $this->app->singleton('mcp-builder.code-generator', function ($app) {
            return new Services\CodeGeneratorService(
                $app,
                $app->make(Generators\ModelGenerator::class),
                $app->make(Generators\RouteGenerator::class),
                $app->make(Generators\ToolGenerator::class)
            );
        });

        $this->app->singleton('mcp-builder.dashboard', function ($app) {
            return new Services\DashboardService($app);
        });

        // Register generators and analyzers
        $this->app->bind(Generators\ConfigGenerator::class);
        $this->app->bind(Generators\ModelGenerator::class, function ($app) {
            return new Generators\ModelGenerator($app->make(Analyzers\ModelAnalyzer::class));
        });
        $this->app->bind(Generators\RouteGenerator::class, function ($app) {
            return new Generators\RouteGenerator($app->make(Analyzers\RouteAnalyzer::class));
        });
        $this->app->bind(Generators\ToolGenerator::class);
        $this->app->bind(Analyzers\ModelAnalyzer::class);
        $this->app->bind(Analyzers\RouteAnalyzer::class);
        $this->app->bind(Builders\ServerBuilder::class);
        $this->app->bind(Prompts\SetupPrompts::class);
        $this->app->bind(Integrations\BoostIntegration::class);
        $this->app->singleton(Services\CacheService::class);
        $this->app->singleton(Services\ExportService::class);
        $this->app->singleton(Services\ValidationService::class);
        $this->app->singleton(Services\ErrorHandlerService::class);
        $this->app->singleton(Services\ErrorResponseFormatter::class);
        $this->app->singleton(Services\ErrorRecoveryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/mcp-builder.php' => config_path('mcp-builder.php'),
        ], 'mcp-builder-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'mcp-builder-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/mcp-builder'),
        ], 'mcp-builder-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/mcp-builder'),
        ], 'mcp-builder-assets');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mcp-builder');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateFromConfig::class,
                GenerateFromModel::class,
                GenerateFromRoutes::class,
                GenerateTool::class,
                GenerateBatch::class,
                SetupMcp::class,
                IntegrateBoost::class,
                ExportServer::class,
                ImportServer::class,
                ValidateConfig::class,
            ]);
        }
    }
}

