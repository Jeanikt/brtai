<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'phone',
        'email',
        'location',
        'rating',
        'price_range',
        'is_verified',
        'metadata'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'is_verified' => 'boolean',
        'metadata' => 'array'
    ];

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriceRange($query, $priceRange)
    {
        return $query->where('price_range', $priceRange);
    }

    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating, 1);
    }
}
