<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PriceTier extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'max_quantity',
        'current_quantity',
        'is_active',
        'start_sale_at',
        'end_sale_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'start_sale_at' => 'datetime',
        'end_sale_at' => 'datetime'
    ];

    protected static function booted()
    {
        static::creating(function ($priceTier) {
            if (empty($priceTier->id)) {
                $priceTier->id = (string) Str::uuid();
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function isAvailable()
    {
        if (!$this->is_active) return false;

        if ($this->max_quantity && $this->current_quantity >= $this->max_quantity) {
            return false;
        }

        $now = now();
        if ($this->start_sale_at && $this->start_sale_at > $now) {
            return false;
        }

        if ($this->end_sale_at && $this->end_sale_at < $now) {
            return false;
        }

        return true;
    }

    public function incrementQuantity()
    {
        $this->increment('current_quantity');
    }

    public function decrementQuantity()
    {
        $this->decrement('current_quantity');
    }
}
