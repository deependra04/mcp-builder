@extends('mcp-builder::layouts.app')

@section('title', 'MCP Builder Dashboard')

@section('content')
<div class="mcp-builder mcp-builder-dashboard container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>MCP Builder Dashboard</h1>
            <p class="text-muted">Manage your MCP servers and tools</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Servers</h5>
                    <h2 class="mb-0">{{ $statistics['servers_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Tools</h5>
                    <h2 class="mb-0">{{ $statistics['tools_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Active Servers</h5>
                    <h2 class="mb-0 text-success">{{ $statistics['active_servers'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Inactive Servers</h5>
                    <h2 class="mb-0 text-warning">{{ $statistics['inactive_servers'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Servers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Servers</h5>
                    <a href="{{ route('mcp-builder.servers.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(count($recentServers) > 0)
                        <div class="list-group">
                            @foreach($recentServers as $server)
                                <a href="{{ route('mcp-builder.servers.show', $server['id']) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $server['name'] }}</h6>
                                        <small class="badge bg-{{ $server['status'] === 'active' ? 'success' : 'warning' }}">
                                            {{ $server['status'] }}
                                        </small>
                                    </div>
                                    <p class="mb-1 text-muted">{{ $server['description'] ?? 'No description' }}</p>
                                    <small>Version {{ $server['version'] }}</small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No servers yet. <a href="{{ route('mcp-builder.servers.create') }}">Create one</a></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Tools -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Tools</h5>
                    <a href="{{ route('mcp-builder.tools.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(count($recentTools) > 0)
                        <div class="list-group">
                            @foreach($recentTools as $tool)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $tool['name'] }}</h6>
                                        @if($tool['is_active'])
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </div>
                                    <p class="mb-1 text-muted">{{ $tool['description'] ?? 'No description' }}</p>
                                    @if(isset($tool['server']))
                                        <small class="text-muted">Server: {{ $tool['server']['name'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No tools yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

