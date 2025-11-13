<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cryptomoney extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'cryptomoney';

    protected $fillable = [
        'name',
        'symbol',
        'coingecko_id',
        'image',
        'category',
        'website',
        'price_eur',
        'market_cap',
        'volume_24h',
        'change_24h_pct',
        'updated_at_api',
    ];

    protected $hidden = [];

    protected $appends = ['image_url', 'price', 'change_24h'];

    protected $casts = [
        'price_eur' => 'decimal:8',
        'market_cap' => 'decimal:8',
        'volume_24h' => 'decimal:8',
        'change_24h_pct' => 'decimal:8',
        'updated_at_api' => 'datetime',
    ];

    public function getPriceAttribute()
    {
        return $this->price_eur;
    }

    public function getChange24hAttribute()
    {
        return $this->change_24h_pct;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Check if file exists in public storage (symlinked)
            $imagePath = public_path('storage/' . $this->image);
            if (file_exists($imagePath)) {
                return asset('storage/' . $this->image);
            } else {
                \Log::warning('Image file not found: ' . $imagePath);
            }
        }
        // Fallback to default image
        return asset('images/default-crypto.png');
    }

    public function setImageAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['image'] = $value;
        }
    }

    public function cryptoWalletAssets()
    {
        return $this->hasMany(CryptoWalletAsset::class, 'cryptomoney_id');
    }

    public function histories()
    {
        return $this->hasMany(CryptoHistory::class, 'cryptomoney_id');
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

    public function toArray()
    {
        $array = parent::toArray();
        // Ensure image_url is always included
        if (!isset($array['image_url'])) {
            $array['image_url'] = $this->image_url;
        }
        return $array;
    }

    /**
     * Store image file and return relative path
     */
    public static function storeImage($imageFile)
    {
        if (!$imageFile) return null;
        $filename = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
        return $imageFile->storeAs('cryptos', $filename, 'public');
    }

    /**
     * Delete image file from storage
     */
    public function deleteImage()
    {
        if ($this->image) {
            $path = public_path('storage/' . $this->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
