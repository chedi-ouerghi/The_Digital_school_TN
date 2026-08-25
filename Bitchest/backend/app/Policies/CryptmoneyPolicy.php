<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cryptomoney;

class CryptmoneyPolicy
{
    /**
     * Détermine si l'utilisateur peut créer une cryptomonnaie
     * Seuls les admins peuvent ajouter des cryptos
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une cryptomonnaie
     * Seuls les admins peuvent modifier
     */
    public function update(User $user, Cryptomoney $cryptomoney): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Détermine si l'utilisateur peut supprimer une cryptomonnaie
     * Seuls les admins peuvent supprimer
     */
    public function delete(User $user, Cryptomoney $cryptomoney): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tout le monde peut voir les cryptos
     */
    public function view(User $user, Cryptomoney $cryptomoney): bool
    {
        return true;
    }

    /**
     * Tout le monde peut lister les cryptos
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
}
