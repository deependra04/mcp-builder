@extends('mcp-builder::layouts.app')

@section('title', 'Tool: ' . $tool->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $tool->name }}</h1>
        <p class="text-muted mb-0">{{ $tool->description ?? 'No description' }}</p>
    </div>
    <a href="{{ route('mcp-builder.tools.index') }}" class="btn btn-outline-secondary">Back to Tools</a>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Status</h6>
                <span class="badge bg-{{ $tool->is_active ? 'success' : 'secondary' }}">
                    {{ $tool->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Server</h6>
                @if($tool->server)
                    <a href="{{ route('mcp-builder.servers.show', $tool->server->id) }}">
                        {{ $tool->server->name }}
                    </a>
                @else
                    <span class="text-muted">No server</span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($tool->input_schema)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Input Schema</h5>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3 rounded"><code>{{ json_encode($tool->input_schema, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
@endif

@if($tool->handler_class)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Handler Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Class:</strong> {{ $tool->handler_class }}</p>
            @if($tool->handler_method)
                <p><strong>Method:</strong> {{ $tool->handler_method }}</p>
            @endif
        </div>
    </div>
@endif
@endsection

