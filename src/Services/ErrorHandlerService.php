<?php

namespace Laravel\McpBuilder\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\McpBuilder\Exceptions\McpBuilderException;
use Laravel\McpBuilder\Exceptions\ServerNotFoundException;
use Laravel\McpBuilder\Exceptions\ConfigFileException;
use Laravel\McpBuilder\Exceptions\ModelNotFoundException;
use Laravel\McpBuilder\Exceptions\ToolGenerationException;

class ErrorHandlerService
{
    /**
     * Error severity levels
     */
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Error categories
     */
    const CATEGORY_VALIDATION = 'validation';
    const CATEGORY_NOT_FOUND = 'not_found';
    const CATEGORY_SERVER = 'server';
    const CATEGORY_PERMISSION = 'permission';
    const CATEGORY_BUSINESS_LOGIC = 'business_logic';

    /**
     * Handle an exception and return user-friendly information
     */
    public function handleException(Exception $exception): array
    {
        // Log the exception
        $this->logException($exception);

        // Get error information based on exception type
        return $this->getErrorInfo($exception);
    }

    /**
     * Get error information array
     */
    protected function getErrorInfo(Exception $exception): array
    {
        // Handle custom exceptions
        if ($exception instanceof ServerNotFoundException) {
            return $this->getNotFoundError('Server', $exception->getMessage());
        }

        if ($exception instanceof ConfigFileException) {
            return $this->getConfigError($exception->getMessage());
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->getNotFoundError('Model', $exception->getMessage());
        }

        if ($exception instanceof ToolGenerationException) {
            return $this->getToolGenerationError($exception->getMessage());
        }

        if ($exception instanceof McpBuilderException) {
            return $this->getGenericError($exception->getMessage());
        }

        // Handle validation exceptions
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $this->getValidationError($exception);
        }

        // Handle authentication exceptions
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->getPermissionError('Authentication required');
        }

        // Handle authorization exceptions
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->getPermissionError('Permission denied');
        }

        // Handle model not found exceptions
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->getNotFoundError('Resource', 'The requested resource was not found');
        }

        // Default generic error
        return $this->getGenericError($this->getUserFriendlyMessage($exception));
    }

    /**
     * Get validation error information
     */
    protected function getValidationError(\Illuminate\Validation\ValidationException $exception): array
    {
        return [
            'message' => 'Validation failed. Please check your input.',
            'user_message' => 'Please correct the errors below and try again.',
            'code' => 'MCP-002',
            'category' => self::CATEGORY_VALIDATION,
            'severity' => self::SEVERITY_WARNING,
            'errors' => $exception->errors(),
            'suggestions' => [
                'Check all required fields are filled',
                'Verify field formats match requirements',
                'Review field validation rules',
            ],
        ];
    }

    /**
     * Get not found error information
     */
    protected function getNotFoundError(string $type, string $message): array
    {
        return [
            'message' => $message,
            'user_message' => "{$type} not found. It may have been deleted or moved.",
            'code' => 'MCP-001',
            'category' => self::CATEGORY_NOT_FOUND,
            'severity' => self::SEVERITY_WARNING,
            'suggestions' => [
                'Verify the name or ID is correct',
                'Check if it was recently deleted',
                'Navigate back to the list to find it',
            ],
        ];
    }

    /**
     * Get config error information
     */
    protected function getConfigError(string $message): array
    {
        return [
            'message' => $message,
            'user_message' => 'Configuration error. Please check your configuration file.',
            'code' => 'MCP-003',
            'category' => self::CATEGORY_SERVER,
            'severity' => self::SEVERITY_ERROR,
            'suggestions' => [
                'Verify the configuration file exists',
                'Check file format (YAML or JSON)',
                'Validate configuration structure',
                'Run: php artisan mcp:validate-config',
            ],
        ];
    }

    /**
     * Get tool generation error information
     */
    protected function getToolGenerationError(string $message): array
    {
        return [
            'message' => $message,
            'user_message' => 'Failed to generate tool. Please check the tool configuration.',
            'code' => 'MCP-005',
            'category' => self::CATEGORY_BUSINESS_LOGIC,
            'severity' => self::SEVERITY_ERROR,
            'suggestions' => [
                'Verify tool name is valid (alphanumeric and underscores only)',
                'Check namespace path exists',
                'Ensure file permissions are correct',
                'Review tool generation logs',
            ],
        ];
    }

    /**
     * Get permission error information
     */
    protected function getPermissionError(string $message): array
    {
        return [
            'message' => $message,
            'user_message' => 'You do not have permission to perform this action.',
            'code' => 'MCP-004',
            'category' => self::CATEGORY_PERMISSION,
            'severity' => self::SEVERITY_ERROR,
            'suggestions' => [
                'Contact your administrator for access',
                'Verify you are logged in with the correct account',
                'Check your role and permissions',
            ],
        ];
    }

    /**
     * Get generic error information
     */
    protected function getGenericError(string $message): array
    {
        return [
            'message' => $message,
            'user_message' => 'An unexpected error occurred. Please try again or contact support.',
            'code' => 'MCP-999',
            'category' => self::CATEGORY_SERVER,
            'severity' => self::SEVERITY_ERROR,
            'suggestions' => [
                'Try the operation again',
                'Refresh the page',
                'Check the error logs',
                'Contact support if the issue persists',
            ],
        ];
    }

    /**
     * Get user-friendly error message
     */
    protected function getUserFriendlyMessage(Exception $exception): string
    {
        // Remove technical details from message
        $message = $exception->getMessage();

        // Common technical patterns to remove or replace
        $patterns = [
            '/SQLSTATE\[.*?\]/' => 'Database error',
            '/Class .*? not found/' => 'Class not found',
            '/Call to undefined method/' => 'Method not found',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $message)) {
                return $replacement;
            }
        }

        // If message is too technical, provide generic message
        if (strlen($message) > 200 || str_contains($message, 'Stack trace')) {
            return 'An unexpected error occurred';
        }

        return $message;
    }

    /**
     * Log exception with context
     */
    protected function logException(Exception $exception): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        if ($exception->getSeverity() >= E_ERROR) {
            Log::error('MCP Builder: Critical exception', $context);
        } else {
            Log::warning('MCP Builder: Exception occurred', $context);
        }
    }

    /**
     * Get error icon based on category
     */
    public function getErrorIcon(string $category): string
    {
        return match ($category) {
            self::CATEGORY_VALIDATION => '⚠️',
            self::CATEGORY_NOT_FOUND => '🔍',
            self::CATEGORY_PERMISSION => '🔒',
            self::CATEGORY_SERVER => '⚠️',
            default => '❌',
        };
    }

    /**
     * Get error color based on severity
     */
    public function getErrorColor(string $severity): string
    {
        return match ($severity) {
            self::SEVERITY_INFO => 'info',
            self::SEVERITY_WARNING => 'warning',
            self::SEVERITY_ERROR => 'danger',
            self::SEVERITY_CRITICAL => 'danger',
            default => 'secondary',
        };
    }
}

