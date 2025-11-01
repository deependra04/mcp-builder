<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MCP Builder')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/mcp-builder/css/mcp-builder.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/mcp-builder/css/mcp-builder-errors.css') }}">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('mcp-builder.dashboard') }}">MCP Builder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mcp-builder.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mcp-builder.servers.index') }}">Servers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mcp-builder.tools.index') }}">Tools</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid mt-4">
        {{-- Toast Container --}}
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="toastContainer"></div>
        </div>

        {{-- Success Toast --}}
        @if(session('success'))
            @php
                $toastId = 'toast-' . uniqid();
            @endphp
            <div id="{{ $toastId }}" class="mcp-toast toast align-items-center bg-success text-white border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true"
                 data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>✓</strong> {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toastEl = document.getElementById('{{ $toastId }}');
                    if (toastEl && window.bootstrap) {
                        const toast = new bootstrap.Toast(toastEl);
                        toast.show();
                    }
                });
            </script>
        @endif

        {{-- Error Toast with Suggestions --}}
        @if(session('error'))
            @php
                $errorToastId = 'error-toast-' . uniqid();
            @endphp
            <div id="{{ $errorToastId }}" class="mcp-toast toast align-items-center bg-danger text-white border-0" 
                 role="alert" aria-live="assertive" aria-atomic="true"
                 data-bs-autohide="true" data-bs-delay="8000">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>✗ Error</strong><br>{{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toastEl = document.getElementById('{{ $errorToastId }}');
                    if (toastEl && window.bootstrap) {
                        const toast = new bootstrap.Toast(toastEl);
                        toast.show();
                    }
                });
            </script>
            @if(session('error_suggestions'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>💡 Suggestions:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach(session('error_suggestions') as $suggestion)
                            <li>{{ $suggestion }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endif

        {{-- Validation Errors --}}
        @if(isset($errors) && $errors->any())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">⚠️ Please correct the following errors:</h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('vendor/mcp-builder/js/mcp-builder.js') }}"></script>
    <script src="{{ asset('vendor/mcp-builder/js/mcp-builder-toasts.js') }}"></script>
    @stack('scripts')
</body>
</html>

