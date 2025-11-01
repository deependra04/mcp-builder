<?php

namespace Laravel\McpBuilder\Commands\Concerns;

use Exception;
use Laravel\McpBuilder\Services\ErrorHandlerService;
use Laravel\McpBuilder\Services\ErrorResponseFormatter;

trait HandlesErrors
{
    protected ErrorHandlerService $errorHandler;
    protected ErrorResponseFormatter $errorFormatter;

    /**
     * Initialize error handling
     */
    protected function initializeErrorHandling(): void
    {
        $this->errorHandler = app(ErrorHandlerService::class);
        $this->errorFormatter = app(ErrorResponseFormatter::class);
    }

    /**
     * Display error in a user-friendly format
     */
    protected function displayError(Exception $exception, bool $verbose = false): void
    {
        $errorInfo = $this->errorFormatter->formatCliError($exception);

        // Display error header
        $this->line('');
        $this->error("{$errorInfo['icon']} Error [{$errorInfo['code']}]: {$errorInfo['message']}");
        $this->line('');

        // Display category
        $this->line("Category: " . ucfirst(str_replace('_', ' ', $errorInfo['category'])));
        $this->line('');

        // Display suggestions
        if (!empty($errorInfo['suggestions'])) {
            $this->info('💡 Suggestions:');
            foreach ($errorInfo['suggestions'] as $index => $suggestion) {
                $this->line("   " . ($index + 1) . ". {$suggestion}");
            }
            $this->line('');
        }

        // Display verbose details if requested
        if ($verbose || $this->option('verbose')) {
            $this->line('Technical Details:');
            $this->line("   Exception: " . get_class($exception));
            $this->line("   Message: " . $exception->getMessage());
            $this->line("   File: " . $exception->getFile());
            $this->line("   Line: " . $exception->getLine());
            $this->line('');
        }
    }

    /**
     * Execute command with error handling
     */
    protected function handleWithErrors(callable $action): int
    {
        try {
            $this->initializeErrorHandling();
            $result = $action();

            if ($result === false) {
                $this->warn('Operation completed with warnings');
                return self::FAILURE;
            }

            return $result ?? self::SUCCESS;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->displayValidationErrors($e);
            return self::FAILURE;
        } catch (Exception $e) {
            $this->displayError($e, $this->option('verbose'));
            return self::FAILURE;
        }
    }

    /**
     * Display validation errors
     */
    protected function displayValidationErrors(\Illuminate\Validation\ValidationException $e): void
    {
        $errors = $e->errors();

        $this->line('');
        $this->error('⚠ Validation Failed');
        $this->line('');

        $this->table(
            ['Field', 'Error'],
            collect($errors)->map(function ($messages, $field) {
                return [$field, implode(', ', $messages)];
            })->toArray()
        );

        $this->line('');
        $this->info('💡 Suggestions:');
        $this->line('   1. Check all required fields are filled');
        $this->line('   2. Verify field formats match requirements');
        $this->line('   3. Review field validation rules');
        $this->line('');
    }

    /**
     * Display formatted error table
     */
    protected function displayErrorTable(array $errors, string $title = 'Errors'): void
    {
        if (empty($errors)) {
            return;
        }

        $this->line('');
        $this->error($title);
        $this->line('');

        $rows = [];
        foreach ($errors as $index => $error) {
            $rows[] = [
                $index + 1,
                is_array($error) ? ($error['message'] ?? $error) : $error,
            ];
        }

        $this->table(['#', 'Error'], $rows);
        $this->line('');
    }
}

