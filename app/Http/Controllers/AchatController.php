<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\DetailAchat;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\PaiementFournisseur;
use App\Models\Produit;
use App\Models\RetourFournisseur;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AchatController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('afficher achats'), 403);

        $achats = Achat::with(['fournisseur', 'details.produit', 'paiements', 'user'])
            ->orderByDesc('created_at')
            ->get();

        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.achats.index', compact('achats', 'fournisseurs', 'produits'));
    }

    public function create()
    {
        abort_unless(Auth::user()->can('créer achat'), 403);

        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('admin.achats.create', compact('fournisseurs', 'produits'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->can('afficher achats'), 403);

        $achat = Achat::with(['fournisseur', 'details.produit', 'paiements.user', 'retours.produit', 'user'])
            ->findOrFail($id);

        return view('admin.achats.show', compact('achat'));
    }

    /**
     * Crée un achat fournisseur multi-produits.
     * RM-044 : Un achat non réceptionné (statut_reception = 'en_attente') n'augmente pas le stock disponible.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('créer achat'), 403);

        $validated = $request->validate([
            'fournisseur_id' => 'required|integer',
            'date_achat' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_achat',
            'reception_immediate' => 'nullable|boolean',
            'montant_paye_initial' => 'nullable|numeric|min:0',
            'mode_paiement' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'produits' => 'required|array|min:1',
            'produits.*.produit_id' => 'required|integer',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_achat_unitaire' => 'nullable|numeric|min:0',
            'produits.*.prix_unitaire' => 'nullable|numeric|min:0',
        ]);

        $entrepriseId = EntrepriseContext::currentId();
        $fournisseur = Fournisseur::where('entreprise_id', $entrepriseId)->findOrFail($validated['fournisseur_id']);

        $achat = DB::transaction(function () use ($validated, $entrepriseId, $fournisseur) {
            $montantTotal = 0;
            $lignesPreparees = [];

            foreach ($validated['produits'] as $ligne) {
                $produit = Produit::where('entreprise_id', $entrepriseId)->findOrFail($ligne['produit_id']);
                $qte = (int) $ligne['quantite'];
                $prixUnitaire = round((float) ($ligne['prix_achat_unitaire'] ?? $ligne['prix_unitaire'] ?? $produit->prix_achat ?? 0), 2);
                $totalLigne = round($qte * $prixUnitaire, 2);
                $montantTotal += $totalLigne;

                $lignesPreparees[] = [
                    'produit' => $produit,
                    'quantite' => $qte,
                    'prix_unitaire' => $prixUnitaire,
                    'total' => $totalLigne,
                ];
            }

            $montantTotal = round($montantTotal, 2);
            $montantInitial = round((float) ($validated['montant_paye_initial'] ?? 0), 2);

            if ($montantInitial > $montantTotal) {
                throw ValidationException::withMessages([
                    'montant_paye_initial' => 'Le montant payé initial ne peut pas dépasser le montant total de l\'achat.',
                ]);
            }

            $resteAPayer = round($montantTotal - $montantInitial, 2);
            $statutPaiement = 'impaye';
            if ($resteAPayer <= 0 && $montantTotal > 0) {
                $statutPaiement = 'paye';
            } elseif ($montantInitial > 0) {
                $statutPaiement = 'partiel';
            }

            $numeroAchat = $this->genererNumeroAchat($entrepriseId);

            $achat = Achat::create([
                'entreprise_id' => $entrepriseId,
                'fournisseur_id' => $fournisseur->id,
                'user_id' => Auth::id(),
                'numero_achat' => $numeroAchat,
                'date_achat' => $validated['date_achat'],
                'date_echeance' => $validated['date_echeance'] ?? null,
                'montant_total' => $montantTotal,
                'montant_paye' => $montantInitial,
                'reste_a_payer' => $resteAPayer,
                'statut_reception' => 'en_attente',
                'statut_paiement' => $statutPaiement,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lignesPreparees as $item) {
                DetailAchat::create([
                    'achat_id' => $achat->id,
                    'produit_id' => $item['produit']->id,
                    'quantite' => $item['quantite'],
                    'quantite_recue' => 0,
                    'prix_unitaire' => $item['prix_unitaire'],
                    'total' => $item['total'],
                ]);
            }

            if ($montantInitial > 0) {
                PaiementFournisseur::create([
                    'entreprise_id' => $entrepriseId,
                    'achat_id' => $achat->id,
                    'fournisseur_id' => $fournisseur->id,
                    'user_id' => Auth::id(),
                    'reference' => 'PF-' . strtoupper(Str::random(8)),
                    'montant' => $montantInitial,
                    'reste_a_payer' => $resteAPayer,
                    'mode_paiement' => $validated['mode_paiement'] ?? 'Espèces',
                    'date_paiement' => $validated['date_achat'],
                    'type_operation' => 'paiement',
                    'notes' => 'Paiement initial à la commande',
                ]);
            }

            if (!empty($validated['reception_immediate']) && Auth::user()->can('réceptionner achat')) {
                $this->executerReception($achat);
            }

            AuditService::log(
                'achat.created',
                "Création de l'achat {$achat->numero_achat} auprès de {$fournisseur->nom} ({$montantTotal} FCFA)",
                $achat,
                null,
                $achat->toArray()
            );

            return $achat;
        });

        return redirect()->route('achats.show', $achat->id)->with('success', 'Achat enregistré avec succès.');
    }

    /**
     * Valide la réception d'un achat et met à jour le stock (RM-044, RM-035, RM-036).
     */
    public function receptionner(Request $request, $id)
    {
        abort_unless(Auth::user()->can('réceptionner achat'), 403);

        $achat = Achat::with('details.produit')->findOrFail($id);

        if ($achat->statut_reception === 'recu') {
            return redirect()->back()->with('error', 'Cet achat a déjà été intégralement réceptionné.');
        }

        if ($achat->statut_reception === 'annule') {
            return redirect()->back()->with('error', 'Impossible de réceptionner un achat annulé.');
        }

        DB::transaction(function () use ($achat) {
            $this->executerReception($achat);
        });

        return redirect()->back()->with('success', 'Réception validée et stock mis à jour avec succès.');
    }

    protected function executerReception(Achat $achat): void
    {
        $achat->load('details');

        foreach ($achat->details as $detail) {
            $qteRestante = $detail->quantite - $detail->quantite_recue;
            if ($qteRestante <= 0) {
                continue;
            }

            $produit = Produit::where('entreprise_id', $achat->entreprise_id)
                ->lockForUpdate()
                ->findOrFail($detail->produit_id);

            $produit->update([
                'prix_achat' => $detail->prix_unitaire,
            ]);

            $detail->update([
                'quantite_recue' => $detail->quantite,
            ]);

            MouvementStock::create([
                'entreprise_id' => $achat->entreprise_id,
                'produit_id' => $produit->id,
                'user_id' => Auth::id(),
                'achat_id' => $achat->id,
                'quantite' => $qteRestante,
                'type_mouvement' => 'entree',
                'date_mouvement' => now()->toDateString(),
                'motif' => "Réception achat #{$achat->numero_achat}",
            ]);
        }

        $achat->update([
            'statut_reception' => 'recu',
            'date_reception' => now()->toDateString(),
        ]);

        AuditService::log(
            'achat.received',
            "Réception de l'achat {$achat->numero_achat} et entrée en stock",
            $achat
        );
    }

    /**
     * Enregistre un paiement fournisseur partiel ou total sur un achat.
     */
    public function payer(Request $request, $id)
    {
        abort_unless(Auth::user()->can('payer fournisseur'), 403);

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_paiement' => 'nullable|string|max:50',
            'date_paiement' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $achat = Achat::lockForUpdate()->findOrFail($id);

            if ($achat->statut_reception === 'annule') {
                throw ValidationException::withMessages([
                    'montant' => 'Impossible d\'enregistrer un paiement sur un achat annulé.',
                ]);
            }

            $montant = round((float) $validated['montant'], 2);
            $resteActuel = round((float) $achat->reste_a_payer, 2);

            if ($montant > $resteActuel) {
                throw ValidationException::withMessages([
                    'montant' => "Le montant payé ({$montant}) dépasse le reste à payer ({$resteActuel}).",
                ]);
            }

            $reference = $validated['reference'] ?? ('PF-' . strtoupper(Str::random(8)));

            if (PaiementFournisseur::withoutGlobalScopes()->where('reference', $reference)->exists()) {
                throw ValidationException::withMessages([
                    'reference' => 'Cette référence de paiement fournisseur existe déjà.',
                ]);
            }

            $nouveauMontantPaye = round((float) $achat->montant_paye + $montant, 2);
            $nouveauReste = max(0, round((float) $achat->montant_total - $nouveauMontantPaye, 2));
            $statutPaiement = $nouveauReste <= 0 ? 'paye' : 'partiel';

            $paiement = PaiementFournisseur::create([
                'entreprise_id' => $achat->entreprise_id,
                'achat_id' => $achat->id,
                'fournisseur_id' => $achat->fournisseur_id,
                'user_id' => Auth::id(),
                'reference' => $reference,
                'montant' => $montant,
                'reste_a_payer' => $nouveauReste,
                'mode_paiement' => $validated['mode_paiement'] ?? 'Espèces',
                'date_paiement' => $validated['date_paiement'] ?? now()->toDateString(),
                'type_operation' => 'paiement',
                'notes' => $validated['notes'] ?? null,
            ]);

            $achat->update([
                'montant_paye' => $nouveauMontantPaye,
                'reste_a_payer' => $nouveauReste,
                'statut_paiement' => $statutPaiement,
            ]);

            AuditService::log(
                'achat.payment_recorded',
                "Paiement fournisseur {$reference} de {$montant} FCFA sur achat {$achat->numero_achat}",
                $paiement,
                null,
                $paiement->toArray()
            );
        });

        return redirect()->back()->with('success', 'Paiement fournisseur enregistré avec succès.');
    }

    /**
     * Enregistre un retour fournisseur (diminue le stock si déjà reçu et ajuste le solde).
     */
    public function retourFournisseur(Request $request, $id)
    {
        abort_unless(Auth::user()->can('modifier achat') || Auth::user()->can('réceptionner achat'), 403);

        $validated = $request->validate([
            'produit_id' => 'required|integer',
            'quantite' => 'required|integer|min:1',
            'motif' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $achat = Achat::with('details')->lockForUpdate()->findOrFail($id);
            $detail = $achat->details->where('produit_id', $validated['produit_id'])->first();

            if (!$detail) {
                throw ValidationException::withMessages([
                    'produit_id' => 'Ce produit ne figure pas dans cet achat.',
                ]);
            }

            $qteRetour = (int) $validated['quantite'];
            if ($qteRetour > $detail->quantite) {
                throw ValidationException::withMessages([
                    'quantite' => 'La quantité retournée dépasse la quantité achetée.',
                ]);
            }

            $montantRetour = round($qteRetour * (float) $detail->prix_unitaire, 2);

            if ($achat->statut_reception === 'recu') {
                $produit = Produit::where('entreprise_id', $achat->entreprise_id)
                    ->lockForUpdate()
                    ->findOrFail($detail->produit_id);

                if ($produit->stock_actuel < $qteRetour) {
                    throw ValidationException::withMessages([
                        'quantite' => 'Stock disponible insuffisant pour effectuer ce retour fournisseur.',
                    ]);
                }

                MouvementStock::create([
                    'entreprise_id' => $achat->entreprise_id,
                    'produit_id' => $produit->id,
                    'user_id' => Auth::id(),
                    'achat_id' => $achat->id,
                    'quantite' => $qteRetour,
                    'type_mouvement' => 'sortie',
                    'date_mouvement' => now()->toDateString(),
                    'motif' => "Retour fournisseur achat #{$achat->numero_achat} : {$validated['motif']}",
                ]);
            }

            $retour = RetourFournisseur::create([
                'entreprise_id' => $achat->entreprise_id,
                'achat_id' => $achat->id,
                'fournisseur_id' => $achat->fournisseur_id,
                'produit_id' => $detail->produit_id,
                'user_id' => Auth::id(),
                'quantite' => $qteRetour,
                'prix_unitaire' => $detail->prix_unitaire,
                'montant_retour' => $montantRetour,
                'motif' => $validated['motif'],
                'date_retour' => now()->toDateString(),
                'statut' => 'valide',
            ]);

            $nouveauTotal = max(0, round((float) $achat->montant_total - $montantRetour, 2));
            $nouveauReste = max(0, round($nouveauTotal - (float) $achat->montant_paye, 2));

            $achat->update([
                'montant_total' => $nouveauTotal,
                'reste_a_payer' => $nouveauReste,
                'statut_paiement' => $nouveauReste <= 0 ? 'paye' : ($achat->montant_paye > 0 ? 'partiel' : 'impaye'),
            ]);

            AuditService::log(
                'achat.supplier_return',
                "Retour fournisseur sur achat {$achat->numero_achat} ({$qteRetour} unités, {$montantRetour} FCFA)",
                $retour,
                null,
                $retour->toArray()
            );
        });

        return redirect()->back()->with('success', 'Retour fournisseur enregistré avec succès.');
    }

    protected function genererNumeroAchat(int $entrepriseId): string
    {
        $prefix = 'ACH-' . date('Ymd') . '-';
        $count = Achat::withoutGlobalScopes()
            ->where('entreprise_id', $entrepriseId)
            ->count() + 1;

        do {
            $numero = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = Achat::withoutGlobalScopes()
                ->where('entreprise_id', $entrepriseId)
                ->where('numero_achat', $numero)
                ->exists();
            $count++;
        } while ($exists);

        return $numero;
    }
}
