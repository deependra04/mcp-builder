<?php

namespace Laravel\McpBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\McpBuilder\Services\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $statistics = $this->dashboardService->getStatistics();
        $recentServers = $this->dashboardService->getRecentServers();
        $recentTools = $this->dashboardService->getRecentTools();

        return view('mcp-builder::dashboard.index', compact('statistics', 'recentServers', 'recentTools'));
    }
}

