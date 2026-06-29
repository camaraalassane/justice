<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieGrade extends Model
{
    protected $table = 'categories_grades'; // ← Ajouter cette ligne

    protected $fillable = ['libelle', 'ordre'];

    public function grades()
    {
        return $this->hasMany(Grade::class, 'categorie_grade_id')->orderBy('ordre');
    }
}