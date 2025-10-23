<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'status_code',
        'response',
        'processed_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime'
    ];

    public function markAsProcessed($statusCode = 200, $response = null)
    {
        $this->update([
            'status_code' => $statusCode,
            'response' => $response,
            'processed_at' => now()
        ]);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }
}
