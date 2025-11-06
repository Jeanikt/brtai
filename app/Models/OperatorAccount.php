<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_type',
        'document',
        'pix_key',
        'bank_code',
        'agency',
        'account',
        'verified',
        'verification_data'
    ];

    protected $casts = [
        'verification_data' => 'array',
        'verified' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function getDocumentFormattedAttribute()
    {
        $doc = $this->document;
        if ($this->account_type === 'CPF' && strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        } elseif ($this->account_type === 'CNPJ' && strlen($doc) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
        }
        return $doc;
    }
}
