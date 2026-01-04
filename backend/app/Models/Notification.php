<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;
    /**
     * Important pour indiquer à Laravel que la clé primaire n'est pas auto-incrémentée
     * et qu'elle est de type string.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Types constants
    public const TYPE_ACCOUNT_REQUEST = 'account_request';
    public const TYPE_TRANSACTION = 'transaction';
    public const TYPE_PRICE_UPDATE = 'price_update';
    public const TYPE_ADMIN_ACTION = 'admin_action';
    public const TYPE_WELCOME = 'welcome';

    /**
     * Notification belongs to a User (nullable for global notifications)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper placeholder: return an icon name depending on type (for future UI)
     */
    public function icon(): string
    {
        return match ($this->type) {
            self::TYPE_ACCOUNT_REQUEST => 'user-plus',
            self::TYPE_TRANSACTION => 'swap-horizontal',
            self::TYPE_PRICE_UPDATE => 'trending-up',
            self::TYPE_ADMIN_ACTION => 'shield-check',
            self::TYPE_WELCOME => 'gift',
            default => 'bell'
        };
    }

    /**
     * Helper placeholder: return a color for the UI
     */
    public function color(): string
    {
        return match ($this->type) {
            self::TYPE_ACCOUNT_REQUEST => 'yellow',
            self::TYPE_TRANSACTION => 'blue',
            self::TYPE_PRICE_UPDATE => 'green',
            self::TYPE_ADMIN_ACTION => 'red',
            self::TYPE_WELCOME => 'green',
            default => 'gray'
        };
    }

    /**
     * Whether this type should also trigger an email in parallel.
     * Prepared for future implementation (queue/mail).
     */
    public function shouldSendEmail(): bool
    {
        return in_array($this->type, [self::TYPE_ACCOUNT_REQUEST, self::TYPE_ADMIN_ACTION], true);
    }

    /**
     * Génère automatiquement un id string unique de 14 caractères
     * avant la création du modèle.
     */
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
