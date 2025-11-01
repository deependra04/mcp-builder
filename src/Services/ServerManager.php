<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Laravel\McpBuilder\Models\McpServer;
use Laravel\McpBuilder\Exceptions\ServerNotFoundException;

class ServerManager
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get all MCP servers with pagination support.
     */
    public function getAll(int $perPage = null): array
    {
        if ($perPage) {
            return McpServer::with('tools')
                ->latest()
                ->paginate($perPage)
                ->toArray();
        }

        return McpServer::with('tools')
            ->latest()
            ->get()
            ->toArray();
    }

    /**
     * Get a specific MCP server.
     */
    public function get(int $id): ?McpServer
    {
        $server = McpServer::with('tools')->find($id);
        
        if (!$server) {
            Log::debug('MCP Builder: Server not found', ['id' => $id]);
        }
        
        return $server;
    }

    /**
     * Get a specific MCP server or throw exception.
     */
    public function findOrFail(int $id): McpServer
    {
        $server = $this->get($id);
        
        if (!$server) {
            throw ServerNotFoundException::forId($id);
        }
        
        return $server;
    }

    /**
     * Create a new MCP server.
     */
    public function create(array $data): McpServer
    {
        try {
            $server = McpServer::create($data);
            Log::info('MCP Builder: Server created', ['id' => $server->id, 'name' => $server->name]);
            return $server;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Failed to create server', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an MCP server.
     */
    public function update(int $id, array $data): bool
    {
        $server = $this->get($id);
        
        if (!$server) {
            Log::warning('MCP Builder: Attempted to update non-existent server', ['id' => $id]);
            return false;
        }

        try {
            $updated = $server->update($data);
            
            if ($updated) {
                Log::info('MCP Builder: Server updated', ['id' => $id, 'name' => $server->name]);
            }
            
            return $updated;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Failed to update server', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete an MCP server.
     */
    public function delete(int $id): bool
    {
        $server = $this->get($id);
        
        if (!$server) {
            Log::warning('MCP Builder: Attempted to delete non-existent server', ['id' => $id]);
            return false;
        }

        try {
            $serverName = $server->name;
            $deleted = $server->delete();
            
            if ($deleted) {
                Log::info('MCP Builder: Server deleted', ['id' => $id, 'name' => $serverName]);
            }
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('MCP Builder: Failed to delete server', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get server status.
     */
    public function getStatus(int $id): string
    {
        $server = $this->get($id);
        
        if (!$server) {
            return 'not_found';
        }

        // Check if server configuration file exists
        $configPath = $this->getConfigPath($server->name);
        
        return File::exists($configPath) ? 'active' : 'inactive';
    }

    /**
     * Get configuration file path for a server.
     */
    protected function getConfigPath(string $serverName): string
    {
        $storagePath = config('mcp-builder.storage_path');
        
        return "{$storagePath}/{$serverName}.json";
    }
}

