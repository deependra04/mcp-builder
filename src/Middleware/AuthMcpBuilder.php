<?php

namespace Laravel\McpBuilder\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMcpBuilder
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if authentication is enabled in config
        $authEnabled = config('mcp-builder.dashboard.auth.enabled', false);

        if (!$authEnabled) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Check for permission if configured
        $permission = config('mcp-builder.dashboard.auth.permission');
        if ($permission && !auth()->user()->can($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            abort(403, 'Access denied');
        }

        return $next($request);
    }
}

