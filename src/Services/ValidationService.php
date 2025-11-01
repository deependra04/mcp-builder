<?php

namespace Laravel\McpBuilder\Services;

class ValidationService
{
    /**
     * Validate MCP tool configuration.
     */
    public function validateTool(array $tool): array
    {
        $errors = [];

        // Required fields
        if (empty($tool['name'])) {
            $errors[] = 'Tool name is required';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $tool['name'])) {
            $errors[] = 'Tool name must contain only lowercase letters, numbers, and underscores';
        }

        if (empty($tool['description'])) {
            $errors[] = 'Tool description is required';
        }

        // Validate input schema
        if (isset($tool['inputSchema'])) {
            $schemaErrors = $this->validateInputSchema($tool['inputSchema']);
            $errors = array_merge($errors, $schemaErrors);
        }

        return $errors;
    }

    /**
     * Validate input schema structure.
     */
    protected function validateInputSchema(array $schema): array
    {
        $errors = [];

        if (!isset($schema['type'])) {
            $errors[] = 'Input schema must have a type';
        } elseif ($schema['type'] !== 'object') {
            $errors[] = 'Input schema type must be "object"';
        }

        if (isset($schema['properties']) && !is_array($schema['properties'])) {
            $errors[] = 'Input schema properties must be an array';
        }

        return $errors;
    }

    /**
     * Validate server configuration.
     */
    public function validateServerConfig(array $config): array
    {
        $errors = [];

        if (empty($config['name'])) {
            $errors[] = 'Server name is required';
        }

        if (empty($config['version'])) {
            $errors[] = 'Server version is required';
        } elseif (!preg_match('/^\d+\.\d+\.\d+$/', $config['version'])) {
            $errors[] = 'Server version must be in semver format (e.g., 1.0.0)';
        }

        if (isset($config['tools']) && !is_array($config['tools'])) {
            $errors[] = 'Tools must be an array';
        }

        // Validate each tool
        if (isset($config['tools']) && is_array($config['tools'])) {
            foreach ($config['tools'] as $index => $tool) {
                $toolErrors = $this->validateTool($tool);
                if (!empty($toolErrors)) {
                    $errors[] = "Tool at index {$index}: " . implode(', ', $toolErrors);
                }
            }
        }

        return $errors;
    }

    /**
     * Check if validation passes.
     */
    public function isValid(array $errors): bool
    {
        return empty($errors);
    }
}

