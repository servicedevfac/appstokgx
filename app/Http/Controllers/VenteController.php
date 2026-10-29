<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Detail_vente;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\RetourClient;
use App\Models\User;
use App\Models\Vente;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VenteController extends Controller
{
    private function checkPermission(string $action = 'view'): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && ! $user->can("{$action} vente")) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission d\'effectuer cette opération sur les ventes.');
        }
    }

    private function ensureSameEntreprise(Vente $vente): void
    {
        $user = Auth::user();
        if (! $user->canAccessEntreprise((int) $vente->entreprise_id)) {
            abort(403, 'Accès interdit aux ventes d\'une autre entreprise.');
        }

        $currentEntrepriseId = EntrepriseContext::currentId($user);
        if ($currentEntrepriseId && (int) $vente->entreprise_id !== (int) $currentEntrepriseId) {
            abort(403, 'Cette vente n\'appartient pas à l\'entreprise active.');
        }

        // Un vendeur / gestionnaire non admin ne peut consulter/modifier que les ventes autorisées
        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin']) && (int) $vente->user_id !== (int) $user->id) {
            abort(403, 'Accès refusé : vous ne pouvez accéder qu\'à vos propres ventes.');
        }
    }

    /**
     * Liste et journal des ventes avec filtres complets (Section 6.4)
     */
    public function index(Request $request)
    {
        $this->checkPermission('view');
        $user = Auth::user();

        $ventes = Vente::with(['client', 'user', 'retours'])->orderByDesc('date_vente')->orderByDesc('id');

        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])) {
            $ventes->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $ventes->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('client_id')) {
            $ventes->where('client_id', (int) $request->client_id);
        }

        if ($request->filled('statut')) {
            $ventes->where('statut', $request->statut);
        }

        if ($request->filled('statut_paiement')) {
            $ventes->where('statut_paiement', $request->statut_paiement);
        }

        if ($request->filled('date_debut')) {
            $ventes->whereDate('date_vente', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $ventes->whereDate('date_vente', '<=', $request->date_fin);
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

        $ventes = $ventes->paginate(20)->withQueryString();

        return view('admin.ventes.index', compact('ventes'));
    }

    public function create()
    {
        $this->checkPermission('create');

        $clients = Client::orderBy('nom')->get();
        $utilisateurs = User::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.ventes.create', compact('clients', 'utilisateurs', 'produits'));
    }

    private function stockActuel(Produit $produit): int
    {
        $entrees = MouvementStock::withoutGlobalScope('entreprise')
            ->where('produit_id', $produit->id)
            ->where('type_mouvement', 'entree')
            ->sum('quantite');
        $sorties = MouvementStock::withoutGlobalScope('entreprise')
            ->where('produit_id', $produit->id)
            ->where('type_mouvement', 'sortie')
            ->sum('quantite');

        return (int) ($entrees - $sorties);
    }

    /**
     * Enregistrement d'une vente avec calcul serveur strict et transaction atomique (RM-035 à RM-045)
     */
    public function store(Request $request)
    {
        $this->checkPermission('create');

        $validated = $request->validate([
            'client_id'             => 'required|integer|exists:clients,id',
            'montant_paye'          => 'nullable|numeric|min:0',
            'remise'                => 'nullable|numeric|min:0',
            'date_vente'            => 'required|date',
            'date_echeance'         => 'nullable|date|after_or_equal:date_vente',
            'mode_paiement'         => 'required|string|max:50',
            'idempotency_key'       => 'nullable|string|max:100',
            'produits'              => 'required|array|min:1',
            'produits.*.produit_id' => 'required|integer|exists:produits,id',
            'produits.*.quantite'   => 'required|integer|min:1',
        ], [
            'client_id.required'             => 'Veuillez sélectionner un client.',
            'client_id.exists'               => 'Le client sélectionné n\'existe pas.',
            'date_vente.required'            => 'La date de la vente est obligatoire.',
            'mode_paiement.required'         => 'Veuillez sélectionner un mode de paiement.',
            'produits.required'              => 'Veuillez ajouter au moins un produit à la vente.',
            'produits.min'                   => 'La vente doit contenir au moins un produit.',
            'produits.*.produit_id.required' => 'Le produit est obligatoire sur chaque ligne.',
            'produits.*.quantite.required'   => 'La quantité est obligatoire.',
            'produits.*.quantite.min'        => 'La quantité doit être d\'au moins 1.',
        ]);

        $user = Auth::user();
        $entrepriseId = EntrepriseContext::currentId($user);

        // Protection anti-double soumission par idempotency_key
        if (! empty($validated['idempotency_key'])) {
            $existing = Vente::withoutGlobalScope('entreprise')
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();
            if ($existing) {
                return redirect()->route('ventes.index')->with('error', 'Cette vente a déjà été enregistrée (double soumission détectée).');
            }
        }

        // Vérifier que le client appartient bien à l'entreprise active
        $client = Client::withoutGlobalScope('entreprise')->findOrFail($validated['client_id']);
        if ($entrepriseId && (int) $client->entreprise_id !== (int) $entrepriseId) {
            abort(403, 'Le client sélectionné n\'appartient pas à votre entreprise.');
        }
        $entrepriseId = $entrepriseId ?? $client->entreprise_id;

        DB::beginTransaction();
        try {
            // Agréger les quantités par produit (au cas où un même produit est présent sur plusieurs lignes)
            $quantitesParProduit = [];
            foreach ($validated['produits'] as $p) {
                $pid = (int) $p['produit_id'];
                $quantitesParProduit[$pid] = ($quantitesParProduit[$pid] ?? 0) + (int) $p['quantite'];
            }

            // Verrouiller les produits en base pour éviter les ventes concurrentes sur le même stock
            $produitsEnBase = Produit::withoutGlobalScope('entreprise')
                ->whereIn('id', array_keys($quantitesParProduit))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $erreursStock = [];
            foreach ($quantitesParProduit as $pid => $qteTotaleDemandee) {
                $prod = $produitsEnBase->get($pid);
                if (! $prod || ($entrepriseId && (int) $prod->entreprise_id !== (int) $entrepriseId)) {
                    DB::rollBack();
                    abort(403, 'Un des produits sélectionnés n\'appartient pas à votre entreprise.');
                }

                $dispo = $this->stockActuel($prod);
                if ($qteTotaleDemandee > $dispo) {
                    $erreursStock[] = "Stock insuffisant pour {$prod->nom} (Disponible: {$dispo}, Demandé: {$qteTotaleDemandee})";
                }
            }

            if (! empty($erreursStock)) {
                DB::rollBack();
                return back()->withInput()->withErrors($erreursStock);
            }

            // Calcul strict des montants côté serveur à partir du prix officiel en base (RM-035, RM-036)
            $sousTotalServeur = 0.0;
            $lignesPreparees = [];
            foreach ($validated['produits'] as $p) {
                $prod = $produitsEnBase->get((int) $p['produit_id']);
                $qte = (int) $p['quantite'];
                $prixUnitaireOfficiel = (float) $prod->prix;
                $coutAchatUnitaire = (float) ($prod->prix_achat ?? 0);
                $totalLigne = $qte * $prixUnitaireOfficiel;

                $sousTotalServeur += $totalLigne;
                $lignesPreparees[] = [
                    'produit_id'          => $prod->id,
                    'quantite'            => $qte,
                    'prix'                => $prixUnitaireOfficiel,
                    'cout_achat_unitaire' => $coutAchatUnitaire,
                    'total'               => $totalLigne,
                ];
            }

            $remise = (float) ($validated['remise'] ?? 0);
            if ($remise > $sousTotalServeur) {
                DB::rollBack();
                return back()->withInput()->with('error', 'La remise ne peut pas dépasser le montant total des articles.');
            }

            $montantTotal = max(0.0, $sousTotalServeur - $remise);
            $montantInitial = $montantTotal;

            // Détermination du montant payé
            if ($request->filled('montant_paye')) {
                $montantPaye = (float) $validated['montant_paye'];
            } elseif (mb_strtolower($validated['mode_paiement']) === 'crédit' || mb_strtolower($validated['mode_paiement']) === 'credit') {
                $montantPaye = 0.0;
            } elseif ($request->has('est_paye') && ! $request->boolean('est_paye')) {
                $montantPaye = 0.0;
            } else {
                $montantPaye = $montantTotal;
            }

            if ($montantPaye > $montantTotal) {
                DB::rollBack();
                return back()->withInput()->with(
                    'error',
                    "L'acompte saisi (" . number_format($montantPaye, 0, ',', ' ') . " FCFA) dépasse le montant total de la vente (" . number_format($montantTotal, 0, ',', ' ') . " FCFA)."
                );
            }

            $resteAPayer = max(0.0, $montantTotal - $montantPaye);
            $estPaye = ($resteAPayer <= 0);
            $statutPaiement = $estPaye ? 'paye' : ($montantPaye > 0 ? 'partiel' : 'impaye');

            // Génération unique du code reçu par entreprise
            $annee = now()->format('Y');
            $mois = now()->format('m');
            $dernierVente = Vente::withoutGlobalScope('entreprise')
                ->where('entreprise_id', $entrepriseId)
                ->whereYear('created_at', $annee)
                ->whereMonth('created_at', $mois)
                ->orderByDesc('id')
                ->first();

            $numero = 1;
            if ($dernierVente && preg_match('/RECU_\d{6}_(\d{4})/', (string) $dernierVente->code_recu, $matches)) {
                $numero = intval($matches[1]) + 1;
            }

            $code_recu = 'RECU_' . $annee . $mois . '_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            while (Vente::withoutGlobalScope('entreprise')->where('entreprise_id', $entrepriseId)->where('code_recu', $code_recu)->exists()) {
                $numero++;
                $code_recu = 'RECU_' . $annee . $mois . '_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }

            $vente = Vente::create([
                'entreprise_id'   => $entrepriseId,
                'client_id'       => $client->id,
                'user_id'         => Auth::id(),
                'date_vente'      => $validated['date_vente'],
                'date_echeance'   => $validated['date_echeance'] ?? null,
                'montant_initial' => $montantInitial,
                'montant_total'   => $montantTotal,
                'remise'          => $remise,
                'mode_paiement'   => $validated['mode_paiement'],
                'code_recu'       => $code_recu,
                'est_paye'        => $estPaye,
                'statut_paiement' => $statutPaiement,
                'montant_paye'    => $montantPaye,
                'reste_a_payer'   => $resteAPayer,
                'statut'          => 'valide',
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ]);

            if ($montantPaye > 0) {
                $paiement = Paiement::create([
                    'entreprise_id'  => $entrepriseId,
                    'vente_id'       => $vente->id,
                    'user_id'        => Auth::id(),
                    'reference'      => 'PAY-' . $vente->code_recu . '-01-' . strtoupper(Str::random(4)),
                    'montant'        => $montantPaye,
                    'mode_paiement'  => $validated['mode_paiement'],
                    'type_operation' => 'encaissement',
                    'date_paiement'  => $validated['date_vente'],
                    'reste_a_payer'  => $resteAPayer,
                ]);

                AuditService::log(
                    'paiement.enregistrement',
                    $paiement,
                    $paiement->id,
                    ['vente_id' => $vente->id, 'montant' => $montantPaye, 'reste_a_payer' => $resteAPayer],
                    'Paiement initial à la création de la vente #' . $vente->code_recu,
                    $entrepriseId
                );
            }

            foreach ($lignesPreparees as $ligne) {
                Detail_vente::create([
                    'vente_id'            => $vente->id,
                    'produit_id'          => $ligne['produit_id'],
                    'quantite'            => $ligne['quantite'],
                    'prix'                => $ligne['prix'],
                    'cout_achat_unitaire' => $ligne['cout_achat_unitaire'],
                    'total'               => $ligne['total'],
                ]);

                MouvementStock::create([
                    'entreprise_id'  => $entrepriseId,
                    'produit_id'     => $ligne['produit_id'],
                    'user_id'        => Auth::id(),
                    'quantite'       => $ligne['quantite'],
                    'motif'          => 'Vente #' . $vente->code_recu,
                    'type_mouvement' => 'sortie',
                    'date_mouvement' => $validated['date_vente'],
                    'vente_id'       => $vente->id,
                ]);
            }

            AuditService::log(
                'vente.validation',
                $vente,
                $vente->id,
                [
                    'code_recu'       => $vente->code_recu,
                    'montant_initial' => $montantInitial,
                    'montant_total'   => $montantTotal,
                    'remise'          => $remise,
                    'montant_paye'    => $montantPaye,
                    'reste_a_payer'   => $resteAPayer,
                    'statut_paiement' => $statutPaiement,
                ],
                'Validation de la vente #' . $vente->code_recu,
                $entrepriseId
            );

            // Génération du reçu PDF (tolérante en environnement de test minimal)
            $filename = 'recu_vente_' . ($vente->client->nom ?? 'client') . '_' . $vente->code_recu . '.pdf';
            try {
                $vente->load(['client', 'user', 'details.produit']);
                $pdf = Pdf::loadView('admin.ventes.recu_pdf', ['vente' => $vente]);
                Storage::put('public/recus/' . $filename, $pdf->output());
                $vente->update(['pdf_recu' => 'recus/' . $filename]);
            } catch (\Throwable $pdfException) {
                report($pdfException);
            }

            DB::commit();

            return redirect()
                ->route('ventes.index')
                ->with('success', 'Vente enregistrée avec succès.')
                ->with('recu_url', asset('storage/recus/' . $filename));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $httpEx) {
            DB::rollBack();
            throw $httpEx;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function show(Vente $vente)
    {
        $this->checkPermission('view');
        $this->ensureSameEntreprise($vente);

        $vente->load(['client', 'user', 'details.produit', 'paiements', 'retours.produit']);
        return view('admin.ventes.detail_vente', compact('vente'));
    }

    public function edit(Vente $vente)
    {
        $this->checkPermission('edit');
        $this->ensureSameEntreprise($vente);

        $clients = Client::orderBy('nom')->get();
        $utilisateurs = User::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.ventes.edit', compact('vente', 'clients', 'utilisateurs', 'produits'));
    }

    public function update(Request $request, Vente $vente)
    {
        $this->checkPermission('edit');
        $this->ensureSameEntreprise($vente);

        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'date_vente'    => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_vente',
            'mode_paiement' => 'required|string|max:50',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $vente->update([
            'client_id'     => $client->id,
            'date_vente'    => $validated['date_vente'],
            'date_echeance' => $validated['date_echeance'] ?? $vente->date_echeance,
            'mode_paiement' => $validated['mode_paiement'],
        ]);

        return redirect()->route('ventes.index')->with('success', 'Vente mise à jour avec succès.');
    }

    /**
     * Interdiction de la suppression définitive d'une vente validée (Section 6.5 & 12)
     */
    public function destroy(Vente $vente)
    {
        $this->checkPermission('delete');
        $this->ensureSameEntreprise($vente);

        return redirect()->route('ventes.index')->with(
            'error',
            'La suppression définitive d\'une vente validée est interdite afin de préserver l\'intégrité de l\'historique financier et commercial. Veuillez utiliser la procédure d\'annulation traçable.'
        );
    }

    /**
     * Annulation traçable d'une vente (Section 6.5)
     */
    public function annulerVente(Request $request, $id)
    {
        $this->checkPermission('annuler');

        $vente = Vente::withoutGlobalScope('entreprise')->with('details')->findOrFail($id);
        $this->ensureSameEntreprise($vente);

        if ($vente->statut === 'annulee') {
            return back()->with('error', 'Cette vente est déjà marquée comme annulée.');
        }

        $motif = trim((string) $request->input('motif', 'Annulation administrative de la vente #' . $vente->code_recu));
        $marchandiseRecuperee = $request->has('marchandise_recuperee')
            ? $request->boolean('marchandise_recuperee')
            : true;
        $montantRembourse = (float) $request->input('montant_rembourse', $vente->montant_paye);
        $montantRembourse = min(max(0.0, $montantRembourse), (float) $vente->montant_paye);

        DB::beginTransaction();
        try {
            $lockedVente = Vente::withoutGlobalScope('entreprise')->lockForUpdate()->findOrFail($vente->id);
            if ($lockedVente->statut === 'annulee') {
                DB::rollBack();
                return back()->with('error', 'Cette vente est déjà marquée comme annulée.');
            }

            $lockedVente->update([
                'statut'                => 'annulee',
                'statut_paiement'       => 'annulee',
                'reste_a_payer'         => 0,
                'motif_annulation'      => $motif,
                'annule_par'            => Auth::id(),
                'annule_at'             => now(),
                'marchandise_recuperee' => $marchandiseRecuperee,
                'montant_rembourse'     => $montantRembourse,
            ]);

            // Réintégration en stock uniquement si la marchandise est réellement récupérée
            if ($marchandiseRecuperee) {
                foreach ($vente->details as $ligne) {
                    MouvementStock::create([
                        'entreprise_id'  => $vente->entreprise_id,
                        'produit_id'     => $ligne->produit_id,
                        'user_id'        => Auth::id(),
                        'vente_id'       => $vente->id,
                        'quantite'       => $ligne->quantite,
                        'motif'          => 'Annulation de la vente #' . $vente->code_recu . ' (' . $motif . ')',
                        'type_mouvement' => 'entree',
                        'date_mouvement' => now()->format('Y-m-d'),
                    ]);
                }
            }

            if ($montantRembourse > 0) {
                Paiement::create([
                    'entreprise_id'  => $vente->entreprise_id,
                    'vente_id'       => $vente->id,
                    'user_id'        => Auth::id(),
                    'reference'      => 'REMB-ANN-' . $vente->code_recu . '-' . strtoupper(Str::random(4)),
                    'montant'        => $montantRembourse,
                    'reste_a_payer'  => 0,
                    'mode_paiement'  => $vente->mode_paiement,
                    'type_operation' => 'remboursement_client',
                    'date_paiement'  => now()->format('Y-m-d'),
                ]);
            }

            AuditService::log(
                'vente.annulation',
                $lockedVente,
                $lockedVente->id,
                [
                    'code_recu'             => $lockedVente->code_recu,
                    'marchandise_recuperee' => $marchandiseRecuperee,
                    'montant_rembourse'     => $montantRembourse,
                ],
                $motif,
                (int) $lockedVente->entreprise_id
            );

            DB::commit();

            return redirect()->route('ventes.index')->with('success', 'Vente annulée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrement d'un retour client distinct (Section 6.5 - RM-043)
     */
    public function enregistrerRetour(Request $request, $id)
    {
        $this->checkPermission('retourner');

        $vente = Vente::withoutGlobalScope('entreprise')->with(['details', 'retours'])->findOrFail($id);
        $this->ensureSameEntreprise($vente);

        if ($vente->statut === 'annulee') {
            return back()->with('error', 'Impossible d\'enregistrer un retour sur une vente annulée.');
        }

        $validated = $request->validate([
            'produit_id'        => 'required|integer|exists:produits,id',
            'quantite'          => 'required|integer|min:1',
            'reintegrer_stock'  => 'nullable|boolean',
            'etat_marchandise'  => 'nullable|string|in:bon_etat,endommage,non_reintegrable',
            'montant_rembourse' => 'nullable|numeric|min:0',
            'motif'             => 'required|string|max:255',
            'date_retour'       => 'nullable|date',
        ]);

        $detailLigne = $vente->details->firstWhere('produit_id', (int) $validated['produit_id']);
        if (! $detailLigne) {
            return back()->with('error', 'Ce produit ne figure pas dans la vente sélectionnée.');
        }

        $dejaRetourne = (int) $vente->retours
            ->where('produit_id', (int) $validated['produit_id'])
            ->where('statut', 'valide')
            ->sum('quantite');

        $qteRetour = (int) $validated['quantite'];
        if (($dejaRetourne + $qteRetour) > (int) $detailLigne->quantite) {
            return back()->with('error', 'La quantité retournée dépasse la quantité vendue.');
        }

        $etatMarchandise = $validated['etat_marchandise'] ?? 'bon_etat';
        // RM-043 : Les produits retournés ne réintègrent le stock que si leur état et leur récupération le permettent
        $reintegrerStock = $request->boolean('reintegrer_stock', false) && $etatMarchandise === 'bon_etat';

        $prixUnitaire = (float) $detailLigne->prix;
        $montantRetour = $qteRetour * $prixUnitaire;

        DB::beginTransaction();
        try {
            $lockedVente = Vente::withoutGlobalScope('entreprise')->lockForUpdate()->findOrFail($vente->id);

            $nouveauMontantTotal = max(0.0, (float) $lockedVente->montant_total - $montantRetour);
            // Montant remboursable uniquement si le client a déjà payé au-delà du nouveau montant total
            $montantRemboursable = max(0.0, (float) $lockedVente->montant_paye - $nouveauMontantTotal);
            $montantRembourseEffectif = isset($validated['montant_rembourse'])
                ? min((float) $validated['montant_rembourse'], $montantRemboursable)
                : 0.0;

            $retour = RetourClient::create([
                'entreprise_id'        => $lockedVente->entreprise_id,
                'vente_id'             => $lockedVente->id,
                'produit_id'           => $detailLigne->produit_id,
                'user_id'              => Auth::id(),
                'quantite'             => $qteRetour,
                'prix_unitaire'        => $prixUnitaire,
                'montant_retour'       => $montantRetour,
                'reintegrer_stock'     => $reintegrerStock,
                'etat_marchandise'     => $etatMarchandise,
                'montant_remboursable' => $montantRemboursable,
                'montant_rembourse'    => $montantRembourseEffectif,
                'motif'                => $validated['motif'],
                'date_retour'          => $validated['date_retour'] ?? now()->format('Y-m-d'),
                'statut'               => 'valide',
            ]);

            if ($reintegrerStock) {
                MouvementStock::create([
                    'entreprise_id'  => $lockedVente->entreprise_id,
                    'produit_id'     => $detailLigne->produit_id,
                    'user_id'        => Auth::id(),
                    'vente_id'       => $lockedVente->id,
                    'quantite'       => $qteRetour,
                    'type_mouvement' => 'entree',
                    'date_mouvement' => $validated['date_retour'] ?? now()->format('Y-m-d'),
                    'motif'          => 'Retour client sur vente #' . $lockedVente->code_recu . ' (' . $validated['motif'] . ')',
                ]);
            }

            $nouveauPayeNet = max(0.0, (float) $lockedVente->montant_paye - $montantRembourseEffectif);
            $nouveauReste = max(0.0, $nouveauMontantTotal - $nouveauPayeNet);
            $estPaye = ($nouveauReste <= 0);
            $statutPaiement = $estPaye ? 'paye' : ($nouveauPayeNet > 0 ? 'partiel' : 'impaye');

            // On conserve montant_initial intact !
            $lockedVente->update([
                'montant_total'     => $nouveauMontantTotal,
                'montant_paye'      => $nouveauPayeNet,
                'reste_a_payer'     => $nouveauReste,
                'est_paye'          => $estPaye,
                'statut_paiement'   => $statutPaiement,
                'montant_rembourse' => (float) $lockedVente->montant_rembourse + $montantRembourseEffectif,
            ]);

            AuditService::log(
                'vente.retour_client',
                $retour,
                $retour->id,
                [
                    'vente_id'          => $lockedVente->id,
                    'produit_id'        => $detailLigne->produit_id,
                    'quantite'          => $qteRetour,
                    'montant_retour'    => $montantRetour,
                    'reintegrer_stock'  => $reintegrerStock,
                    'etat_marchandise'  => $etatMarchandise,
                    'montant_rembourse' => $montantRembourseEffectif,
                ],
                $validated['motif'],
                (int) $lockedVente->entreprise_id
            );

            DB::commit();

            return back()->with('success', 'Retour client enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retour client : ' . $e->getMessage());
        }
    }

    public function imprimerTicket($id)
    {
        $this->checkPermission('view');
        $vente = Vente::withoutGlobalScope('entreprise')->with(['client', 'details.produit', 'user'])->findOrFail($id);
        $this->ensureSameEntreprise($vente);

        $pdf = PDF::loadView('admin.ventes.recu_ticket', compact('vente'));

        return $pdf->stream('vente_' . $vente->code_recu . '.pdf');
    }

    /**
     * Règlement / Paiement sur une vente avec protection anti-doublon et verrou (Section 6.3 & 6.6)
     */
    public function payer(Request $request, $id)
    {
        $this->checkPermission('view');

        $vente = Vente::withoutGlobalScope('entreprise')->findOrFail($id);
        $this->ensureSameEntreprise($vente);

        if ($vente->statut === 'annulee') {
            return redirect()->back()->with('error', 'Impossible d\'enregistrer un paiement sur une vente annulée.');
        }

        // Unifier les champs montant_paye et montant
        if (! $request->filled('montant_paye') && $request->filled('montant')) {
            $request->merge(['montant_paye' => $request->input('montant')]);
        }

        $validated = $request->validate([
            'mode_paiement' => 'required|string|max:50',
            'montant_paye'  => 'required|numeric|min:1',
            'reference'     => 'nullable|string|max:100',
        ], [
            'montant_paye.required'  => 'Veuillez saisir un montant de règlement.',
            'montant_paye.numeric'   => 'Le montant doit être une valeur numérique.',
            'montant_paye.min'       => 'Le montant doit être au moins de 1 FCFA.',
            'mode_paiement.required' => 'Veuillez sélectionner un mode de paiement.',
        ]);

        // Vérification anti-doublon par référence explicite
        if (! empty($validated['reference'])) {
            if (Paiement::withoutGlobalScope('entreprise')->where('reference', $validated['reference'])->exists()) {
                return redirect()->back()->with('error', 'Paiement refusé : cette référence de paiement a déjà été enregistrée (doublon détecté).');
            }
        }

        DB::beginTransaction();
        try {
            $lockedVente = Vente::withoutGlobalScope('entreprise')->lockForUpdate()->findOrFail($vente->id);

            if ($lockedVente->est_paye || (float) $lockedVente->reste_a_payer <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Cette vente est déjà entièrement soldée.');
            }

            $montant = (float) $validated['montant_paye'];
            $resteActuel = (float) $lockedVente->reste_a_payer;

            if ($montant > $resteActuel) {
                DB::rollBack();
                return redirect()->back()->withInput()->with(
                    'error',
                    "Paiement refusé : le montant saisi (" . number_format($montant, 0, ',', ' ') . " FCFA) dépasse le reste à payer (" . number_format($resteActuel, 0, ',', ' ') . " FCFA)."
                );
            }

            $nouveauMontantPaye = (float) $lockedVente->montant_paye + $montant;
            $nouveauReste = max(0.0, (float) $lockedVente->montant_total - $nouveauMontantPaye);
            $estPaye = ($nouveauReste <= 0);
            $statutPaiement = $estPaye ? 'paye' : 'partiel';

            $reference = ! empty($validated['reference'])
                ? $validated['reference']
                : 'PAY-' . $lockedVente->code_recu . '-' . now()->format('His') . '-' . strtoupper(Str::random(4));

            $paiement = Paiement::create([
                'entreprise_id'  => $lockedVente->entreprise_id,
                'vente_id'       => $lockedVente->id,
                'user_id'        => Auth::id(),
                'reference'      => $reference,
                'montant'        => $montant,
                'mode_paiement'  => $validated['mode_paiement'],
                'type_operation' => 'encaissement',
                'date_paiement'  => now()->format('Y-m-d'),
                'reste_a_payer'  => $nouveauReste,
            ]);

            // Le montant_initial et le montant_total de la vente restent intacts !
            $lockedVente->montant_paye = $nouveauMontantPaye;
            $lockedVente->reste_a_payer = $nouveauReste;
            $lockedVente->mode_paiement = $validated['mode_paiement'];
            $lockedVente->est_paye = $estPaye;
            $lockedVente->statut_paiement = $statutPaiement;
            $lockedVente->save();

            AuditService::log(
                'paiement.enregistrement',
                $paiement,
                $paiement->id,
                [
                    'vente_id'      => $lockedVente->id,
                    'reference'     => $reference,
                    'montant'       => $montant,
                    'reste_a_payer' => $nouveauReste,
                ],
                'Enregistrement de paiement sur la vente #' . $lockedVente->code_recu,
                (int) $lockedVente->entreprise_id
            );

            DB::commit();

            $message = "Paiement de " . number_format($montant, 0, ',', ' ') . " FCFA enregistré avec succès.";
            if ($nouveauReste > 0) {
                $message .= " Reste à payer : " . number_format($nouveauReste, 0, ',', ' ') . " FCFA.";
            } else {
                $message .= " La vente est désormais totalement soldée !";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du paiement : ' . $e->getMessage());
        }
    }

    public function ventesFiltrees(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $entrepriseId = EntrepriseContext::currentId($user);

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
                $dateExpr = "DATE(ventes.date_vente)";
        }

        $ventesQuery = DB::table('ventes')
            ->select(DB::raw("$dateExpr as periode"), DB::raw("SUM(ventes.montant_total) as total"))
            ->whereBetween('ventes.date_vente', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->where('ventes.statut', 'valide');

        if ($entrepriseId) {
            $ventesQuery->where('ventes.entreprise_id', $entrepriseId);
        }

        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])) {
            $ventesQuery->where('ventes.user_id', $userId);
        }

        $ventes = $ventesQuery->groupBy('periode')->orderBy('periode')->get()->keyBy('periode');

        $paiementsQuery = DB::table('paiements')
            ->join('ventes', 'paiements.vente_id', '=', 'ventes.id')
            ->select(DB::raw("$dateExpr as periode"), DB::raw("SUM(paiements.montant) as total"))
            ->whereBetween('paiements.date_paiement', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->where('paiements.type_operation', 'encaissement')
            ->where('ventes.statut', 'valide');

        if ($entrepriseId) {
            $paiementsQuery->where('ventes.entreprise_id', $entrepriseId);
        }

        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])) {
            $paiementsQuery->where('ventes.user_id', $userId);
        }

        $paiements = $paiementsQuery->groupBy('periode')->orderBy('periode')->get()->keyBy('periode');

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

    public function ventesFiltrees1(Request $request)
    {
        return $this->ventesFiltrees($request);
    }

    public function exportPdflist(Request $request)
    {
        $this->checkPermission('exporter');
        $user = Auth::user();

        $ventes = Vente::with(['client', 'user'])->where('statut', 'valide');

        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])) {
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

    public function exportPDF(Request $request)
    {
        return $this->exportPdflist($request);
    }
}
