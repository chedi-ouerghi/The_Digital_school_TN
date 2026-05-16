<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $table = 'cours';
    protected $fillable = ['Titre', 'Description', 'CategorieId', 'InstructeurId'];

    public function categorie()
    {
        return $this->belongsTo(Category::class, 'CategorieId');
    }

    public function instructeur()
    {
        return $this->belongsTo(Instructor::class, 'InstructeurId');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'CoursId');
    }
}
