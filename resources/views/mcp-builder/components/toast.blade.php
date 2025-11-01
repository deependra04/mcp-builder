@props(['type' => 'info', 'title' => null, 'message', 'duration' => 5000, 'dismissible' => true])

@php
    $types = [
        'success' => ['bg-success', 'text-white', '✓'],
        'error' => ['bg-danger', 'text-white', '✗'],
        'warning' => ['bg-warning', 'text-dark', '⚠'],
        'info' => ['bg-info', 'text-white', 'ℹ'],
    ];
    
    $classes = $types[$type] ?? $types['info'];
@endphp

<div class="mcp-toast toast align-items-center {{ implode(' ', $classes) }} border-0" 
     role="alert" 
     aria-live="assertive" 
     aria-atomic="true"
     data-bs-autohide="{{ $dismissible ? 'true' : 'false' }}"
     data-bs-delay="{{ $duration }}">
    <div class="d-flex">
        <div class="toast-body">
            @if($title)
                <strong>{{ $title }}</strong><br>
            @endif
            {{ $message }}
        </div>
        @if($dismissible)
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        @endif
    </div>
</div>

