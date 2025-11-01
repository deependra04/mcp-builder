<?php

namespace Laravel\McpBuilder\Services;

use Exception;

class ErrorRecoveryService
{
    /**
     * Get recovery suggestions based on error context
     */
    public function getSuggestions(Exception $exception, array $context = []): array
    {
        $baseSuggestions = $this->getBaseSuggestions($exception);

        // Add context-specific suggestions
        if (isset($context['action'])) {
            $contextual = $this->getContextualSuggestions($context['action'], $exception);
            return array_merge($baseSuggestions, $contextual);
        }

        return $baseSuggestions;
    }

    /**
     * Get base suggestions for exception type
     */
    protected function getBaseSuggestions(Exception $exception): array
    {
        $class = get_class($exception);

        // File-related errors
        if (str_contains($exception->getMessage(), 'file') || 
            str_contains($exception->getMessage(), 'File')) {
            return [
                'Verify the file path is correct',
                'Check file permissions',
                'Ensure the directory exists',
                'Try regenerating the file',
            ];
        }

        // Database-related errors
        if (str_contains($exception->getMessage(), 'SQL') || 
            str_contains($exception->getMessage(), 'database')) {
            return [
                'Check database connection',
                'Verify table exists',
                'Run migrations: php artisan migrate',
                'Check database permissions',
            ];
        }

        // Configuration errors
        if (str_contains($exception->getMessage(), 'config') || 
            str_contains($exception->getMessage(), 'Config')) {
            return [
                'Verify configuration file exists',
                'Check configuration format',
                'Validate configuration structure',
                'Run: php artisan config:clear',
            ];
        }

        // Permission errors
        if (str_contains($exception->getMessage(), 'permission') || 
            str_contains($exception->getMessage(), 'Permission')) {
            return [
                'Check file/directory permissions',
                'Verify user has required access',
                'Contact administrator',
            ];
        }

        // Generic suggestions
        return [
            'Try the operation again',
            'Refresh the page',
            'Check the error logs',
            'Contact support if issue persists',
        ];
    }

    /**
     * Get contextual suggestions based on action
     */
    protected function getContextualSuggestions(string $action, Exception $exception): array
    {
        return match ($action) {
            'create_server' => [
                'Verify server name is unique',
                'Check all required fields',
                'Validate server configuration',
            ],
            'generate_tool' => [
                'Verify tool name is valid',
                'Check namespace exists',
                'Validate tool schema',
                'Review tool generation logs',
            ],
            'save_config' => [
                'Verify storage path is writable',
                'Check configuration structure',
                'Validate JSON/YAML format',
            ],
            'delete_server' => [
                'Ensure server is not in use',
                'Check dependencies',
                'Verify you have permission',
            ],
            default => [],
        };
    }

    /**
     * Get troubleshooting guide link
     */
    public function getTroubleshootingLink(string $errorCode): ?string
    {
        return config('mcp-builder.documentation_url') . "/troubleshooting/{$errorCode}";
    }
}

