<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categorie';
    protected $fillable = ['NomCategorie', 'Image'];

    public function cours()
    {
        return $this->hasMany(Cours::class, 'CategorieId');
    }
}

