@extends('mcp-builder::layouts.app')

@section('title', 'Server: ' . $server->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $server->name }}</h1>
        <p class="text-muted mb-0">{{ $server->description ?? 'No description' }}</p>
    </div>
    <div>
        <a href="{{ route('mcp-builder.servers.edit', $server->id) }}" class="btn btn-outline-secondary">Edit</a>
        <form action="{{ route('mcp-builder.servers.destroy', $server->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Status</h6>
                <span class="badge bg-{{ $server->status === 'active' ? 'success' : 'warning' }}">
                    {{ $server->status }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Version</h6>
                <p class="mb-0">{{ $server->version }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Tools</h6>
                <p class="mb-0">{{ $server->tools->count() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Server Tools</h5>
    </div>
    <div class="card-body">
        @if($server->tools->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($server->tools as $tool)
                        <tr>
                            <td>{{ $tool->name }}</td>
                            <td>{{ $tool->description ?? 'No description' }}</td>
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
            <p class="text-muted mb-0">No tools found for this server.</p>
        @endif
    </div>
</div>
@endsection

