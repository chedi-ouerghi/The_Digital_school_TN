<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'transactions';

    protected $fillable = [
        'crypto_wallet_asset_id',
        'cryptomoney_id',
        'type',
        'quantity',
        'price',
        'total_eur',
        'admin_operation',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price' => 'decimal:8',
        'total_eur' => 'decimal:8',
        'admin_operation' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    public function cryptoWalletAsset()
    {
        return $this->belongsTo(CryptoWalletAsset::class, 'crypto_wallet_asset_id');
    }

    public function cryptomoney()
    {
        return $this->belongsTo(Cryptomoney::class, 'cryptomoney_id');
    }

    public function getWalletAttribute()
    {
        return $this->cryptoWalletAsset ? $this->cryptoWalletAsset->wallet : null;
    }

    public function wallet()
    {
        return $this->cryptoWalletAsset ? $this->cryptoWalletAsset->wallet : null;
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
