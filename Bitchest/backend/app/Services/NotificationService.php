<?php

namespace App\Services;

use App\Models\Cryptomoney;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function create(array $attributes): Notification
    {
        foreach (['read_at', 'action_url', 'entity_id', 'entity_type', 'metadata', 'dedupe_key'] as $column) {
            if (!Schema::hasColumn('notifications', $column)) {
                unset($attributes[$column]);
            }
        }
        $attributes['is_read'] = $attributes['is_read'] ?? false;
        $attributes['read_at'] = $attributes['is_read'] ? ($attributes['read_at'] ?? now()) : null;

        if (!empty($attributes['dedupe_key'])) {
            return Notification::firstOrCreate(
                ['dedupe_key' => $attributes['dedupe_key']],
                $attributes
            );
        }

        return Notification::create($attributes);
    }

    public function createWelcome(User $user): Notification
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_WELCOME,
            'title' => 'Welcome to BitChest',
            'message' => "Welcome {$user->name}, your account is now active.",
            'action_url' => '/dashboard/portfolio',
            'entity_id' => $user->id,
            'entity_type' => User::class,
            'dedupe_key' => "welcome:user:{$user->id}",
        ]);
    }

    public function createRoleSync(User $user, string $oldRole, string $newRole): Notification
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ROLE_SYNC,
            'title' => 'Account status updated',
            'message' => "Your account status has been updated. You are now {$newRole}.",
            'action_url' => '/dashboard/profile',
            'entity_id' => $user->id,
            'entity_type' => User::class,
            'metadata' => ['old_role' => $oldRole, 'new_role' => $newRole],
            'dedupe_key' => "role-sync:user:{$user->id}:{$oldRole}:{$newRole}",
        ]);
    }

    public function createPriceChange(Cryptomoney $crypto, string $oldPrice, string $newPrice): int
    {
        if (bccomp($oldPrice, $newPrice, 18) === 0) {
            return 0;
        }

        $users = User::query()
            ->where('role', 'CLIENT')
            ->whereHas('wallets.cryptoWalletAssets', fn ($query) =>
                $query->where('cryptomoney_id', $crypto->id)
            )
            ->pluck('id');

        $created = 0;
        foreach ($users as $userId) {
            $notification = $this->create([
                'user_id' => $userId,
                'type' => Notification::TYPE_PRICE_CHANGE,
                'title' => 'Price updated',
                'message' => "The price of {$crypto->name} has been updated from €{$oldPrice} to €{$newPrice}.",
                'action_url' => "/dashboard/cryptos/{$crypto->id}",
                'entity_id' => $crypto->id,
                'entity_type' => Cryptomoney::class,
                'metadata' => [
                    'symbol' => $crypto->symbol,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                ],
                'dedupe_key' => "price-change:user:{$userId}:crypto:{$crypto->id}:{$newPrice}",
            ]);
            $created += $notification->wasRecentlyCreated ? 1 : 0;
        }

        return $created;
    }

    public function getUserNotifications(User $user, int $perPage = 20, ?bool $read = null): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->when($read !== null, fn ($query) => $query->where('is_read', $read))
            ->latest()
            ->paginate(min(max($perPage, 1), 100));
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)->where('is_read', false)->count();
    }

    public function markAsRead(User $user, string $id): ?Notification
    {
        $notification = Notification::findOrFail($id);
        if ($notification->user_id !== $user->id) {
            abort(403, 'Access denied.');
        }
        if (!$notification->is_read) {
            $attributes = ['is_read' => true];
            if (Schema::hasColumn('notifications', 'read_at')) {
                $attributes['read_at'] = now();
            }
            $notification->forceFill($attributes)->save();
        }
        return $notification->fresh();
    }

    public function markAllAsRead(User $user): int
    {
        $attributes = ['is_read' => true];
        if (Schema::hasColumn('notifications', 'read_at')) {
            $attributes['read_at'] = now();
        }

        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update($attributes);
    }
}