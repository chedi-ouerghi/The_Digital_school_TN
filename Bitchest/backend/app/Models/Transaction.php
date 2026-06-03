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
        'type',
        'quantity',
        'price',
        'total_eur',
        'admin_operation',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'type' => 'string',
        'quantity' => 'decimal:18',
        'price' => 'decimal:18',
        'total_eur' => 'decimal:18',
        'admin_operation' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    protected $with = ['cryptoWalletAsset.cryptomoney'];

    /* ===================== RELATIONS ===================== */

    public function cryptoWalletAsset()
    {
        return $this->belongsTo(
            CryptoWalletAsset::class,
            'crypto_wallet_asset_id'
        );
    }

    /**
     * Accès indirect au wallet via crypto_wallet_assets
     * (plus simple et plus fiable que hasOneThrough ici)
     */
    public function wallet()
    {
        return $this->cryptoWalletAsset?->wallet();
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
