<?php

namespace Laravel\McpBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McpServer extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'mcp_servers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'version',
        'description',
        'config',
        'status',
        'storage_path',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tools for this server.
     */
    public function tools(): HasMany
    {
        return $this->hasMany(McpTool::class);
    }

    /**
     * Check if the server is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the configuration file path.
     */
    public function getConfigPathAttribute(): string
    {
        $storagePath = config('mcp-builder.storage_path');
        return "{$storagePath}/{$this->name}.json";
    }
}

