<?php

namespace Laravel\McpBuilder\Generators;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;
use Laravel\McpBuilder\Exceptions\ConfigFileException;

class ConfigGenerator
{

    /**
     * Generate MCP server configuration from a config file.
     */
    public function generateFromFile(string $configPath): array
    {
        if (!File::exists($configPath)) {
            throw ConfigFileException::notFound($configPath);
        }

        $extension = strtolower(File::extension($configPath));
        $content = File::get($configPath);

        return match ($extension) {
            'yaml', 'yml' => $this->parseYaml($content),
            'json' => $this->parseJson($content),
            default => throw ConfigFileException::invalidFormat($extension),
        };
    }

    /**
     * Parse YAML content.
     */
    protected function parseYaml(string $content): array
    {
        try {
            return Yaml::parse($content);
        } catch (\Exception $e) {
            throw ConfigFileException::parseError('YAML', $e->getMessage());
        }
    }

    /**
     * Parse JSON content.
     */
    protected function parseJson(string $content): array
    {
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConfigFileException::parseError('JSON', json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Validate MCP server configuration structure.
     */
    public function validateConfig(array $config): bool
    {
        $required = ['name', 'version', 'tools'];

        foreach ($required as $field) {
            if (!isset($config[$field])) {
                throw ConfigFileException::invalidStructure($field);
            }
        }

        if (!is_array($config['tools'])) {
            throw new ConfigFileException("Tools must be an array");
        }

        return true;
    }

    /**
     * Generate MCP server configuration from parsed config.
     */
    public function generateServerConfig(array $config): array
    {
        $this->validateConfig($config);

        return [
            'name' => $config['name'],
            'version' => $config['version'],
            'description' => $config['description'] ?? '',
            'tools' => $config['tools'],
            'resources' => $config['resources'] ?? [],
            'prompts' => $config['prompts'] ?? [],
            'created_at' => now()->toDateTimeString(),
        ];
    }
}

