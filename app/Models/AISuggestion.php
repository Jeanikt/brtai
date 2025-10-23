<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AISuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'suggestion_type',
        'content',
        'confidence_score',
        'is_applied',
        'applied_at',
        'feedback_score'
    ];

    protected $casts = [
        'content' => 'array',
        'confidence_score' => 'decimal:2',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function markAsApplied()
    {
        $this->update([
            'is_applied' => true,
            'applied_at' => now()
        ]);
    }

    public function scopeEventTheme($query)
    {
        return $query->where('suggestion_type', 'event_theme');
    }

    public function scopePricing($query)
    {
        return $query->where('suggestion_type', 'pricing');
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 0.7);
    }
}
