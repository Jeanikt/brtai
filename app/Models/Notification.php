<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'participant_id',
        'event_id',
        'type',
        'title',
        'message',
        'channel',
        'status',
        'sent_at',
        'read_at',
        'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
