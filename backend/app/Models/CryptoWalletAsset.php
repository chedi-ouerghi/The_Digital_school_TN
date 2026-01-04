<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class CryptoWalletAsset extends Pivot
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'crypto_wallet_assets';

    protected $fillable = [
        'wallet_id',
        'cryptomoney_id',
        'quantity',
        'average_buy_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:18',
        'average_buy_price' => 'decimal:18',
    ];

    /* ===================== RELATIONS ===================== */

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function cryptomoney()
    {
        return $this->belongsTo(Cryptomoney::class, 'cryptomoney_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'crypto_wallet_asset_id');
    }

    /* ===================== CALCULS ===================== */

    public function getTotalInvested(): float
    {
        return (float) $this->quantity * (float) $this->average_buy_price;
    }

    public function getCurrentValue(): float
    {
        $price = (float) ($this->cryptomoney?->price_eur ?? 0);
        return (float) $this->quantity * $price;
    }



    /* ===================== BOOT ===================== */

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
