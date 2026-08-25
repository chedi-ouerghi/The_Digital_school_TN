<?php

namespace App\Models;

use App\Helpers\DecimalMath;
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

    public function getTotalValue(): string
    {
        return $this->cryptoWalletAssets->reduce(
            fn (string $total, $asset) => DecimalMath::add($total, $asset->getCurrentValue()),
            '0'
        );
    }

    public function getTotalPlusValue(): string
    {
        return $this->cryptoWalletAssets->reduce(
            fn (string $total, $asset) => DecimalMath::add(
                $total,
                DecimalMath::subtract($asset->getCurrentValue(), $asset->getTotalInvested())
            ),
            '0'
        );
    }


    /* ===================== BOOT ===================== */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
