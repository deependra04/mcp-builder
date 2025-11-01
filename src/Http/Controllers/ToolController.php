<?php

namespace Laravel\McpBuilder\Http\Controllers;

use Laravel\McpBuilder\Models\McpTool;

class ToolController extends Controller
{
    /**
     * Display a listing of the tools.
     */
    public function index()
    {
        $tools = McpTool::with('server')->latest()->get();
        return view('mcp-builder::dashboard.tools.index', compact('tools'));
    }

    /**
     * Display the specified tool.
     */
    public function show($id)
    {
        $tool = McpTool::with('server')->findOrFail($id);
        return view('mcp-builder::dashboard.tools.show', compact('tool'));
    }
}

