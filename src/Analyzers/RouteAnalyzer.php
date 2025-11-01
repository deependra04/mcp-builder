<?php

namespace Laravel\McpBuilder\Analyzers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RouteInstance;
use ReflectionMethod;

class RouteAnalyzer
{
    /**
     * Analyze all registered routes and extract information for MCP tool generation.
     */
    public function analyze(): array
    {
        $routes = Route::getRoutes();
        $analyzed = [];

        foreach ($routes as $route) {
            if (!$this->shouldIncludeRoute($route)) {
                continue;
            }

            $analyzed[] = $this->analyzeRoute($route);
        }

        return $analyzed;
    }

    /**
     * Analyze a specific route.
     */
    public function analyzeRoute(RouteInstance $route): array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;

        $info = [
            'uri' => $route->uri(),
            'methods' => array_diff($route->methods(), ['HEAD', 'OPTIONS']),
            'name' => $route->getName(),
        ];

        if ($controller) {
            [$controllerClass, $method] = explode('@', $controller);
            $info['controller'] = $controllerClass;
            $info['method'] = $method;
            $info['parameters'] = $this->analyzeControllerMethod($controllerClass, $method);
        }

        return $info;
    }

    /**
     * Check if a route should be included in MCP tool generation.
     */
    protected function shouldIncludeRoute(RouteInstance $route): bool
    {
        // Skip API resource routes that might be auto-generated
        $uri = $route->uri();
        
        // Include only routes that have a controller action
        return !empty($route->getAction()['controller']);
    }

    /**
     * Analyze controller method parameters.
     */
    protected function analyzeControllerMethod(string $controllerClass, string $method): array
    {
        if (!class_exists($controllerClass)) {
            return [];
        }

        try {
            $reflection = new \ReflectionClass($controllerClass);
            
            if (!$reflection->hasMethod($method)) {
                return [];
            }

            $methodReflection = $reflection->getMethod($method);
            $parameters = [];

            foreach ($methodReflection->getParameters() as $param) {
                $parameters[] = [
                    'name' => $param->getName(),
                    'type' => $param->getType()?->getName(),
                    'nullable' => $param->allowsNull(),
                    'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                ];
            }

            return $parameters;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate tool name from route.
     */
    public function generateToolName(RouteInstance $route): string
    {
        $name = $route->getName();
        
        if ($name) {
            return str_replace('.', '_', $name);
        }

        $uri = str_replace(['/', '{', '}'], ['_', '', ''], $route->uri());
        $methods = implode('_', $route->methods());
        
        return strtolower("{$methods}_{$uri}");
    }
}

