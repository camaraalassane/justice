<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfractionBase extends Model
{
    protected $table = 'infractions_base';

    protected $fillable = [
        'code_infraction',
        'libelle',
        'description',
        'classification',
        'nature',
        'gravite',
    ];

    // Procédures liées à cette infraction
    public function procedures()
    {
        return $this->belongsToMany(Procedure::class, 'procedure_infraction')
                    ->withPivot('qualification')
                    ->withTimestamps();
    }
    /**
 * Générer un code d'infraction automatique
 */
public static function generateCode(string $classification): string
{
    $prefix = match($classification) {
        'Criminelle' => 'INF-CR',
        'Délictuelle' => 'INF-DE',
        'Contravention' => 'INF-CO',
        default => 'INF-XX',
    };

    $last = self::where('code_infraction', 'LIKE', "{$prefix}%")
        ->orderBy('code_infraction', 'desc')
        ->first();

    if ($last) {
        $num = intval(substr($last->code_infraction, -2)) + 1;
    } else {
        $num = 1;
    }

    return $prefix . str_pad($num, 2, '0', STR_PAD_LEFT);
}
}