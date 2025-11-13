<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CryptoHistory extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'crypto_history';

    protected $fillable = [
        'cryptomoney_id',
        'price',
        'market_cap',
        'volume',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'market_cap' => 'decimal:8',
        'volume' => 'decimal:8',
        'recorded_at' => 'datetime',
    ];

    public function cryptomoney()
    {
        return $this->belongsTo(Cryptomoney::class, 'cryptomoney_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = strtoupper(Str::random(14));
            }
        });
    }
}
