@extends('mcp-builder::layouts.app')

@section('title', '403 - Access Denied')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <h1 class="display-1 fw-bold text-warning">403</h1>
                <h2 class="mb-4">Access Denied</h2>
                <p class="lead text-muted mb-4">
                    You don't have permission to access this resource.
                </p>
                
                <div class="suggestions mb-4">
                    <h5>💡 What you can do:</h5>
                    <ul class="list-unstyled text-start">
                        <li>✓ Verify you're logged in with the correct account</li>
                        <li>✓ Check your role and permissions</li>
                        <li>✓ Contact your administrator for access</li>
                        <li>✓ Return to the <a href="{{ route('mcp-builder.dashboard') }}">dashboard</a></li>
                    </ul>
                </div>

                <div class="alert alert-warning mt-4">
                    <strong>Error Code:</strong> MCP-004<br>
                    <small>Permission denied. Please contact your administrator if you believe this is an error.</small>
                </div>

                <div class="mt-4">
                    <a href="{{ route('mcp-builder.dashboard') }}" class="btn btn-primary">
                        ← Go to Dashboard
                    </a>
                    @if(auth()->check())
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            ← Go Back
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            🔐 Login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

