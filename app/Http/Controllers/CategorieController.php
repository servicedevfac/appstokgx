<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])
            && ! $user->can("{$action} categorie")
            && ! $user->can("{$action} produit")
        ) {
            abort(403, 'Accès refusé aux catégories.');
        }
    }

    private function ensureSameEntreprise(Categorie $categorie): void
    {
        $user = Auth::user();
        $currentId = EntrepriseContext::currentId($user);
        if (! $user->canAccessEntreprise((int) $categorie->entreprise_id) || ($currentId && (int) $categorie->entreprise_id !== (int) $currentId)) {
            abort(403, 'Accès interdit à une catégorie d\'une autre entreprise.');
        }
    }

    public function index()
    {
        $this->checkPermission('view');
        $categories = Categorie::withCount('produits')->orderBy('nom')->paginate(15);

        if (request()->wantsJson()) {
            return response()->json($categories);
        }

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');
        $entrepriseId = EntrepriseContext::currentId();

        $validated = $request->validate([
            'nom'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'nom')->where(fn ($q) => $q->where('entreprise_id', $entrepriseId)),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        Categorie::create([
            'entreprise_id' => $entrepriseId,
            'nom'           => $validated['nom'],
            'description'   => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');
        $categorie = Categorie::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($categorie);

        $validated = $request->validate([
            'nom'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'nom')
                    ->where(fn ($q) => $q->where('entreprise_id', $categorie->entreprise_id))
                    ->ignore($categorie->id),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $categorie->update($validated);

        return back()->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $this->checkPermission('delete');
        $categorie = Categorie::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($categorie);

        if ($categorie->produits()->withoutGlobalScope('entreprise')->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette catégorie : des produits y sont rattachés.');
        }

        $categorie->delete();

        return back()->with('success', 'Catégorie supprimée avec succès.');
    }
}

