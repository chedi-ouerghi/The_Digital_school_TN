<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $table = 'instructeur';
    protected $fillable = ['Nom', 'Prenom'];

    public function cours()
    {
        return $this->hasMany(Cours::class, 'InstructeurId');
    }
}
