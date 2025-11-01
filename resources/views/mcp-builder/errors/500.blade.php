@extends('mcp-builder::layouts.app')

@section('title', '500 - Server Error')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="mcp-builder-error-page">
                <h1 class="display-1 fw-bold text-danger">500</h1>
                <h2 class="mb-4">Server Error</h2>
                <p class="lead text-muted mb-4">
                    Something went wrong on our end. We're working to fix it.
                </p>
                
                <div class="suggestions mb-4">
                    <h5>💡 What you can do:</h5>
                    <ul class="list-unstyled text-start">
                        <li>✓ Try refreshing the page</li>
                        <li>✓ Check if the issue persists</li>
                        <li>✓ Review the error logs</li>
                        <li>✓ Contact support if the problem continues</li>
                    </ul>
                </div>

                <div class="alert alert-info mt-4">
                    <strong>Error Code:</strong> MCP-500<br>
                    <small>If this problem persists, please contact support with this error code.</small>
                </div>

                <div class="mt-4">
                    <a href="{{ route('mcp-builder.dashboard') }}" class="btn btn-primary">
                        ← Go to Dashboard
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-outline-primary">
                        ↻ Refresh Page
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

