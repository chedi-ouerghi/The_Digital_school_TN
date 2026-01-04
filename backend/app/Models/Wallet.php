<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance_eur',
    ];

    protected $casts = [
        'balance_eur' => 'decimal:2',
    ];

    /* ===================== RELATIONS ===================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cryptoWalletAssets()
    {
        return $this->hasMany(CryptoWalletAsset::class, 'wallet_id');
    }

    public function transactions()
    {
        return $this->hasManyThrough(
            Transaction::class,
            CryptoWalletAsset::class,
            'wallet_id',
            'crypto_wallet_asset_id'
        );
    }

    /* ===================== CALCULS ===================== */

    public function getTotalValue(): float
    {
        return $this->cryptoWalletAssets->sum(
            fn ($asset) => $asset->getCurrentValue()
        );
    }

    public function getTotalPlusValue(): float
    {
        return $this->cryptoWalletAssets->sum(
            fn ($asset) => $asset->getCurrentValue() - ($asset->quantity * $asset->average_buy_price)
        );
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
