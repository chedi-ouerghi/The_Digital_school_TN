<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountRequest extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'account_requests';

    protected $fillable = [
        'name',
        'email',
        'status',
        'token',
        'processed_at',
        'processed_by',
        'user_id',
        'rejection_reason',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Relation vers le user lié à cette demande
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation vers l'admin qui a traité la demande
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Génération automatique d'ID et token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = strtoupper(Str::random(14));
            }
            if (empty($model->token)) {
                $model->token = strtoupper(Str::random(32));
            }
        });
    }
}
