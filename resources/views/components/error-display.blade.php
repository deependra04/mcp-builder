@props(['error', 'title' => null, 'showDetails' => false])

@php
    $error = is_array($error) ? $error : ['message' => $error];
    $icon = $error['icon'] ?? '❌';
    $color = $error['color'] ?? 'danger';
    $code = $error['code'] ?? null;
    $suggestions = $error['suggestions'] ?? [];
    $title = $title ?? ($error['user_message'] ?? $error['message'] ?? 'An error occurred');
@endphp

<div class="card border-{{ $color }}">
    <div class="card-header bg-{{ $color }} text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $icon }} {{ $title }}</strong>
            @if($code)
                <small class="ms-2 opacity-75">[{{ $code }}]</small>
            @endif
        </div>
        @if($showDetails && isset($error['details']))
            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#errorDetails">
                Details
            </button>
        @endif
    </div>
    <div class="card-body">
        <p class="mb-3">{{ $error['user_message'] ?? $error['message'] ?? 'An unexpected error occurred.' }}</p>

        @if(!empty($suggestions))
            <div class="alert alert-light">
                <strong>💡 Suggestions:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($suggestions as $suggestion)
                        <li>{{ $suggestion }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($showDetails && isset($error['details']))
            <div class="collapse mt-3" id="errorDetails">
                <div class="card card-body bg-light">
                    <pre class="mb-0">{{ json_encode($error['details'], JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ url()->previous() ?? route('mcp-builder.dashboard') }}" class="btn btn-outline-secondary">
                ← Go Back
            </a>
            @if(isset($error['documentation']))
                <a href="{{ $error['documentation'] }}" target="_blank" class="btn btn-outline-primary">
                    📖 Documentation
                </a>
            @endif
        </div>
    </div>
</div>

