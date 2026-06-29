<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['libelle', 'abreviation', 'categorie_grade_id', 'ordre', 'age_limite'];

    public function categorie()
    {
        return $this->belongsTo(CategorieGrade::class, 'categorie_grade_id');
    }

    public function militaires()
    {
        return $this->hasMany(Militaire::class);
    }
}