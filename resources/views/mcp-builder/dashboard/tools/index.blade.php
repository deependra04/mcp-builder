@extends('mcp-builder::layouts.app')

@section('title', 'MCP Tools')

@section('content')
<h1>MCP Tools</h1>

<div class="card">
    <div class="card-body">
        @if($tools->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Server</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tools as $tool)
                        <tr>
                            <td>{{ $tool->name }}</td>
                            <td>{{ $tool->description ?? 'No description' }}</td>
                            <td>
                                @if($tool->server)
                                    <a href="{{ route('mcp-builder.servers.show', $tool->server->id) }}">
                                        {{ $tool->server->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No server</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tool->is_active ? 'success' : 'secondary' }}">
                                    {{ $tool->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('mcp-builder.tools.show', $tool->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">No tools found.</p>
        @endif
    </div>
</div>
@endsection

