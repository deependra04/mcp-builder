<?php

namespace Laravel\McpBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\McpBuilder\Models\McpServer;
use Laravel\McpBuilder\Services\ServerManager;

class ServerController extends Controller
{
    protected ServerManager $serverManager;

    public function __construct(ServerManager $serverManager)
    {
        $this->serverManager = $serverManager;
    }

    /**
     * Display a listing of the servers.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $servers = McpServer::with('tools')
            ->latest()
            ->paginate($perPage);
            
        return view('mcp-builder::dashboard.servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new server.
     */
    public function create()
    {
        return view('mcp-builder::dashboard.servers.create');
    }

    /**
     * Store a newly created server.
     */
    public function store(Request $request)
    {
        return $this->handle(function() use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:mcp_servers,name',
                'version' => 'required|string',
                'description' => 'nullable|string',
                'config' => 'nullable|array',
            ]);

            $server = $this->serverManager->create($validated);

            return $this->successResponse(
                'Server created successfully',
                $server,
                route('mcp-builder.servers.show', $server->id)
            );
        }, 'Server created successfully', route('mcp-builder.servers.index'));
    }

    /**
     * Display the specified server.
     */
    public function show($id)
    {
        $server = $this->serverManager->get($id);

        if (!$server) {
            return redirect()
                ->route('mcp-builder.servers.index')
                ->with('error', 'Server not found');
        }

        return view('mcp-builder::dashboard.servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified server.
     */
    public function edit($id)
    {
        $server = $this->serverManager->get($id);

        if (!$server) {
            return redirect()
                ->route('mcp-builder.servers.index')
                ->with('error', 'Server not found');
        }

        return view('mcp-builder::dashboard.servers.edit', compact('server'));
    }

    /**
     * Update the specified server.
     */
    public function update(Request $request, $id)
    {
        return $this->handle(function() use ($request, $id) {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:mcp_servers,name,' . $id,
                'version' => 'required|string',
                'description' => 'nullable|string',
                'config' => 'nullable|array',
            ]);

            $updated = $this->serverManager->update($id, $validated);

            if (!$updated) {
                throw new \RuntimeException('Failed to update server');
            }

            return $this->successResponse(
                'Server updated successfully',
                null,
                route('mcp-builder.servers.show', $id)
            );
        }, 'Server updated successfully', route('mcp-builder.servers.index'));
    }

    /**
     * Remove the specified server.
     */
    public function destroy($id)
    {
        return $this->handle(function() use ($id) {
            $deleted = $this->serverManager->delete($id);

            if (!$deleted) {
                throw new \RuntimeException('Failed to delete server');
            }

            return $this->successResponse(
                'Server deleted successfully',
                null,
                route('mcp-builder.servers.index')
            );
        }, 'Server deleted successfully', route('mcp-builder.servers.index'));
    }
}

