<?php

namespace App\Services;

use App\Models\Achat;
use App\Models\AvanceSalaire;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\PaiementFournisseur;
use App\Models\RemboursementAvance;
use App\Models\RetourClient;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Calcule les indicateurs financiers stricts (RM-046 à RM-054) pour une ou plusieurs entreprises
     */
    public static function calculateIndicators(
        array $entrepriseIds,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $userId = null
    ): array {
        if (empty($entrepriseIds)) {
            return self::emptyIndicators();
        }

        // 1. Ventes validées sur la période
        $ventesQuery = Vente::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->where('statut', 'valide');

        if ($userId) {
            $ventesQuery->where('user_id', $userId);
        }
        if ($dateDebut) {
            $ventesQuery->whereDate('date_vente', '>=', $dateDebut);
        }
        if ($dateFin) {
            $ventesQuery->whereDate('date_vente', '<=', $dateFin);
        }

        $ventesBrutes = (float) (clone $ventesQuery)->sum('montant_total');
        $nbVentes = (int) (clone $ventesQuery)->count();

        // Retours clients validés sur la période
        $retoursClientsQuery = RetourClient::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->where('statut', 'valide');
        if ($userId) {
            $retoursClientsQuery->where('user_id', $userId);
        }
        if ($dateDebut) {
            $retoursClientsQuery->whereDate('date_retour', '>=', $dateDebut);
        }
        if ($dateFin) {
            $retoursClientsQuery->whereDate('date_retour', '<=', $dateFin);
        }
        $totalRetoursClients = (float) (clone $retoursClientsQuery)->sum('montant_retour');
        $remboursementsClientsRetours = (float) (clone $retoursClientsQuery)->sum('montant_rembourse');

        // Chiffre d'affaires net : ventes validées (montant_total est ajusté lors des retours)
        $chiffreAffairesNet = max(0, $ventesBrutes);

        // 2. Créances clients (restant dû sur les ventes validées)
        $creancesQuery = Vente::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->where('statut', 'valide');
        if ($userId) {
            $creancesQuery->where('user_id', $userId);
        }
        if ($dateDebut) {
            $creancesQuery->whereDate('date_vente', '>=', $dateDebut);
        }
        if ($dateFin) {
            $creancesQuery->whereDate('date_vente', '<=', $dateFin);
        }
        $creancesClients = (float) $creancesQuery->sum('reste_a_payer');

        // 3. Encaissements réellement reçus pendant la période (distincts des ventes !)
        $paiementsQuery = Paiement::withoutGlobalScope('entreprise')
            ->whereIn('paiements.entreprise_id', $entrepriseIds)
            ->whereHas('vente', function ($q) use ($userId) {
                $q->withoutGlobalScope('entreprise')->where('statut', 'valide');
                if ($userId) {
                    $q->where('user_id', $userId);
                }
            });

        if ($dateDebut) {
            $paiementsQuery->whereDate('date_paiement', '>=', $dateDebut);
        }
        if ($dateFin) {
            $paiementsQuery->whereDate('date_paiement', '<=', $dateFin);
        }

        $encaissements = (float) (clone $paiementsQuery)
            ->where('type_operation', 'encaissement')
            ->sum('montant');

        $remboursementsClientsPaiements = (float) (clone $paiementsQuery)
            ->where('type_operation', 'remboursement_client')
            ->sum('montant');

        $remboursementsClients = $remboursementsClientsPaiements + $remboursementsClientsRetours;

        // 4. Coût des marchandises vendues (COGS) pour les ventes de la période
        $venteIdsPeriode = (clone $ventesQuery)->pluck('id')->all();
        $coutMarchandisesVendues = 0.0;
        if (! empty($venteIdsPeriode)) {
            $coutMarchandisesVendues = (float) DB::table('detail_ventes')
                ->whereIn('vente_id', $venteIdsPeriode)
                ->selectRaw('SUM(quantite * COALESCE(cout_achat_unitaire, 0)) as cogs')
                ->value('cogs');
        }

        // 5. Achats & Dettes fournisseurs (distincts du COGS et des paiements fournisseurs !)
        $achatsQuery = Achat::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->where('statut_reception', '!=', 'annule');
        if ($dateDebut) {
            $achatsQuery->whereDate('date_achat', '>=', $dateDebut);
        }
        if ($dateFin) {
            $achatsQuery->whereDate('date_achat', '<=', $dateFin);
        }
        $achatsPeriode = (float) (clone $achatsQuery)->sum('montant_total');
        $dettesFournisseurs = (float) (clone $achatsQuery)->sum('reste_a_payer');

        // 6. Paiements fournisseurs réellement décaissés sur la période
        $paiementsFournisseursQuery = PaiementFournisseur::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds);
        if ($dateDebut) {
            $paiementsFournisseursQuery->whereDate('date_paiement', '>=', $dateDebut);
        }
        if ($dateFin) {
            $paiementsFournisseursQuery->whereDate('date_paiement', '<=', $dateFin);
        }
        $paiementsFournisseurs = (float) (clone $paiementsFournisseursQuery)
            ->where('type_operation', 'paiement')
            ->sum('montant');
        $remboursementsFournisseurs = (float) (clone $paiementsFournisseursQuery)
            ->where('type_operation', 'remboursement_fournisseur')
            ->sum('montant');

        // 7. Dépenses validées uniquement (RM-053 / 7.4)
        $depensesQuery = Depense::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->where('statut', 'validee');
        if ($dateDebut) {
            $depensesQuery->whereDate('date_depense', '>=', $dateDebut);
        }
        if ($dateFin) {
            $depensesQuery->whereDate('date_depense', '<=', $dateFin);
        }
        $depensesValidees = (float) $depensesQuery->sum('montant');

        // 8. Avances sur salaire et remboursements d'avances (séparés des dépenses)
        $avancesQuery = AvanceSalaire::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->whereIn('statut', ['approuvee', 'remboursee']);
        if ($dateDebut) {
            $avancesQuery->whereDate('date_avance', '>=', $dateDebut);
        }
        if ($dateFin) {
            $avancesQuery->whereDate('date_avance', '<=', $dateFin);
        }
        $avancesSalairesVersees = (float) (clone $avancesQuery)->sum('montant');
        $soldeAvancesEmployes = (float) AvanceSalaire::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds)
            ->whereIn('statut', ['approuvee', 'remboursee'])
            ->sum('reste_a_rembourser');

        $remboursementsAvancesQuery = RemboursementAvance::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $entrepriseIds);
        if ($dateDebut) {
            $remboursementsAvancesQuery->whereDate('date_remboursement', '>=', $dateDebut);
        }
        if ($dateFin) {
            $remboursementsAvancesQuery->whereDate('date_remboursement', '<=', $dateFin);
        }
        $remboursementsAvancesRecus = (float) $remboursementsAvancesQuery->sum('montant');

        // 9. Marge brute estimée & Résultat estimatif & Trésorerie nette
        $margeBruteEstimee = $chiffreAffairesNet - $coutMarchandisesVendues;
        $resultatEstimatif = $margeBruteEstimee - $depensesValidees;

        $totalEntreesTresorerie = $encaissements + $remboursementsFournisseurs + $remboursementsAvancesRecus;
        $totalSortiesTresorerie = $paiementsFournisseurs + $depensesValidees + $remboursementsClients + $avancesSalairesVersees;
        $fluxTresorerieNet = $totalEntreesTresorerie - $totalSortiesTresorerie;

        return [
            'chiffre_affaires'           => round($chiffreAffairesNet, 2),
            'chiffre_affaires_net'       => round($chiffreAffairesNet, 2),
            'ventes_nettes'              => round($chiffreAffairesNet, 2),
            'retours_clients'            => round($totalRetoursClients, 2),
            'nb_ventes'                  => $nbVentes,
            'panier_moyen'               => $nbVentes > 0 ? round($chiffreAffairesNet / $nbVentes, 2) : 0.0,
            'encaissements'              => round($encaissements, 2),
            'total_encaisse'             => round($encaissements, 2),
            'remboursements_clients'     => round($remboursementsClients, 2),
            'creances_clients'           => round($creancesClients, 2),
            'achats'                     => round($achatsPeriode, 2),
            'paiements_fournisseurs'     => round($paiementsFournisseurs, 2),
            'remboursements_fournisseurs'=> round($remboursementsFournisseurs, 2),
            'dettes_fournisseurs'        => round($dettesFournisseurs, 2),
            'depenses'                   => round($depensesValidees, 2),
            'depenses_validees'          => round($depensesValidees, 2),
            'cout_marchandises_vendues'  => round($coutMarchandisesVendues, 2),
            'marge_brute_estimee'        => round($margeBruteEstimee, 2),
            'resultat_estimatif'         => round($resultatEstimatif, 2),
            'avances_salaires_versees'   => round($avancesSalairesVersees, 2),
            'remboursements_avances'     => round($remboursementsAvancesRecus, 2),
            'solde_avances_employes'     => round($soldeAvancesEmployes, 2),
            'encours_avances_salaires'   => round($soldeAvancesEmployes, 2),
            'entrees_tresorerie'         => round($totalEntreesTresorerie, 2),
            'sorties_tresorerie'         => round($totalSortiesTresorerie, 2),
            'flux_tresorerie_net'        => round($fluxTresorerieNet, 2),
            'avertissement_comptable'    => 'Indicateurs de gestion estimatifs hors taxes, amortissements et charges fiscales non enregistrées.',
        ];
    }

    private static function emptyIndicators(): array
    {
        return [
            'chiffre_affaires'           => 0.0,
            'chiffre_affaires_net'       => 0.0,
            'ventes_nettes'              => 0.0,
            'retours_clients'            => 0.0,
            'nb_ventes'                  => 0,
            'panier_moyen'               => 0.0,
            'encaissements'              => 0.0,
            'total_encaisse'             => 0.0,
            'remboursements_clients'     => 0.0,
            'creances_clients'           => 0.0,
            'achats'                     => 0.0,
            'paiements_fournisseurs'     => 0.0,
            'remboursements_fournisseurs'=> 0.0,
            'dettes_fournisseurs'        => 0.0,
            'depenses'                   => 0.0,
            'depenses_validees'          => 0.0,
            'cout_marchandises_vendues'  => 0.0,
            'marge_brute_estimee'        => 0.0,
            'resultat_estimatif'         => 0.0,
            'avances_salaires_versees'   => 0.0,
            'remboursements_avances'     => 0.0,
            'solde_avances_employes'     => 0.0,
            'encours_avances_salaires'   => 0.0,
            'entrees_tresorerie'         => 0.0,
            'sorties_tresorerie'         => 0.0,
            'flux_tresorerie_net'        => 0.0,
            'avertissement_comptable'    => 'Aucune entreprise autorisée.',
        ];
    }
}
