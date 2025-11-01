<?php

namespace Laravel\McpBuilder\Services;

use Illuminate\Support\Facades\File;
use Laravel\McpBuilder\Models\McpServer;

class ExportService
{
    /**
     * Export server configuration to JSON file.
     */
    public function exportServer(McpServer $server, string $exportPath = null): string
    {
        $exportPath = $exportPath ?? storage_path('mcp-exports');
        File::ensureDirectoryExists($exportPath);

        $filename = "{$server->name}_" . date('Y-m-d_His') . '.json';
        $filePath = "{$exportPath}/{$filename}";

        $data = [
            'server' => [
                'name' => $server->name,
                'version' => $server->version,
                'description' => $server->description,
                'config' => $server->config,
                'status' => $server->status,
            ],
            'tools' => $server->tools->map(function ($tool) {
                return [
                    'name' => $tool->name,
                    'description' => $tool->description,
                    'input_schema' => $tool->input_schema,
                    'handler_class' => $tool->handler_class,
                    'handler_method' => $tool->handler_method,
                    'metadata' => $tool->metadata,
                    'is_active' => $tool->is_active,
                ];
            })->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        File::put($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $filePath;
    }

    /**
     * Import server configuration from JSON file.
     */
    public function importServer(string $importPath, string $serverName = null): McpServer
    {
        if (!File::exists($importPath)) {
            throw new \InvalidArgumentException("Import file not found: {$importPath}");
        }

        $content = File::get($importPath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in import file: " . json_last_error_msg());
        }

        $serverData = $data['server'] ?? [];
        $toolsData = $data['tools'] ?? [];

        // Use provided name or fallback to imported name
        $name = $serverName ?? $serverData['name'] ?? 'imported-server-' . time();

        // Create or update server
        $server = McpServer::updateOrCreate(
            ['name' => $name],
            [
                'version' => $serverData['version'] ?? '1.0.0',
                'description' => $serverData['description'] ?? 'Imported server',
                'config' => $serverData['config'] ?? null,
                'status' => $serverData['status'] ?? 'inactive',
            ]
        );

        // Import tools
        foreach ($toolsData as $toolData) {
            $server->tools()->updateOrCreate(
                ['name' => $toolData['name']],
                [
                    'description' => $toolData['description'] ?? null,
                    'input_schema' => $toolData['input_schema'] ?? null,
                    'handler_class' => $toolData['handler_class'] ?? null,
                    'handler_method' => $toolData['handler_method'] ?? null,
                    'metadata' => $toolData['metadata'] ?? null,
                    'is_active' => $toolData['is_active'] ?? true,
                ]
            );
        }

        return $server->fresh(['tools']);
    }
}

