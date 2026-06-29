<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope pour filtrer par type d'action
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    // Scope pour filtrer par modèle
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    // Scope pour les dernières 24h
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}