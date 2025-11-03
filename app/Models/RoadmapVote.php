<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'item_id',
        'vote_type', // like ou dislike
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function item()
    {
        return $this->belongsTo(RoadmapItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(Profile::class, 'user_id');
    }
}
