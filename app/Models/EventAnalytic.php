<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventAnalytic extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'date',
        'page_views',
        'unique_visitors',
        'conversion_rate',
        'total_revenue',
        'tickets_sold',
        'metadata'
    ];

    protected $casts = [
        'date' => 'date',
        'conversion_rate' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'metadata' => 'array'
    ];

    protected static function booted()
    {
        static::creating(function ($eventAnalytic) {
            if (empty($eventAnalytic->id)) {
                $eventAnalytic->id = (string) Str::uuid();
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function incrementPageViews()
    {
        $this->increment('page_views');
    }

    public function incrementUniqueVisitors()
    {
        $this->increment('unique_visitors');
    }

    public function updateConversionRate()
    {
        if ($this->unique_visitors > 0) {
            $this->conversion_rate = ($this->tickets_sold / $this->unique_visitors) * 100;
            $this->save();
        }
    }
}
