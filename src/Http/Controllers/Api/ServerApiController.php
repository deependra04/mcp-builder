<?php

namespace Laravel\McpBuilder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Laravel\McpBuilder\Http\Controllers\Controller;
use Laravel\McpBuilder\Services\ServerManager;
use Laravel\McpBuilder\Models\McpServer;
use Laravel\McpBuilder\Services\ErrorResponseFormatter;
use Exception;

class ServerApiController extends Controller
{
    protected ServerManager $serverManager;
    protected ErrorResponseFormatter $errorFormatter;

    public function __construct(ServerManager $serverManager)
    {
        $this->serverManager = $serverManager;
        $this->errorFormatter = app(ErrorResponseFormatter::class);
    }

    /**
     * Display a listing of the servers.
     */
    public function index()
    {
        $servers = McpServer::with('tools')->get();
        return response()->json($servers);
    }

    /**
     * Display the specified server.
     */
    public function show($id)
    {
        try {
            $server = $this->serverManager->get($id);

            if (!$server) {
                throw ServerNotFoundException::forId($id);
            }

            $server->load('tools');
            return response()->json([
                'success' => true,
                'data' => $server,
            ]);
        } catch (ServerNotFoundException $e) {
            return $this->errorFormatter->formatApiError($e, 404);
        } catch (Exception $e) {
            return $this->errorFormatter->formatApiError($e, 500);
        }
    }

    /**
     * Store a newly created server.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:mcp_servers,name',
                'version' => 'required|string',
                'description' => 'nullable|string',
                'config' => 'nullable|array',
            ]);

            $server = $this->serverManager->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Server created successfully',
                'data' => $server,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorFormatter->formatValidationErrors($e->errors());
        } catch (Exception $e) {
            return $this->errorFormatter->formatApiError($e, 500);
        }
    }

    /**
     * Update the specified server.
     */
    public function update(Request $request, $id)
    {
        try {
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

            $server = $this->serverManager->get($id);
            return response()->json([
                'success' => true,
                'message' => 'Server updated successfully',
                'data' => $server,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorFormatter->formatValidationErrors($e->errors());
        } catch (Exception $e) {
            return $this->errorFormatter->formatApiError($e, 500);
        }
    }

    /**
     * Remove the specified server.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->serverManager->delete($id);

            if (!$deleted) {
                throw new \RuntimeException('Failed to delete server');
            }

            return response()->json([
                'success' => true,
                'message' => 'Server deleted successfully',
            ]);
        } catch (Exception $e) {
            return $this->errorFormatter->formatApiError($e, 500);
        }
    }
}

