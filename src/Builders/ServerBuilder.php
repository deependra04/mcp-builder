<?php

namespace Laravel\McpBuilder\Builders;

use Laravel\McpBuilder\Generators\ConfigGenerator;
use Laravel\McpBuilder\Generators\ModelGenerator;
use Laravel\McpBuilder\Generators\RouteGenerator;
use Laravel\McpBuilder\Services\ConfigManager;
use Laravel\McpBuilder\Integrations\BoostIntegration;

class ServerBuilder
{
    protected ConfigGenerator $configGenerator;
    protected ModelGenerator $modelGenerator;
    protected RouteGenerator $routeGenerator;
    protected ConfigManager $configManager;
    protected BoostIntegration $boostIntegration;

    public function __construct(
        ConfigGenerator $configGenerator,
        ModelGenerator $modelGenerator,
        RouteGenerator $routeGenerator,
        ConfigManager $configManager,
        BoostIntegration $boostIntegration
    ) {
        $this->configGenerator = $configGenerator;
        $this->modelGenerator = $modelGenerator;
        $this->routeGenerator = $routeGenerator;
        $this->configManager = $configManager;
        $this->boostIntegration = $boostIntegration;
    }

    /**
     * Build a complete MCP server configuration.
     */
    public function build(array $options): array
    {
        $serverConfig = [
            'name' => $options['name'],
            'version' => $options['version'] ?? '1.0.0',
            'description' => $options['description'] ?? '',
            'tools' => [],
            'resources' => $options['resources'] ?? [],
            'prompts' => $options['prompts'] ?? [],
        ];

        // Generate from config file
        if (!empty($options['config_file'])) {
            $config = $this->configGenerator->generateFromFile($options['config_file']);
            $serverConfig = array_merge($serverConfig, $config);
        }

        // Generate from models
        if (!empty($options['models'])) {
            foreach ($options['models'] as $modelClass) {
                $modelTools = $this->modelGenerator->generateFromModel($modelClass);
                $serverConfig['tools'] = array_merge($serverConfig['tools'], array_values($modelTools));
            }
        }

        // Generate from routes
        if (!empty($options['include_routes'])) {
            $routeTools = $this->routeGenerator->generateFromRoutes();
            $serverConfig['tools'] = array_merge($serverConfig['tools'], $routeTools);
        }

        // Add manually defined tools
        if (!empty($options['manual_tools'])) {
            $serverConfig['tools'] = array_merge($serverConfig['tools'], $options['manual_tools']);
        }

        // Integrate Laravel Boost tools if requested (auto-installs if needed)
        if (!empty($options['include_boost'])) {
            $serverConfig = $this->boostIntegration->mergeBoostTools(
                $serverConfig,
                true,  // autoInstall
                null   // outputCallback
            );
        }

        return $serverConfig;
    }

    /**
     * Save the built server configuration.
     */
    public function save(string $serverName, array $serverConfig): bool
    {
        return $this->configManager->saveConfig($serverName, $serverConfig);
    }
}

