<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Foundation\Application;
use Laravel\McpBuilder\Models\McpServer;
use Laravel\McpBuilder\Models\McpTool;

class DashboardService
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get dashboard statistics.
     */
    public function getStatistics(): array
    {
        return [
            'servers_count' => McpServer::count(),
            'tools_count' => McpTool::count(),
            'active_servers' => McpServer::where('status', 'active')->count(),
            'inactive_servers' => McpServer::where('status', 'inactive')->count(),
        ];
    }

    /**
     * Get recent servers.
     */
    public function getRecentServers(int $limit = 5): array
    {
        return McpServer::latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent tools.
     */
    public function getRecentTools(int $limit = 5): array
    {
        return McpTool::latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }
}

