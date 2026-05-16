<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable; // Ajoutez cette ligne

class Utilisateur extends Model implements Authenticatable // Ajoutez cette ligne
{
    use AuthenticatableTrait;

    protected $table = 'utilisateur';
    protected $fillable = ['Nom', 'Prenom', 'Email', 'MotDePasse', 'Role'];
}
