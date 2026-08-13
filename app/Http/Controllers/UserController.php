<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use LogsActivity;
    /**
     * Vérifier que l'utilisateur est Admin
     */
    private function checkAdmin()
    {
        if (!auth()->user()->peutGererUtilisateurs()) {
            abort(403, 'Accès non autorisé. Seul l\'Administrateur peut gérer les utilisateurs.');
        }
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $users = User::query()
            ->when($request->search, function($q) use ($request) {
                $search = $request->search;
                $q->where(function($sub) use ($search) {
                    $sub->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhere('role', 'ILIKE', "%{$search}%");
                });
            })
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => User::getRoles(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->checkAdmin();

        return Inertia::render('Users/Create', [
            'roles' => User::getRoles(),
        ]);
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|in:' . implode(',', array_keys(User::getRoles())),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Journalisation
        $this->logCreate($user, "Utilisateur créé : {$user->name} ({$user->email}) - Rôle : {$request->role}");

        return redirect()->route('users.index')
            ->with('success', "Utilisateur {$user->name} créé avec succès.");
    }

    /**
     * Formulaire d'édition
     */
    public function edit(User $user)
    {
        $this->checkAdmin();

        // Empêcher un utilisateur de se modifier lui-même
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas modifier votre propre compte.');
        }

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => User::getRoles(),
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->checkAdmin();

        // Empêcher la modification de soi-même
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas modifier votre propre compte.');
        }

        // Empêcher la modification du dernier Admin
        if ($user->role === 'ADMIN' && $request->role !== 'ADMIN') {
            $adminCount = User::where('role', 'ADMIN')->count();
            if ($adminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Impossible de modifier le dernier Administrateur.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:' . implode(',', array_keys(User::getRoles())),
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Journalisation
        $this->logUpdate($user, "Utilisateur modifié : {$user->name} - Nouveau rôle : {$request->role}");

        return redirect()->route('users.index')
            ->with('success', "Utilisateur {$user->name} mis à jour avec succès.");
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $this->checkAdmin();

        // Empêcher la suppression de soi-même
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher la suppression du dernier Admin
        if ($user->role === 'ADMIN') {
            $adminCount = User::where('role', 'ADMIN')->count();
            if ($adminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Impossible de supprimer le dernier Administrateur.');
            }
        }

        $name = $user->name;
        $user->delete();

        // Journalisation
        $this->logActivity('delete', 'User', null, "Utilisateur supprimé : {$name}");

        return redirect()->route('users.index')
            ->with('success', "Utilisateur {$name} supprimé avec succès.");
    }
}