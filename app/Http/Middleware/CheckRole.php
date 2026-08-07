
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        
        if (!$user) {
            abort(403, 'Non authentifié');
        }
        
        // Si aucun rôle n'est spécifié, autoriser
        if (empty($roles)) {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a l'un des rôles autorisés
        if (in_array($user->role, $roles)) {
            return $next($request);
        }
        
        abort(403, 'Accès non autorisé');
    }
}