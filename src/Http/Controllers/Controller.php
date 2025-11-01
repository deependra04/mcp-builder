<?php

namespace Laravel\McpBuilder\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\McpBuilder\Services\ErrorHandlerService;
use Laravel\McpBuilder\Services\ErrorResponseFormatter;
use Exception;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected ErrorHandlerService $errorHandler;
    protected ErrorResponseFormatter $errorFormatter;

    public function __construct()
    {
        $this->errorHandler = app(ErrorHandlerService::class);
        $this->errorFormatter = app(ErrorResponseFormatter::class);
    }

    /**
     * Execute an action with error handling
     */
    protected function handle(callable $action, string $successMessage = null, string $errorRoute = null)
    {
        try {
            $result = $action();

            if ($successMessage && request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $result,
                ]);
            }

            return $result;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (Exception $e) {
            return $this->handleException($e, $errorRoute);
        }
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(\Illuminate\Validation\ValidationException $e)
    {
        if (request()->wantsJson()) {
            return $this->errorFormatter->formatValidationErrors($e->errors());
        }

        return redirect()->back()
            ->withInput()
            ->withErrors($e->errors())
            ->with('error_message', 'Please correct the errors and try again.');
    }

    /**
     * Handle general exceptions
     */
    protected function handleException(Exception $e, ?string $redirectRoute = null): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $errorInfo = $this->errorHandler->handleException($e);

        if (request()->wantsJson()) {
            return $this->errorFormatter->formatApiError($e, 500);
        }

        $route = $redirectRoute ?? (url()->previous() ?: route('mcp-builder.dashboard'));

        return redirect($route)
            ->with('error', $errorInfo['user_message'] ?? $errorInfo['message'])
            ->with('error_code', $errorInfo['code'])
            ->with('error_suggestions', $errorInfo['suggestions'] ?? []);
    }

    /**
     * Return success response
     */
    protected function successResponse(string $message, $data = null, string $route = null, int $statusCode = 200)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], $statusCode);
        }

        if ($route) {
            return redirect($route)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $statusCode = 400, string $route = null)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $message,
                    'code' => 'MCP-999',
                ],
            ], $statusCode);
        }

        if ($route) {
            return redirect($route)->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }
}
