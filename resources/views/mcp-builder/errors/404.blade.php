@extends('mcp-builder::layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="mcp-builder-error-page">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead text-muted mb-4">
                    The page you're looking for doesn't exist or has been moved.
                </p>
                
                <div class="suggestions mb-4">
                    <h5>💡 What you can do:</h5>
                    <ul class="list-unstyled text-start">
                        <li>✓ Check the URL for typos</li>
                        <li>✓ Navigate back to the <a href="{{ route('mcp-builder.dashboard') }}">dashboard</a></li>
                        <li>✓ Browse your <a href="{{ route('mcp-builder.servers.index') }}">servers</a></li>
                        <li>✓ View all <a href="{{ route('mcp-builder.tools.index') }}">tools</a></li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="{{ route('mcp-builder.dashboard') }}" class="btn btn-primary">
                        ← Go to Dashboard
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        ← Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

