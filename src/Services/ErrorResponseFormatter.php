<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class ErrorResponseFormatter
{
    protected ErrorHandlerService $errorHandler;

    public function __construct(ErrorHandlerService $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Format error for API response
     */
    public function formatApiError(\Exception $exception, int $statusCode = 500): JsonResponse
    {
        $errorInfo = $this->errorHandler->handleException($exception);

        $response = [
            'error' => [
                'message' => $errorInfo['user_message'] ?? $errorInfo['message'],
                'code' => $errorInfo['code'],
                'category' => $errorInfo['category'],
                'suggestions' => $errorInfo['suggestions'] ?? [],
            ],
        ];

        // Include validation errors if present
        if (isset($errorInfo['errors'])) {
            $response['error']['details'] = $errorInfo['errors'];
        }

        // Include documentation link
        $response['error']['documentation'] = $this->getDocumentationUrl($errorInfo['code']);

        // Include technical details only in debug mode
        if (App::environment('local', 'testing')) {
            $response['error']['debug'] = [
                'message' => $errorInfo['message'],
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Format error for web response
     */
    public function formatWebError(\Exception $exception, Request $request = null): array
    {
        $errorInfo = $this->errorHandler->handleException($exception);

        return [
            'error' => $errorInfo,
            'icon' => $this->errorHandler->getErrorIcon($errorInfo['category']),
            'color' => $this->errorHandler->getErrorColor($errorInfo['severity']),
            'show_details' => App::environment('local', 'testing'),
        ];
    }

    /**
     * Format error for CLI output
     */
    public function formatCliError(\Exception $exception): array
    {
        $errorInfo = $this->errorHandler->handleException($exception);

        return [
            'message' => $errorInfo['user_message'] ?? $errorInfo['message'],
            'code' => $errorInfo['code'],
            'category' => $errorInfo['category'],
            'suggestions' => $errorInfo['suggestions'] ?? [],
            'icon' => $this->getCliIcon($errorInfo['category']),
        ];
    }

    /**
     * Format validation errors for response
     */
    public function formatValidationErrors(array $errors): JsonResponse|array
    {
        $formatted = [
            'error' => [
                'message' => 'Validation failed',
                'code' => 'MCP-002',
                'category' => 'validation',
                'details' => $errors,
                'suggestions' => [
                    'Review all highlighted fields',
                    'Check required fields are filled',
                    'Verify field formats',
                ],
            ],
        ];

        return response()->json($formatted, 422);
    }

    /**
     * Get CLI icon based on category
     */
    protected function getCliIcon(string $category): string
    {
        return match ($category) {
            ErrorHandlerService::CATEGORY_VALIDATION => '⚠',
            ErrorHandlerService::CATEGORY_NOT_FOUND => '❓',
            ErrorHandlerService::CATEGORY_PERMISSION => '🔒',
            ErrorHandlerService::CATEGORY_SERVER => '⚠',
            default => '✗',
        };
    }

    /**
     * Get documentation URL for error code
     */
    protected function getDocumentationUrl(string $code): ?string
    {
        // In a real implementation, this would point to actual documentation
        return config('mcp-builder.documentation_url') . "/errors/{$code}";
    }
}

