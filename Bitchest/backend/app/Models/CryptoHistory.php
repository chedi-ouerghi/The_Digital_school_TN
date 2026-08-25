<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CryptoHistory extends Model
{
    use HasFactory;

    // ✅ Configuration de la clé primaire
    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $table = 'crypto_history';

    // ✅ CORRECTION: Ajouter tous les champs fillable
    protected $fillable = [
        'cryptomoney_id',
        'price',
        'volume',
        'recorded_at',
    ];

    // ✅ CORRECTION: Types de casting appropriés
    protected $casts = [
        'id' => 'string',
        'cryptomoney_id' => 'string',
        'price' => 'decimal:10',
        'volume' => 'decimal:2',
        'recorded_at' => 'immutable_date',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    // ✅ Relations
    public function cryptomoney()
    {
        return $this->belongsTo(Cryptomoney::class, 'cryptomoney_id');
    }

    // ✅ Génération automatique de l'ID (UUID harmonisé)
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ✅ Accesseurs pour faciliter la lecture
    public function getPriceFormatted(): string
    {
        return number_format($this->price, 8, '.', '');
    }

    public function getMarketCapFormatted(): string
    {
        return 'N/A'; // market_cap n'existe plus dans CryptoHistory
    }

    public function getVolumeFormatted(): string
    {
        return number_format($this->volume ?? 0, 2, '.', '');
    }

    // ✅ Scopes utiles
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    public function scopeForCrypto($query, string $cryptoId)
    {
        return $query->where('cryptomoney_id', $cryptoId);
    }

    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('recorded_at', 'asc');
    }
}