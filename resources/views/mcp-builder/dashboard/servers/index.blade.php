@extends('mcp-builder::layouts.app')

@section('title', 'MCP Servers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>MCP Servers</h1>
    <a href="{{ route('mcp-builder.servers.create') }}" class="btn btn-primary">Create Server</a>
</div>

<div class="card">
    <div class="card-body">
        @if($servers->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Tools</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servers as $server)
                        <tr>
                            <td>
                                <a href="{{ route('mcp-builder.servers.show', $server->id) }}">
                                    {{ $server->name }}
                                </a>
                            </td>
                            <td>{{ $server->version }}</td>
                            <td>
                                <span class="badge bg-{{ $server->status === 'active' ? 'success' : 'warning' }}">
                                    {{ $server->status }}
                                </span>
                            </td>
                            <td>{{ $server->tools_count ?? $server->tools->count() }}</td>
                            <td>{{ $server->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('mcp-builder.servers.show', $server->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('mcp-builder.servers.edit', $server->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('mcp-builder.servers.destroy', $server->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">No servers found. <a href="{{ route('mcp-builder.servers.create') }}">Create your first server</a></p>
        @endif
    </div>
</div>
@endsection

