<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\StockAlerte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MouvementStockController extends Controller
{
    /**
     * Vérification des droits administrateur
     */
    private function checkAdminPermission(string $action = 'manage'): void
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Administrateur', 'super admin']) && !$user->can("{$action} stock")) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à effectuer cette action sur le stock.');
        }
    }

    /**
     * Liste des mouvements de stock avec chargement des relations
     */
    public function index()
    {
        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        $mouvements = MouvementStock::with(['produit', 'user'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.stocks.index', compact('mouvements', 'produits', 'users'));
    }

    /**
     * Formulaire d'enregistrement d'un mouvement de stock
     */
    public function create()
    {
        $this->checkAdminPermission('create');

        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        return view('admin.stocks.create', compact('produits', 'users'));
    }

    /**
     * Enregistrement d'un mouvement de stock
     */
    public function store(Request $request)
    {
        $this->checkAdminPermission('create');

        $validated = $request->validate([
            'produit_id'     => 'required|exists:produits,id',
            'type_mouvement' => 'required|in:entree,sortie',
            'quantite'       => 'required|integer|min:1',
            'motif'          => 'required|string|max:255',
            'date_mouvement' => 'required|date',
        ], [
            'produit_id.required'     => 'Veuillez sélectionner un produit.',
            'produit_id.exists'       => 'Le produit sélectionné n\'existe pas.',
            'type_mouvement.required' => 'Le type de mouvement est obligatoire.',
            'type_mouvement.in'       => 'Le type de mouvement doit être entrée ou sortie.',
            'quantite.required'       => 'La quantité est obligatoire.',
            'quantite.integer'        => 'La quantité doit être un nombre entier.',
            'quantite.min'            => 'La quantité doit être d\'au moins 1.',
            'motif.required'          => 'Le motif du mouvement est obligatoire.',
            'date_mouvement.required' => 'La date du mouvement est obligatoire.',
            'date_mouvement.date'     => 'La date du mouvement n\'est pas valide.',
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);

        // Sécurité anti-stock négatif : vérifier si le stock est suffisant en cas de sortie
        if ($validated['type_mouvement'] === 'sortie' && $validated['quantite'] > $produit->stock_actuel) {
            return back()->withInput()->with(
                'error',
                "Sortie impossible : la quantité demandée ({$validated['quantite']}) dépasse le stock actuel ({$produit->stock_actuel})."
            );
        }

        $mouvement = MouvementStock::create([
            'produit_id'     => $produit->id,
            'user_id'        => Auth::id(),
            'type_mouvement' => $validated['type_mouvement'],
            'quantite'       => $validated['quantite'],
            'motif'          => $validated['motif'],
            'date_mouvement' => $validated['date_mouvement'],
            'vente_id'       => null,
        ]);

        // Recharger le produit pour recalculer le stock
        $produit->load('mouvements');

        // Gestion de l'alerte stock faible
        if ($produit->stock_actuel <= $produit->seuil_alerte && !$produit->alerte_envoyee) {
            try {
                $produitsFaibles = Produit::all()
                    ->filter(fn ($p) => $p->stock_actuel <= $p->seuil_alerte)
                    ->values()
                    ->all();

                $admins = User::role('Administrateur')->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new StockAlerte($produitsFaibles));
                }

                $produit->update([
                    'alerte_envoyee'  => true,
                    'last_alerted_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Éviter de bloquer l'enregistrement si le serveur mail n'est pas joignable
                report($e);
            }
        }

        return redirect()->route('mouvementStocks.index')
                         ->with('success', 'Mouvement de stock enregistré avec succès.');
    }

    /**
     * Détail d'un mouvement de stock
     */
    public function show($id)
    {
        $mouvementStock = MouvementStock::with(['produit', 'user'])->findOrFail($id);
        return view('admin.stocks.show', compact('mouvementStock'));
    }

    /**
     * Formulaire de modification d'un mouvement
     */
    public function edit(MouvementStock $mouvementStock)
    {
        $this->checkAdminPermission('edit');

        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        return view('admin.stocks.edit', compact('mouvementStock', 'produits', 'users'));
    }

    /**
     * Mise à jour d'un mouvement de stock
     */
    public function update(Request $request, MouvementStock $mouvementStock)
    {
        $this->checkAdminPermission('edit');

        $validated = $request->validate([
            'produit_id'     => 'required|exists:produits,id',
            'user_id'        => 'required|exists:users,id',
            'type_mouvement' => 'required|in:entree,sortie',
            'quantite'       => 'required|integer|min:1',
            'motif'          => 'required|string|max:255',
            'date_mouvement' => 'required|date',
        ], [
            'produit_id.required'     => 'Veuillez sélectionner un produit.',
            'produit_id.exists'       => 'Le produit sélectionné n\'existe pas.',
            'user_id.required'        => 'Veuillez sélectionner un utilisateur.',
            'user_id.exists'          => 'L\'utilisateur sélectionné n\'existe pas.',
            'type_mouvement.required' => 'Le type de mouvement est obligatoire.',
            'type_mouvement.in'       => 'Le type de mouvement doit être entrée ou sortie.',
            'quantite.required'       => 'La quantité est obligatoire.',
            'quantite.integer'        => 'La quantité doit être un nombre entier.',
            'quantite.min'            => 'La quantité doit être d\'au moins 1.',
            'motif.required'          => 'Le motif du mouvement est obligatoire.',
            'date_mouvement.required' => 'La date du mouvement est obligatoire.',
            'date_mouvement.date'     => 'La date du mouvement n\'est pas valide.',
        ]);

        $mouvementStock->update([
            'produit_id'     => $validated['produit_id'],
            'user_id'        => $validated['user_id'],
            'type_mouvement' => $validated['type_mouvement'],
            'quantite'       => $validated['quantite'],
            'motif'          => $validated['motif'],
            'date_mouvement' => $validated['date_mouvement'],
        ]);

        return redirect()->route('mouvementStocks.index')
                         ->with('success', 'Mouvement de stock mis à jour avec succès.');
    }

    /**
     * Suppression d'un mouvement
     */
    public function destroy(MouvementStock $mouvementStock)
    {
        return redirect()->route('mouvementStocks.index')
                         ->with('error', 'La suppression des mouvements de stock est désactivée pour garantir l\'auditabilité des flux.');
    }
}
