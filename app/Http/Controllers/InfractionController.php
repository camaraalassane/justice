<?php

namespace App\Http\Controllers;

use App\Models\InfractionBase;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InfractionController extends Controller
{
    use LogsActivity;

    /**
     * Liste des infractions
     */
    public function index()
    {
        $infractions = InfractionBase::orderBy('classification')
            ->orderBy('code_infraction')
            ->paginate(50);

        return Inertia::render('Infractions/Index', [
            'infractions' => $infractions,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return Inertia::render('Infractions/Create', [
            'classifications' => ['Criminelle', 'Délictuelle', 'Contravention'],
            'natures' => [
                'Atteinte à l\'honneur',
                'Atteinte aux biens',
                'Manquement à la discipline',
                'Infraction au droit commun',
                'Désertion',
                'Trahison',
            ],
        ]);
    }

    /**
     * Enregistrer une nouvelle infraction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_infraction' => 'required|string|max:20|unique:infractions_base,code_infraction',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classification' => 'required|in:Criminelle,Délictuelle,Contravention',
            'nature' => 'required|string|max:255',
            'gravite' => 'required|integer|min:1|max:5',
        ]);

        $infraction = InfractionBase::create($validated);

        $this->logCreate($infraction, "Infraction créée : {$infraction->code_infraction} - {$infraction->libelle}");

        return redirect()->route('infractions.index')
            ->with('success', 'Infraction créée avec succès.');
    }

    /**
     * Formulaire de modification
     */
    public function edit(InfractionBase $infraction)
    {
        return Inertia::render('Infractions/Edit', [
            'infraction' => $infraction,
            'classifications' => ['Criminelle', 'Délictuelle', 'Contravention'],
            'natures' => [
                'Atteinte à l\'honneur',
                'Atteinte aux biens',
                'Manquement à la discipline',
                'Infraction au droit commun',
                'Désertion',
                'Trahison',
            ],
        ]);
    }

    /**
     * Mettre à jour une infraction
     */
    public function update(Request $request, InfractionBase $infraction)
    {
        $validated = $request->validate([
            'code_infraction' => 'required|string|max:20|unique:infractions_base,code_infraction,' . $infraction->id,
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classification' => 'required|in:Criminelle,Délictuelle,Contravention',
            'nature' => 'required|string|max:255',
            'gravite' => 'required|integer|min:1|max:5',
        ]);

        $changes = array_diff_assoc($validated, $infraction->only(array_keys($validated)));
        $infraction->update($validated);

        if (!empty($changes)) {
            $this->logUpdate($infraction, "Infraction modifiée : {$infraction->code_infraction} - {$infraction->libelle}", $changes);
        }

        return redirect()->route('infractions.index')
            ->with('success', 'Infraction modifiée avec succès.');
    }

    /**
     * Supprimer une infraction (ADMIN uniquement)
     */
    public function destroy(InfractionBase $infraction)
    {
        if (auth()->user()->role !== 'ADMIN') {
            return back()->with('error', 'Seul l\'Administrateur peut supprimer une infraction.');
        }

        if ($infraction->procedures()->exists()) {
            return back()->with('error', 'Impossible de supprimer une infraction utilisée dans des procédures.');
        }

        $this->logDelete($infraction, "Infraction supprimée : {$infraction->code_infraction} - {$infraction->libelle}");
        $infraction->delete();

        return redirect()->route('infractions.index')
            ->with('success', 'Infraction supprimée avec succès.');
    }

    /**
     * Retourne toutes les infractions (pour le select dans Show)
     */
    public function allData()
    {
        $infractions = InfractionBase::select('id', 'code_infraction', 'libelle', 'classification', 'nature')
            ->orderBy('libelle')
            ->get();

        return response()->json($infractions);
    }

    /**
     * Création rapide d'une infraction depuis le formulaire de procédure
     */
    public function quickCreate(Request $request)
    {
        $validated = $request->validate([
            'code_infraction' => 'required|string|max:20|unique:infractions_base,code_infraction',
            'libelle' => 'required|string|max:255',
            'classification' => 'required|in:Criminelle,Délictuelle,Contravention',
            'nature' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $infraction = InfractionBase::create([
            'code_infraction' => $validated['code_infraction'],
            'libelle' => $validated['libelle'],
            'classification' => $validated['classification'],
            'nature' => $validated['nature'] ?? 'Manquement à la discipline',
            'description' => $validated['description'] ?? null,
            'gravite' => 1,
        ]);

        return response()->json($infraction);
    }
}