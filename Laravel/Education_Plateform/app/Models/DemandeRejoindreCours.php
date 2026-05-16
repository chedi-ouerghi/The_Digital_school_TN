<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRequest extends Model
{
    protected $table = 'demande_rejoindre_cours';
    protected $fillable = ['CoursId', 'EtudiantId', 'Statut'];

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'CoursId');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'EtudiantId');
    }
}
