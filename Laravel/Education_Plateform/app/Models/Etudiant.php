<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $table = 'etudiant';
    protected $fillable = ['Nom', 'Email', 'MotDePasse', 'PhotoProfil'];

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'EtudiantId');
    }

    public function demandesRejoindreCours()
    {
        return $this->hasMany(StudentRequest::class, 'EtudiantId');
    }
}
