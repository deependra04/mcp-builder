<?php

namespace Laravel\McpBuilder\Prompts;

use Illuminate\Support\Facades\Validator;

class SetupPrompts
{
    /**
     * Prompt for server name.
     */
    public function promptServerName(?string $default = null): string
    {
        $name = $default ?? 'my-mcp-server';

        return $this->validateInput(
            $name,
            'Server name',
            ['required', 'string', 'max:255', 'regex:/^[a-z0-9-_]+$/i'],
            'Server name must contain only letters, numbers, hyphens, and underscores'
        );
    }

    /**
     * Prompt for server version.
     */
    public function promptServerVersion(?string $default = null): string
    {
        $version = $default ?? '1.0.0';

        return $this->validateInput(
            $version,
            'Server version',
            ['required', 'string', 'regex:/^\d+\.\d+\.\d+$/'],
            'Version must be in semver format (e.g., 1.0.0)'
        );
    }

    /**
     * Prompt for server description.
     */
    public function promptServerDescription(?string $default = null): string
    {
        return $default ?? $this->ask('Server description (optional)', '');
    }

    /**
     * Prompt for generation method.
     */
    public function promptGenerationMethod(): string
    {
        $options = [
            'config' => 'From configuration file (YAML/JSON)',
            'model' => 'From Eloquent model(s)',
            'routes' => 'From Laravel routes',
            'manual' => 'Manual setup',
            'mixed' => 'Mixed (combine multiple methods)',
        ];

        return $this->choice('How would you like to generate MCP tools?', $options, 'config');
    }

    /**
     * Prompt for model class.
     */
    public function promptModelClass(): ?string
    {
        return $this->ask('Enter model class name (e.g., App\\Models\\User)', null);
    }

    /**
     * Prompt for config file path.
     */
    public function promptConfigFile(): ?string
    {
        return $this->ask('Enter path to configuration file (YAML or JSON)', null);
    }

    /**
     * Prompt for including routes.
     */
    public function promptIncludeRoutes(): bool
    {
        return $this->confirm('Would you like to generate tools from Laravel routes?', false);
    }

    /**
     * Prompt for route filter.
     */
    public function promptRouteFilter(): ?string
    {
        return $this->ask('Filter routes by prefix or pattern (optional)', null);
    }

    /**
     * Ask a question and return the answer.
     */
    protected function ask(string $question, ?string $default = null): ?string
    {
        // This will be handled by the command class
        return $default;
    }

    /**
     * Choose from options.
     */
    protected function choice(string $question, array $options, string $default = null): string
    {
        // This will be handled by the command class
        return $default ?? array_key_first($options);
    }

    /**
     * Confirm a question.
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        // This will be handled by the command class
        return $default;
    }

    /**
     * Validate input.
     */
    protected function validateInput(string $value, string $field, array $rules, ?string $message = null): string
    {
        $validator = Validator::make([$field => $value], [$field => $rules]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                $message ?? $validator->errors()->first($field)
            );
        }

        return $value;
    }
}

