<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'remember_token',
        'last_id_change_at',
        'profile_picture',
        'profile_banner',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'password' => 'hashed', 
        'last_id_change_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relations
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function accountRequest()
    {
        return $this->hasOne(AccountRequest::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return strtoupper($this->role ?? '') === 'ADMIN';
    }

    /**
     * Génération automatique d'ID et remember_token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = strtoupper(Str::random(14));
            }
            if (empty($model->remember_token)) {
                $model->remember_token = Str::random(60);
            }
        });
    }
}
