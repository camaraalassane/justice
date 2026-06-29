<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentProcedure extends Model
{
    protected $table = 'documents_procedure';

    protected $fillable = [
        'procedure_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'upload_par',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function uploadeur()
    {
        return $this->belongsTo(User::class, 'upload_par');
    }
}