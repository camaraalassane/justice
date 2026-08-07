<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Armee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function militaires()
    {
        return $this->hasMany(Militaire::class);
    }
}