<?php

namespace App\Http\Controllers;

use App\Models\AvanceSalaire;
use App\Models\Employe;
use App\Models\RemboursementAvance;
use App\Services\AuditService;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AvanceSalaireController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can('afficher avances salaires'), 403);

        $avances = AvanceSalaire::with(['employe', 'remboursements', 'user'])
            ->orderByDesc('date_avance')
            ->orderByDesc('id')
            ->get();

        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('admin.avances.index', compact('avances', 'employes'));
    }

    /**
     * Crée une avance sur salaire (RM-055 à RM-060).
     * Vérifie le plafond autorisé par employé / entreprise côté serveur.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('créer avance salaire'), 403);

        $validated = $request->validate([
            'employe_id' => 'required|integer',
            'montant' => 'required|numeric|min:0.01',
            'date_avance' => 'required|date',
            'motif' => 'nullable|string|max:255',
            'statut' => 'nullable|in:approuvee,en_attente,rejetee',
        ]);

        $entrepriseId = EntrepriseContext::currentId();
        $employe = Employe::where('entreprise_id', $entrepriseId)
            ->where('actif', true)
            ->findOrFail($validated['employe_id']);

        $montantDemande = round((float) $validated['montant'], 2);

        // RM-057 : Vérification du plafond autorisé côté serveur
        $plafondEffectif = $employe->plafond_effectif;
        $encoursActuel = $employe->solde_avance_restant;

        if ($plafondEffectif > 0 && ($encoursActuel + $montantDemande) > $plafondEffectif) {
            throw ValidationException::withMessages([
                'montant' => "Plafond d'avance dépassé pour {$employe->nom}. Plafond autorisé : {$plafondEffectif} FCFA, encours actuel : {$encoursActuel} FCFA, montant demandé : {$montantDemande} FCFA.",
            ]);
        }

        $statut = $validated['statut'] ?? 'approuvee';

        $avance = DB::transaction(function () use ($validated, $entrepriseId, $employe, $montantDemande, $statut) {
            $avance = AvanceSalaire::create([
                'entreprise_id' => $entrepriseId,
                'employe_id' => $employe->id,
                'valide_par' => Auth::id(),
                'montant' => $montantDemande,
                'montant_rembourse' => 0,
                'reste_a_rembourser' => $montantDemande,
                'date_avance' => $validated['date_avance'],
                'motif' => $validated['motif'] ?? 'Avance sur salaire',
                'statut' => $statut,
            ]);

            AuditService::log(
                'avance_salaire.created',
                "Octroi d'une avance sur salaire de {$montantDemande} FCFA à {$employe->nom} {$employe->prenom}",
                $avance,
                null,
                $avance->toArray()
            );

            return $avance;
        });

        return redirect()->back()->with('success', 'Avance sur salaire enregistrée avec succès.');
    }

    /**
     * Enregistre un remboursement d'avance (RM-058, RM-059).
     * Le remboursement est une écriture séparée dans remboursement_avances.
     */
    public function rembourser(Request $request, $id)
    {
        abort_unless(Auth::user()->can('rembourser avance salaire'), 403);

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_remboursement' => 'nullable|string|max:50',
            'date_remboursement' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $avance = AvanceSalaire::with('employe')->lockForUpdate()->findOrFail($id);

            if (in_array($avance->statut, ['en_attente', 'rejetee', 'annulee'], true)) {
                throw ValidationException::withMessages([
                    'montant' => 'Impossible de rembourser une avance non approuvée ou rejetée.',
                ]);
            }

            $montant = round((float) $validated['montant'], 2);
            $reste = round((float) $avance->reste_a_rembourser, 2);

            if ($montant > $reste) {
                throw ValidationException::withMessages([
                    'montant' => "Le montant du remboursement ({$montant} FCFA) dépasse le solde restant à rembourser ({$reste} FCFA).",
                ]);
            }

            $reference = $validated['reference'] ?? ('RMB-' . strtoupper(Str::random(8)));

            // Protection anti-doublon
            if (RemboursementAvance::withoutGlobalScopes()->where('reference', $reference)->exists()) {
                throw ValidationException::withMessages([
                    'reference' => 'Cette référence de remboursement a déjà été enregistrée.',
                ]);
            }

            $remboursement = RemboursementAvance::create([
                'entreprise_id' => $avance->entreprise_id,
                'avance_salaire_id' => $avance->id,
                'employe_id' => $avance->employe_id,
                'user_id' => Auth::id(),
                'reference' => $reference,
                'montant' => $montant,
                'mode_remboursement' => $validated['mode_remboursement'] ?? 'retenue_salaire',
                'date_remboursement' => $validated['date_remboursement'] ?? now()->toDateString(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $nouveauRembourse = round((float) $avance->montant_rembourse + $montant, 2);
            $nouveauReste = max(0, round((float) $avance->montant - $nouveauRembourse, 2));
            $nouveauStatut = $nouveauReste <= 0 ? 'remboursee' : 'approuvee';

            $avance->update([
                'montant_rembourse' => $nouveauRembourse,
                'reste_a_rembourser' => $nouveauReste,
                'statut' => $nouveauStatut,
            ]);

            AuditService::log(
                'avance_salaire.repaid',
                "Remboursement d'avance {$reference} de {$montant} FCFA pour {$avance->employe->nom}",
                $remboursement,
                null,
                $remboursement->toArray()
            );
        });

        return redirect()->back()->with('success', 'Remboursement d\'avance enregistré avec succès.');
    }
}
