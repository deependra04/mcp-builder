<?php

namespace Laravel\McpBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpTool extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'mcp_tools';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mcp_server_id',
        'name',
        'description',
        'input_schema',
        'handler_class',
        'handler_method',
        'metadata',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'input_schema' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the server that owns this tool.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(McpServer::class, 'mcp_server_id');
    }

    /**
     * Scope to get only active tools.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

