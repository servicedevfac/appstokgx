<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Detail_Vente;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VenteController extends Controller
{
    /**
     * Vérification des permissions administrateur
     */
    private function checkAdminPermission(string $action = 'manage'): void
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Administrateur', 'super admin']) && !$user->can("{$action} vente")) {
            abort(403, 'Accès refusé. Seul un administrateur est autorisé à effectuer cette action.');
        }
    }

    /**
     * Liste des ventes avec filtres et recherche
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $ventes = Vente::with(['client', 'user'])->orderByDesc('date_vente');

        if ($user->hasRole('Gestionnaire')) {
            $ventes->where('user_id', $user->id);
        }

        // Filtrage par période
        if ($request->filled(['date_debut', 'date_fin'])) {
            $ventes->whereBetween('date_vente', [$request->date_debut, $request->date_fin]);
        }

        // Recherche textuelle
        if ($request->filled('q')) {
            $q = trim($request->q);
            $ventes->where(function ($query) use ($q) {
                $query->where('code_recu', 'like', "%{$q}%")
                    ->orWhereHas('client', function ($sub) use ($q) {
                        $sub->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%");
                    })
                    ->orWhereHas('user', function ($sub) use ($q) {
                        $sub->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%");
                    });
            });
        }

        $ventes = $ventes->paginate(20)->withQueryString();

        return view('admin.ventes.index', compact('ventes'));
    }

    /**
     * Formulaire de création d'une vente
     */
    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        $utilisateurs = User::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.ventes.create', compact('clients', 'utilisateurs', 'produits'));
    }

    /**
     * Calcul du stock actuel pour un produit
     */
    private function stockActuel(Produit $produit): int
    {
        $entrees = $produit->mouvements()->where('type_mouvement', 'entree')->sum('quantite');
        $sorties = $produit->mouvements()->where('type_mouvement', 'sortie')->sum('quantite');
        return (int) ($entrees - $sorties);
    }

    /**
     * Enregistrement d'une vente avec transaction atomique
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'montant_total'         => 'required|numeric|min:0',
            'montant_paye'          => 'nullable|numeric|min:0',
            'remise'                => 'nullable|numeric|min:0',
            'date_vente'            => 'required|date',
            'mode_paiement'         => 'required|string|max:50',
            'produits'              => 'required|array|min:1',
            'produits.*.produit_id' => 'required|exists:produits,id',
            'produits.*.quantite'   => 'required|integer|min:1',
            'produits.*.prix'       => 'required|numeric|min:0',
        ], [
            'client_id.required'             => 'Veuillez sélectionner un client.',
            'client_id.exists'               => 'Le client sélectionné n\'existe pas.',
            'montant_total.required'         => 'Le montant total est obligatoire.',
            'date_vente.required'            => 'La date de la vente est obligatoire.',
            'mode_paiement.required'         => 'Veuillez sélectionner un mode de paiement.',
            'produits.required'              => 'Veuillez ajouter au moins un produit à la vente.',
            'produits.min'                   => 'La vente doit contenir au moins un produit.',
            'produits.*.produit_id.required' => 'Le produit est obligatoire sur chaque ligne.',
            'produits.*.quantite.required'   => 'La quantité est obligatoire.',
            'produits.*.quantite.min'        => 'La quantité doit être d\'au moins 1.',
        ]);

        $montantTotal = (float) $validated['montant_total'];
        $montantPaye = (float) ($validated['montant_paye'] ?? 0);

        // Sécurité : l'acompte ne peut pas excéder le total
        if ($montantPaye > $montantTotal) {
            return back()->withInput()->with(
                'error',
                "L'acompte saisi (" . number_format($montantPaye, 0, ',', ' ') . " FCFA) dépasse le montant total de la vente (" . number_format($montantTotal, 0, ',', ' ') . " FCFA)."
            );
        }

        // Génération unique du code reçu
        $annee = now()->format('Y');
        $mois = now()->format('m');
        $dernierVente = Vente::whereYear('created_at', $annee)
            ->whereMonth('created_at', $mois)
            ->orderByDesc('id')
            ->first();

        $numero = 1;
        if ($dernierVente && preg_match('/RECU_\d{6}_(\d{4})/', $dernierVente->code_recu, $matches)) {
            $numero = intval($matches[1]) + 1;
        }

        $code_recu = 'RECU_' . $annee . $mois . '_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        while (Vente::where('code_recu', $code_recu)->exists()) {
            $numero++;
            $code_recu = 'RECU_' . $annee . $mois . '_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        }

        DB::beginTransaction();
        try {
            // Vérification de la disponibilité du stock pour TOUS les produits
            $erreursStock = [];
            foreach ($validated['produits'] as $p) {
                $prod = Produit::findOrFail($p['produit_id']);
                $dispo = $this->stockActuel($prod);
                if ((int) $p['quantite'] > $dispo) {
                    $erreursStock[] = "Stock insuffisant pour {$prod->nom} (Disponible: {$dispo}, Demandé: {$p['quantite']})";
                }
            }

            if (!empty($erreursStock)) {
                DB::rollBack();
                return back()->withInput()->withErrors($erreursStock);
            }

            $resteAPayer = max(0, $montantTotal - $montantPaye);
            $estPaye = ($resteAPayer <= 0);

            $vente = Vente::create([
                'client_id'     => $validated['client_id'],
                'user_id'       => Auth::id(),
                'date_vente'    => $validated['date_vente'],
                'montant_total' => $montantTotal,
                'remise'        => $validated['remise'] ?? 0,
                'mode_paiement' => $validated['mode_paiement'],
                'code_recu'     => $code_recu,
                'est_paye'      => $estPaye,
                'montant_paye'  => $montantPaye,
                'reste_a_payer' => $resteAPayer,
                'statut'        => 'valide',
            ]);

            // Enregistrement de l'acompte / paiement initial
            if ($montantPaye > 0) {
                Paiement::create([
                    'vente_id'      => $vente->id,
                    'montant'       => $montantPaye,
                    'mode_paiement' => $validated['mode_paiement'],
                    'date_paiement' => $validated['date_vente'],
                    'reste_a_payer' => $resteAPayer,
                ]);
            }

            // Création des lignes de détails et mouvements de sortie
            foreach ($validated['produits'] as $p) {
                $totalLigne = (int) $p['quantite'] * (float) $p['prix'];

                Detail_Vente::create([
                    'vente_id'   => $vente->id,
                    'produit_id' => $p['produit_id'],
                    'quantite'   => $p['quantite'],
                    'prix'       => $p['prix'],
                    'total'      => $totalLigne,
                    'est_paye'   => $estPaye,
                ]);

                MouvementStock::create([
                    'produit_id'     => $p['produit_id'],
                    'user_id'        => Auth::id(),
                    'quantite'       => $p['quantite'],
                    'motif'          => 'Vente #' . $vente->code_recu,
                    'type_mouvement' => 'sortie',
                    'date_mouvement' => $validated['date_vente'],
                    'vente_id'       => $vente->id,
                ]);
            }

            // Génération du reçu PDF
            $vente->load(['client', 'user', 'details.produit']);
            $pdf = Pdf::loadView('admin.ventes.recu_pdf', ['vente' => $vente]);
            $filename = 'recu_vente_' . ($vente->client->nom ?? 'client') . '_' . $vente->code_recu . '.pdf';
            Storage::put('public/recus/' . $filename, $pdf->output());
            $vente->update(['pdf_recu' => 'recus/' . $filename]);

            DB::commit();

            return redirect()
                ->route('ventes.index')
                ->with('success', 'Vente enregistrée avec succès.')
                ->with('recu_url', asset('storage/recus/' . $filename));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    /**
     * Détail d'une vente
     */
    public function show(Vente $vente)
    {
        $vente->load(['client', 'user', 'details.produit', 'paiements']);
        return view('admin.ventes.detail_vente', compact('vente'));
    }

    /**
     * Formulaire de modification d'une vente
     */
    public function edit(Vente $vente)
    {
        $this->checkAdminPermission('edit');

        $clients = Client::orderBy('nom')->get();
        $utilisateurs = User::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.ventes.edit', compact('vente', 'clients', 'utilisateurs', 'produits'));
    }

    /**
     * Mise à jour d'une vente
     */
    public function update(Request $request, Vente $vente)
    {
        $this->checkAdminPermission('edit');

        $validated = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'montant_total'  => 'required|numeric|min:0',
            'remise'         => 'nullable|numeric|min:0',
            'date_vente'     => 'required|date',
            'mode_paiement'  => 'required|string|max:50',
        ]);

        $vente->update([
            'client_id'     => $validated['client_id'],
            'montant_total' => $validated['montant_total'],
            'remise'        => $validated['remise'] ?? 0,
            'date_vente'    => $validated['date_vente'],
            'mode_paiement' => $validated['mode_paiement'],
        ]);

        return redirect()->route('ventes.index')->with('success', 'Vente mise à jour avec succès.');
    }

    /**
     * Suppression sécurisée d'une vente avec réajustement du stock
     */
    public function destroy(Vente $vente)
    {
        $this->checkAdminPermission('delete');

        DB::transaction(function () use ($vente) {
            // Si la vente n'était pas déjà annulée, restaurer le stock
            if ($vente->statut !== 'annulee') {
                foreach ($vente->details as $ligne) {
                    MouvementStock::create([
                        'produit_id'     => $ligne->produit_id,
                        'user_id'        => Auth::id(),
                        'quantite'       => $ligne->quantite,
                        'motif'          => 'Suppression de la vente #' . $vente->code_recu,
                        'type_mouvement' => 'entree',
                        'date_mouvement' => now()->format('Y-m-d'),
                    ]);
                }
            }

            // Nettoyer le fichier PDF généré
            if ($vente->pdf_recu && Storage::disk('public')->exists($vente->pdf_recu)) {
                Storage::disk('public')->delete($vente->pdf_recu);
            }

            $vente->details()->delete();
            $vente->paiements()->delete();
            $vente->delete();
        });

        return redirect()->route('ventes.index')->with('success', 'Vente supprimée avec succès et stock réajusté.');
    }

    /**
     * Annulation d'une vente et restauration intégrale du stock
     */
    public function annulerVente($id)
    {
        $this->checkAdminPermission('annuler');

        $vente = Vente::with('details')->findOrFail($id);

        if ($vente->statut === 'annulee') {
            return back()->with('error', 'Cette vente est déjà marquée comme annulée.');
        }

        DB::beginTransaction();
        try {
            $vente->update(['statut' => 'annulee']);

            foreach ($vente->details as $ligne) {
                MouvementStock::create([
                    'produit_id'     => $ligne->produit_id,
                    'user_id'        => Auth::id(),
                    'quantite'       => $ligne->quantite,
                    'motif'          => 'Annulation de la vente #' . $vente->code_recu,
                    'type_mouvement' => 'entree',
                    'date_mouvement' => now()->format('Y-m-d'),
                ]);
            }

            DB::commit();

            return redirect()->route('ventes.index')->with('success', 'Vente annulée avec succès et stock restitué.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Impression du ticket de vente
     */
    public function imprimerTicket($id)
    {
        $vente = Vente::with(['client', 'details.produit', 'user'])->findOrFail($id);

        $pdf = PDF::loadView('admin.ventes.recu_ticket', compact('vente'));

        return $pdf->stream('vente_' . $vente->code_recu . '.pdf');
    }

    /**
     * Règlement / Paiement direct sur une vente
     */
    public function payer(Request $request, $id)
    {
        $vente = Vente::findOrFail($id);

        if ($vente->est_paye || (float) $vente->reste_a_payer <= 0) {
            return redirect()->back()->with('error', 'Cette vente est déjà entièrement soldée.');
        }

        $validated = $request->validate([
            'mode_paiement' => 'required|string|max:50',
            'montant_paye'  => 'required|numeric|min:1',
        ], [
            'montant_paye.required' => 'Veuillez saisir un montant de règlement.',
            'montant_paye.numeric'  => 'Le montant doit être une valeur numérique.',
            'montant_paye.min'      => 'Le montant doit être au moins de 1 FCFA.',
            'mode_paiement.required'=> 'Veuillez sélectionner un mode de paiement.',
        ]);

        $montant = (float) $validated['montant_paye'];
        $resteActuel = (float) $vente->reste_a_payer;

        if ($montant > $resteActuel) {
            return redirect()->back()->withInput()->with(
                'error',
                "Paiement refusé : le montant saisi (" . number_format($montant, 0, ',', ' ') . " FCFA) dépasse le reste à payer (" . number_format($resteActuel, 0, ',', ' ') . " FCFA)."
            );
        }

        $nouveauMontantPaye = (float) $vente->montant_paye + $montant;
        $nouveauReste = max(0, (float) $vente->montant_total - $nouveauMontantPaye);

        $vente->paiements()->create([
            'montant'       => $montant,
            'mode_paiement' => $validated['mode_paiement'],
            'date_paiement' => now()->format('Y-m-d'),
            'reste_a_payer' => $nouveauReste,
        ]);

        $vente->montant_paye = $nouveauMontantPaye;
        $vente->reste_a_payer = $nouveauReste;
        $vente->mode_paiement = $validated['mode_paiement'];
        $vente->est_paye = ($nouveauReste <= 0);
        $vente->save();

        $message = "Paiement de " . number_format($montant, 0, ',', ' ') . " FCFA enregistré avec succès.";
        if ($nouveauReste > 0) {
            $message .= " Reste à payer : " . number_format($nouveauReste, 0, ',', ' ') . " FCFA.";
        } else {
            $message .= " La vente est désormais totalement soldée !";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Données graphiques filtrées (Ventes, Paiements, Reste)
     */
    public function ventesFiltrees(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $dateDebut = $request->date_debut ? Carbon::parse($request->date_debut)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateFin = $request->date_fin ? Carbon::parse($request->date_fin)->endOfDay() : Carbon::now()->endOfDay();

        $periode = $request->periode ?? 'jour';
        $driver = DB::connection()->getDriverName();

        switch ($periode) {
            case 'semaine':
                $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%W', ventes.date_vente)" : "YEARWEEK(ventes.date_vente, 1)";
                break;
            case 'mois':
                $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m', ventes.date_vente)" : "DATE_FORMAT(ventes.date_vente, '%Y-%m')";
                break;
            case 'annee':
                $dateExpr = $driver === 'sqlite' ? "strftime('%Y', ventes.date_vente)" : "YEAR(ventes.date_vente)";
                break;
            case 'jour':
            default:
                $dateExpr = $driver === 'sqlite' ? "DATE(ventes.date_vente)" : "DATE(ventes.date_vente)";
        }

        // 1. Ventes
        $ventesQuery = DB::table('ventes')
            ->select(DB::raw("$dateExpr as periode"), DB::raw("SUM(ventes.montant_total) as total"))
            ->whereBetween('ventes.date_vente', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->where('ventes.statut', 'valide');

        if ($user->hasRole('Gestionnaire')) {
            $ventesQuery->where('ventes.user_id', $userId);
        }

        $ventes = $ventesQuery->groupBy('periode')->orderBy('periode')->get()->keyBy('periode');

        // 2. Paiements
        $paiementsQuery = DB::table('paiements')
            ->join('ventes', 'paiements.vente_id', '=', 'ventes.id')
            ->select(DB::raw("$dateExpr as periode"), DB::raw("SUM(paiements.montant) as total"))
            ->whereBetween('paiements.created_at', [$dateDebut, $dateFin])
            ->where('ventes.statut', 'valide');

        if ($user->hasRole('Gestionnaire')) {
            $paiementsQuery->where('ventes.user_id', $userId);
        }

        $paiements = $paiementsQuery->groupBy('periode')->orderBy('periode')->get()->keyBy('periode');

        // 3. Synthèse
        $allPeriods = $ventes->keys()->merge($paiements->keys())->unique()->sort()->values();

        $data = [
            'labels'    => [],
            'ventes'    => [],
            'paiements' => [],
            'reste'     => [],
        ];

        foreach ($allPeriods as $pKey) {
            $totalVentes = $ventes->has($pKey) ? (float) $ventes[$pKey]->total : 0;
            $totalPaiements = $paiements->has($pKey) ? (float) $paiements[$pKey]->total : 0;
            $reste = max(0, $totalVentes - $totalPaiements);

            $data['labels'][] = $pKey;
            $data['ventes'][] = round($totalVentes, 2);
            $data['paiements'][] = round($totalPaiements, 2);
            $data['reste'][] = round($reste, 2);
        }

        return response()->json($data);
    }

    /**
     * Alias pour la route ventes-filtrees1
     */
    public function ventesFiltrees1(Request $request)
    {
        return $this->ventesFiltrees($request);
    }

    /**
     * Exportation PDF de la liste des ventes
     */
    public function exportPdflist(Request $request)
    {
        $user = Auth::user();

        $ventes = Vente::with(['client', 'user'])->where('statut', 'valide');

        if ($user->hasRole('Gestionnaire')) {
            $ventes->where('user_id', $user->id);
        }

        if ($request->filled(['date_debut', 'date_fin'])) {
            $ventes->whereBetween('date_vente', [$request->date_debut, $request->date_fin]);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $ventes->where(function ($query) use ($q) {
                $query->where('code_recu', 'like', "%{$q}%")
                    ->orWhereHas('client', function ($sub) use ($q) {
                        $sub->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%");
                    })
                    ->orWhereHas('user', function ($sub) use ($q) {
                        $sub->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%");
                    });
            });
        }

        $ventes = $ventes->orderByDesc('created_at')->get();

        $pdf = PDF::loadView('admin.ventes.pdf', [
            'ventes'  => $ventes,
            'request' => $request,
        ]);

        return $pdf->download('ventes_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Alias pour la route ventes-export-pdf
     */
    public function exportPDF(Request $request)
    {
        return $this->exportPdflist($request);
    }
}
