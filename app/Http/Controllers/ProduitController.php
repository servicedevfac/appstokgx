<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProduitController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} produit")) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission d\'effectuer cette action sur les produits.');
        }
    }

    private function ensureSameEntreprise(Produit $produit): void
    {
        $user = Auth::user();
        $currentId = EntrepriseContext::currentId($user);
        if (! $user->canAccessEntreprise((int) $produit->entreprise_id) || ($currentId && (int) $produit->entreprise_id !== (int) $currentId)) {
            abort(403, 'Accès interdit au produit d\'une autre entreprise.');
        }
    }

    public function index()
    {
        $this->checkPermission('view');
        $produits = Produit::with(['mouvements', 'categorie'])->orderBy('nom')->paginate(15);
        $categories = Categorie::orderBy('nom')->get();
        return view('admin.produits.index', compact('produits', 'categories'));
    }

    public function create()
    {
        $this->checkPermission('create');
        $categories = Categorie::orderBy('nom')->get();
        return view('admin.produits.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');
        $entrepriseId = EntrepriseContext::currentId();
        $entreprise = EntrepriseContext::current();

        if ($entreprise && ! $entreprise->canAddProduit()) {
            return back()->withInput()->with(
                'error',
                'Limite maximale de produits atteinte pour votre abonnement actuel (' . ($entreprise->abonnementActuel()?->nom_plan ?? 'Standard') . '). Veuillez passer à une formule supérieure.'
            );
        }

        $validated = $request->validate([
            'nom'           => [
                'required',
                'string',
                'max:255',
                Rule::unique('produits', 'nom')->where(fn ($q) => $q->where('entreprise_id', $entrepriseId)),
            ],
            'reference'     => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('produits', 'reference')->where(fn ($q) => $q->where('entreprise_id', $entrepriseId)),
            ],
            'categorie_id'  => 'nullable|integer|exists:categories,id',
            'prix_achat'    => 'nullable|numeric|min:0',
            'prix'          => 'required|numeric|min:0',
            'seuil_alerte'  => 'required|integer|min:0',
            'stock_initial' => 'nullable|integer|min:0',
        ], [
            'nom.required'          => 'Le nom du produit est obligatoire.',
            'nom.unique'            => 'Ce produit existe déjà dans votre entreprise.',
            'prix.required'         => 'Le prix de vente du produit est obligatoire.',
            'prix.numeric'          => 'Le prix doit être un nombre valide.',
            'prix.min'              => 'Le prix ne peut pas être négatif.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.integer'  => 'Le seuil d\'alerte doit être un nombre entier.',
            'seuil_alerte.min'      => 'Le seuil d\'alerte ne peut pas être négatif.',
        ]);

        if (! empty($validated['categorie_id'])) {
            $categorie = Categorie::withoutGlobalScope('entreprise')->findOrFail($validated['categorie_id']);
            if ($entrepriseId && (int) $categorie->entreprise_id !== (int) $entrepriseId) {
                abort(403, 'La catégorie sélectionnée n\'appartient pas à votre entreprise.');
            }
        }

        DB::transaction(function () use ($validated, $entrepriseId) {
            $produit = Produit::create([
                'entreprise_id'  => $entrepriseId,
                'categorie_id'   => $validated['categorie_id'] ?? null,
                'nom'            => $validated['nom'],
                'reference'      => $validated['reference'] ?? null,
                'prix_achat'     => $validated['prix_achat'] ?? 0,
                'prix'           => $validated['prix'],
                'seuil_alerte'   => $validated['seuil_alerte'],
                'alerte_envoyee' => false,
            ]);

            $stockInitial = (int) ($validated['stock_initial'] ?? 0);
            if ($stockInitial > 0) {
                MouvementStock::create([
                    'entreprise_id'  => $entrepriseId,
                    'produit_id'     => $produit->id,
                    'user_id'        => Auth::id(),
                    'quantite'       => $stockInitial,
                    'type_mouvement' => 'entree',
                    'date_mouvement' => now()->format('Y-m-d'),
                    'motif'          => 'Stock initial à la création du produit',
                ]);
            }
        });

        return redirect()->route('produits.index')->with('success', 'Produit créé avec succès.');
    }

    public function show($id)
    {
        $this->checkPermission('view');
        $produit = Produit::withoutGlobalScope('entreprise')->with(['mouvements.user', 'details', 'categorie'])->findOrFail($id);
        $this->ensureSameEntreprise($produit);

        return view('admin.produits.show', compact('produit'));
    }

    public function edit($id)
    {
        $this->checkPermission('edit');
        $produit = Produit::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($produit);

        $categories = Categorie::orderBy('nom')->get();
        return view('admin.produits.edit', compact('produit', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');
        $produit = Produit::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($produit);
        $entrepriseId = $produit->entreprise_id;

        $validated = $request->validate([
            'nom'          => [
                'required',
                'string',
                'max:255',
                Rule::unique('produits', 'nom')
                    ->where(fn ($q) => $q->where('entreprise_id', $entrepriseId))
                    ->ignore($produit->id),
            ],
            'reference'    => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('produits', 'reference')
                    ->where(fn ($q) => $q->where('entreprise_id', $entrepriseId))
                    ->ignore($produit->id),
            ],
            'categorie_id' => 'nullable|integer|exists:categories,id',
            'prix_achat'   => 'nullable|numeric|min:0',
            'prix'         => 'required|numeric|min:0',
            'seuil_alerte' => 'required|integer|min:0',
        ]);

        // NB : La modification du prix du produit ne modifie jamais les detail_ventes existants (RM-035)
        $produit->update([
            'categorie_id' => $validated['categorie_id'] ?? $produit->categorie_id,
            'nom'          => $validated['nom'],
            'reference'    => $validated['reference'] ?? $produit->reference,
            'prix_achat'   => $validated['prix_achat'] ?? $produit->prix_achat,
            'prix'         => $validated['prix'],
            'seuil_alerte' => $validated['seuil_alerte'],
        ]);

        return redirect()->route('produits.index')->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $this->checkPermission('delete');
        $produit = Produit::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($produit);

        if ($produit->details()->count() > 0) {
            return redirect()->route('produits.index')->with(
                'error',
                'Impossible de supprimer ce produit : il figure dans des factures ou ventes existantes.'
            );
        }

        if ($produit->mouvements()->withoutGlobalScope('entreprise')->count() > 0) {
            return redirect()->route('produits.index')->with(
                'error',
                'Impossible de supprimer ce produit : des mouvements de stock y sont rattachés.'
            );
        }

        $produit->delete();

        return redirect()->route('produits.index')->with('success', 'Produit supprimé avec succès.');
    }

    public function indexAlerte()
    {
        $this->checkPermission('view');
        $produits = Produit::with('mouvements')->get();

        $produitsAlerte = $produits->filter(function ($produit) {
            return $produit->stock_actuel <= $produit->seuil_alerte;
        });

        return view('produits.alertes', compact('produitsAlerte'));
    }
}
