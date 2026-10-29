<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\StockAlerte;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MouvementStockController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} stock")) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission d\'effectuer cette action sur le stock.');
        }
    }

    private function ensureSameEntreprise(MouvementStock $mouvement): void
    {
        $user = Auth::user();
        $currentId = EntrepriseContext::currentId($user);
        if (! $user->canAccessEntreprise((int) $mouvement->entreprise_id) || ($currentId && (int) $mouvement->entreprise_id !== (int) $currentId)) {
            abort(403, 'Accès interdit au mouvement de stock d\'une autre entreprise.');
        }
    }

    public function index()
    {
        $this->checkPermission('view');

        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        $mouvements = MouvementStock::with(['produit', 'user'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.stocks.index', compact('mouvements', 'produits', 'users'));
    }

    public function create()
    {
        $this->checkPermission('create');

        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        return view('admin.stocks.create', compact('produits', 'users'));
    }

    public function store(Request $request)
    {
        $this->checkPermission('create');

        $validated = $request->validate([
            'produit_id'        => 'required|exists:produits,id',
            'type_mouvement'    => 'required|in:entree,sortie',
            'quantite'          => 'required|integer|min:1',
            'motif'             => 'required|string|max:255',
            'date_mouvement'    => 'required|date',
            'reference_externe' => 'nullable|string|max:100',
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

        if (! empty($validated['reference_externe'])) {
            if (MouvementStock::withoutGlobalScope('entreprise')->where('reference_externe', $validated['reference_externe'])->exists()) {
                return back()->withInput()->with('error', 'Ce mouvement de stock a déjà été enregistré (doublon détecté).');
            }
        }

        $user = Auth::user();
        $entrepriseId = EntrepriseContext::currentId($user);

        DB::beginTransaction();
        try {
            $produit = Produit::withoutGlobalScope('entreprise')->lockForUpdate()->findOrFail($validated['produit_id']);
            if ($entrepriseId && (int) $produit->entreprise_id !== (int) $entrepriseId) {
                DB::rollBack();
                abort(403, 'Ce produit n\'appartient pas à votre entreprise.');
            }
            $entrepriseId = $entrepriseId ?? $produit->entreprise_id;

            if ($validated['type_mouvement'] === 'sortie' && (int) $validated['quantite'] > $produit->stock_actuel) {
                DB::rollBack();
                return back()->withInput()->with(
                    'error',
                    "Sortie impossible : la quantité demandée ({$validated['quantite']}) dépasse le stock actuel ({$produit->stock_actuel})."
                );
            }

            $mouvement = MouvementStock::create([
                'entreprise_id'     => $entrepriseId,
                'produit_id'        => $produit->id,
                'user_id'           => Auth::id(),
                'type_mouvement'    => $validated['type_mouvement'],
                'quantite'          => $validated['quantite'],
                'motif'             => $validated['motif'],
                'date_mouvement'    => $validated['date_mouvement'],
                'vente_id'          => null,
                'reference_externe' => $validated['reference_externe'] ?? null,
            ]);

            AuditService::log(
                'stock.mouvement_manuel',
                $mouvement,
                $mouvement->id,
                [
                    'produit_id'     => $produit->id,
                    'type_mouvement' => $validated['type_mouvement'],
                    'quantite'       => $validated['quantite'],
                ],
                $validated['motif'],
                (int) $entrepriseId
            );

            DB::commit();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $httpEx) {
            DB::rollBack();
            throw $httpEx;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }

        // Gestion de l'alerte stock faible
        if ($produit->stock_actuel <= $produit->seuil_alerte && ! $produit->alerte_envoyee) {
            try {
                $produitsFaibles = Produit::where('entreprise_id', $entrepriseId)->get()
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
                report($e);
            }
        }

        return redirect()->route('mouvementStocks.index')
                         ->with('success', 'Mouvement de stock enregistré avec succès.');
    }

    public function show($id)
    {
        $this->checkPermission('view');
        $mouvementStock = MouvementStock::withoutGlobalScope('entreprise')->with(['produit', 'user'])->findOrFail($id);
        $this->ensureSameEntreprise($mouvementStock);

        return view('admin.stocks.show', compact('mouvementStock'));
    }

    public function edit($id)
    {
        $this->checkPermission('edit');
        $mouvementStock = MouvementStock::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($mouvementStock);

        $produits = Produit::orderBy('nom')->get();
        $users = User::orderBy('nom')->get();

        return view('admin.stocks.edit', compact('mouvementStock', 'produits', 'users'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');
        $mouvementStock = MouvementStock::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($mouvementStock);

        $validated = $request->validate([
            'produit_id'     => 'required|exists:produits,id',
            'user_id'        => 'required|exists:users,id',
            'type_mouvement' => 'required|in:entree,sortie',
            'quantite'       => 'required|integer|min:1',
            'motif'          => 'required|string|max:255',
            'date_mouvement' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $produit = Produit::withoutGlobalScope('entreprise')->lockForUpdate()->findOrFail($validated['produit_id']);
            if ((int) $produit->entreprise_id !== (int) $mouvementStock->entreprise_id) {
                DB::rollBack();
                abort(403, 'Ce produit n\'appartient pas à la même entreprise.');
            }

            $mouvementStock->update([
                'produit_id'     => $validated['produit_id'],
                'user_id'        => $validated['user_id'],
                'type_mouvement' => $validated['type_mouvement'],
                'quantite'       => $validated['quantite'],
                'motif'          => $validated['motif'],
                'date_mouvement' => $validated['date_mouvement'],
            ]);

            if ($produit->stock_actuel < 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Modification refusée : cette opération rendrait le stock du produit négatif.');
            }

            AuditService::log(
                'stock.mouvement_modification',
                $mouvementStock,
                $mouvementStock->id,
                $validated,
                $validated['motif'],
                (int) $mouvementStock->entreprise_id
            );

            DB::commit();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $httpEx) {
            DB::rollBack();
            throw $httpEx;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }

        return redirect()->route('mouvementStocks.index')
                         ->with('success', 'Mouvement de stock mis à jour avec succès.');
    }

    public function destroy($id)
    {
        return redirect()->route('mouvementStocks.index')
                         ->with('error', 'La suppression des mouvements de stock est désactivée pour garantir l\'auditabilité des flux.');
    }
}
