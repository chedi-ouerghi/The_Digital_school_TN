<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    protected $table = 'commentaire';
    protected $fillable = ['Contenu', 'CoursId', 'EtudiantId'];

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'CoursId');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'EtudiantId');
    }
}
