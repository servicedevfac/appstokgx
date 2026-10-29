<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Vente;
use App\Services\EntrepriseContext;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $driver = DB::getDriverName();

        if ($user->isSuperAdmin() && ! $user->is_proprietaire) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->hasAnyRole(['Propriétaire', 'Administrateur'])) {
            return $this->adminDashboard($driver, $request);
        }

        if ($user->hasAnyRole(['Gestionnaire', 'Commerciale', 'Commercial'])) {
            return $this->vendeurDashboard($user, $driver, $request);
        }

        return redirect()->route('ventes.index');
    }

    /**
     * Tableau de bord consolidé multi-entreprises pour un Propriétaire (Section 3.2 & 7.2)
     */
    public function consolide(Request $request)
    {
        $user = Auth::user();
        $periodData = $this->resolvePeriod($request);
        $periode = $periodData['periode'];
        $dateDebut = $periodData['dateDebut'];
        $dateFin = $periodData['dateFin'];
        $periodeLabel = $periodData['periodeLabel'];
        $periodeTitle = $periodData['periodeTitle'];

        $entreprises = $user->accessibleEntreprises();
        $accessibleIds = $entreprises->pluck('id')->map(fn ($id) => (int) $id)->all();

        $consolide = [
            'global'         => FinanceService::calculateIndicators($accessibleIds, $dateDebut, $dateFin),
            'par_entreprise' => [],
        ];

        foreach ($entreprises as $ent) {
            $consolide['par_entreprise'][$ent->id] = FinanceService::calculateIndicators([(int) $ent->id], $dateDebut, $dateFin);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'entreprises'  => $entreprises,
                'consolide'    => $consolide,
                'periode'      => $periode,
                'periodeLabel' => $periodeLabel,
            ]);
        }

        return view('dashboards.consolide', compact(
            'entreprises',
            'consolide',
            'periode',
            'periodeLabel',
            'periodeTitle',
            'dateDebut',
            'dateFin'
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $periode = $request->input('periode', 'mois');
        $dateDebut = null;
        $dateFin = null;
        $periodeLabel = '';
        $periodeTitle = 'ce mois';

        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $periode = 'custom';
            $dateDebut = $request->input('date_debut');
            $dateFin = $request->input('date_fin');

            if ($dateDebut && $dateFin) {
                $periodeLabel = 'Du ' . Carbon::parse($dateDebut)->format('d/m/Y') . ' au ' . Carbon::parse($dateFin)->format('d/m/Y');
                $periodeTitle = 'sélection';
            } elseif ($dateDebut) {
                $periodeLabel = 'À partir du ' . Carbon::parse($dateDebut)->format('d/m/Y');
                $periodeTitle = 'sélection';
            } elseif ($dateFin) {
                $periodeLabel = "Jusqu'au " . Carbon::parse($dateFin)->format('d/m/Y');
                $periodeTitle = 'sélection';
            }
        } else {
            switch ($periode) {
                case 'jour':
                    $dateDebut = Carbon::today()->format('Y-m-d');
                    $dateFin = Carbon::today()->format('Y-m-d');
                    $periodeLabel = "Aujourd'hui (" . Carbon::today()->format('d/m/Y') . ")";
                    $periodeTitle = 'jour';
                    break;
                case 'semaine':
                    $dateDebut = Carbon::now()->startOfWeek()->format('Y-m-d');
                    $dateFin = Carbon::now()->endOfWeek()->format('Y-m-d');
                    $periodeLabel = "Cette semaine (" . Carbon::now()->startOfWeek()->format('d/m') . " au " . Carbon::now()->endOfWeek()->format('d/m/Y') . ")";
                    $periodeTitle = 'semaine';
                    break;
                case 'annee':
                    $dateDebut = Carbon::now()->startOfYear()->format('Y-m-d');
                    $dateFin = Carbon::now()->endOfYear()->format('Y-m-d');
                    $periodeLabel = "Cette année (" . Carbon::now()->year . ")";
                    $periodeTitle = 'an';
                    break;
                case 'tout':
                    $dateDebut = null;
                    $dateFin = null;
                    $periodeLabel = 'Toutes les périodes (Global)';
                    $periodeTitle = 'global';
                    break;
                case 'mois':
                default:
                    $periode = 'mois';
                    $dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $dateFin = Carbon::now()->endOfMonth()->format('Y-m-d');
                    $periodeLabel = 'Ce mois (' . Carbon::now()->translatedFormat('F Y') . ')';
                    $periodeTitle = 'mois';
                    break;
            }
        }

        return [
            'periode'      => $periode,
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
            'periodeLabel' => $periodeLabel,
            'periodeTitle' => $periodeTitle,
        ];
    }

    private function adminDashboard(string $driver, Request $request)
    {
        $user = Auth::user();
        $periodData = $this->resolvePeriod($request);
        $periode = $periodData['periode'];
        $dateDebut = $periodData['dateDebut'];
        $dateFin = $periodData['dateFin'];
        $periodeLabel = $periodData['periodeLabel'];
        $periodeTitle = $periodData['periodeTitle'];

        // Déterminer le périmètre d'entreprises autorisées (entreprise active ou consolidé)
        $isConsolide = $request->boolean('consolide', false);
        $accessibleIds = $user->accessibleEntrepriseIds();
        $currentId = EntrepriseContext::currentId($user);

        if ($isConsolide) {
            $targetEntrepriseIds = $accessibleIds;
        } else {
            $targetEntrepriseIds = $currentId ? [$currentId] : $accessibleIds;
        }

        // Calcul strict via FinanceService (RM-046 à RM-054)
        $indicateursFinanciers = FinanceService::calculateIndicators($targetEntrepriseIds, $dateDebut, $dateFin);

        $caPeriode = $indicateursFinanciers['chiffre_affaires'];
        $encaissePeriode = $indicateursFinanciers['encaissements'];
        $resteAPayerPeriode = $indicateursFinanciers['creances_clients'];
        $depensesPeriode = $indicateursFinanciers['depenses'];
        $margeBrutePeriode = $indicateursFinanciers['marge_brute_estimee'];
        $fluxTresoreriePeriode = $indicateursFinanciers['flux_tresorerie_net'];
        $beneficeNetPeriode = $indicateursFinanciers['resultat_estimatif'];
        $nbVentesPeriode = $indicateursFinanciers['nb_ventes'];
        $panierMoyenPeriode = $indicateursFinanciers['panier_moyen'];
        $dettesFournisseursPeriode = $indicateursFinanciers['dettes_fournisseurs'];
        $achatsPeriode = $indicateursFinanciers['achats'];
        $paiementsFournisseursPeriode = $indicateursFinanciers['paiements_fournisseurs'];

        $ventes = Vente::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->get();
        $derniersVentes = Vente::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->where('statut', 'valide')
            ->orderByDesc('date_vente')
            ->take(10)
            ->get();

        $derniersClients = Vente::withoutGlobalScope('entreprise')
            ->with('client')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->select('client_id')
            ->groupBy('client_id')
            ->orderByRaw('MAX(date_vente) DESC')
            ->take(10)
            ->get();

        $derniersDepenses = Depense::withoutGlobalScope('entreprise')
            ->with('user')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->where('statut', 'validee')
            ->orderByDesc('date_depense')
            ->take(5)
            ->get();

        // Produits en alerte de stock (LEFT JOIN pour inclure les produits à 0 mouvement !)
        $produitsStockFaible = Produit::withoutGlobalScope('entreprise')
            ->select('produits.*')
            ->leftJoin('mouvement_stocks', 'produits.id', '=', 'mouvement_stocks.produit_id')
            ->whereIn('produits.entreprise_id', $targetEntrepriseIds)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'entree\' THEN mouvement_stocks.quantite ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'sortie\' THEN mouvement_stocks.quantite ELSE 0 END), 0) as stock_actuel
            ')
            ->groupBy('produits.id', 'produits.entreprise_id', 'produits.categorie_id', 'produits.nom', 'produits.reference', 'produits.prix_achat', 'produits.prix', 'produits.seuil_alerte', 'produits.alerte_envoyee', 'produits.last_alerted_at', 'produits.created_at', 'produits.updated_at')
            ->havingRaw('(COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'entree\' THEN mouvement_stocks.quantite ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'sortie\' THEN mouvement_stocks.quantite ELSE 0 END), 0)) <= produits.seuil_alerte')
            ->get();

        // 12 derniers mois (Graphique)
        $months = collect(range(0, 11))
            ->map(fn ($i) => Carbon::now()->subMonths($i)->format('Y-m'))
            ->reverse()
            ->values();

        $labels = $months->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'))->values();

        $dateExprVente = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_vente)",
            'pgsql'  => "TO_CHAR(date_vente, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_vente, '%Y-%m')",
        };

        $dateExprPaiement = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_paiement)",
            'pgsql'  => "TO_CHAR(date_paiement, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_paiement, '%Y-%m')",
        };

        $dateExprDepense = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_depense)",
            'pgsql'  => "TO_CHAR(date_depense, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_depense, '%Y-%m')",
        };

        $startMonthLimit = Carbon::now()->subMonths(11)->startOfMonth()->format('Y-m-d');

        $salesByMonth = Vente::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->selectRaw("$dateExprVente as month, SUM(montant_total) as total")
            ->where('date_vente', '>=', $startMonthLimit)
            ->where('statut', 'valide')
            ->groupBy('month')
            ->pluck('total', 'month');

        $paymentsByMonth = Paiement::withoutGlobalScope('entreprise')
            ->whereIn('paiements.entreprise_id', $targetEntrepriseIds)
            ->where('type_operation', 'encaissement')
            ->whereHas('vente', fn ($q) => $q->withoutGlobalScope('entreprise')->where('statut', 'valide'))
            ->selectRaw("$dateExprPaiement as month, SUM(montant) as total")
            ->where('date_paiement', '>=', $startMonthLimit)
            ->groupBy('month')
            ->pluck('total', 'month');

        $depensesByMonth = Depense::withoutGlobalScope('entreprise')
            ->whereIn('entreprise_id', $targetEntrepriseIds)
            ->where('statut', 'validee')
            ->selectRaw("$dateExprDepense as month, SUM(montant) as total")
            ->where('date_depense', '>=', $startMonthLimit)
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = $months->map(fn ($m) => (float) ($salesByMonth[$m] ?? 0))->values();
        $data1 = $months->map(fn ($m) => (float) ($paymentsByMonth[$m] ?? 0))->values();
        $dataDepenses = $months->map(fn ($m) => (float) ($depensesByMonth[$m] ?? 0))->values();
        $dataBenefice = $months->map(fn ($m) => (float) (($paymentsByMonth[$m] ?? 0) - ($depensesByMonth[$m] ?? 0)))->values();
        $dataReste = $months->map(fn ($m) => max(0, (float) (($salesByMonth[$m] ?? 0) - ($paymentsByMonth[$m] ?? 0))))->values();

        // Variables de compatibilité (en respectant RM-047 : Chiffre d'affaires = ventes nettes, jamais encaissements)
        $ca_journalier = $caPeriode;
        $chiffreAffaireMoisEnCours = $caPeriode;
        $chiffreAffaires = $caPeriode;
        $chiffreAffairesGlobaux = $caPeriode;
        $totalVentesNonPayes = $resteAPayerPeriode;
        $depensesMois = $depensesPeriode;
        $beneficeNetMois = $beneficeNetPeriode;

        if ($request->wantsJson()) {
            return response()->json([
                'periode'      => $periode,
                'date_debut'   => $dateDebut,
                'date_fin'     => $dateFin,
                'is_consolide' => $isConsolide,
                'entreprises'  => $targetEntrepriseIds,
                'indicateurs'  => $indicateursFinanciers,
            ]);
        }

        return view('dashboards.admin', compact(
            'periode',
            'periodeLabel',
            'periodeTitle',
            'dateDebut',
            'dateFin',
            'isConsolide',
            'indicateursFinanciers',
            'caPeriode',
            'encaissePeriode',
            'resteAPayerPeriode',
            'depensesPeriode',
            'margeBrutePeriode',
            'fluxTresoreriePeriode',
            'beneficeNetPeriode',
            'dettesFournisseursPeriode',
            'achatsPeriode',
            'paiementsFournisseursPeriode',
            'nbVentesPeriode',
            'panierMoyenPeriode',
            'ca_journalier',
            'chiffreAffaires',
            'totalVentesNonPayes',
            'ventes',
            'labels',
            'data',
            'dataReste',
            'data1',
            'dataDepenses',
            'dataBenefice',
            'chiffreAffairesGlobaux',
            'derniersVentes',
            'derniersClients',
            'produitsStockFaible',
            'chiffreAffaireMoisEnCours',
            'depensesMois',
            'beneficeNetMois',
            'derniersDepenses'
        ));
    }

    private function vendeurDashboard($user, string $driver, Request $request)
    {
        $userId = $user->id;
        $periodData = $this->resolvePeriod($request);
        $periode = $periodData['periode'];
        $dateDebut = $periodData['dateDebut'];
        $dateFin = $periodData['dateFin'];
        $periodeLabel = $periodData['periodeLabel'];
        $periodeTitle = $periodData['periodeTitle'];

        $currentId = EntrepriseContext::currentId($user);
        $targetEntrepriseIds = $currentId ? [$currentId] : $user->accessibleEntrepriseIds();

        $indicateursFinanciers = FinanceService::calculateIndicators($targetEntrepriseIds, $dateDebut, $dateFin, $userId);

        $caPeriode = $indicateursFinanciers['chiffre_affaires'];
        $encaissePeriode = $indicateursFinanciers['encaissements'];
        $resteAPayerPeriode = $indicateursFinanciers['creances_clients'];
        $nbVentesPeriode = $indicateursFinanciers['nb_ventes'];
        $panierMoyenPeriode = $indicateursFinanciers['panier_moyen'];
        $tauxRecouvrement = $caPeriode > 0 ? round(($encaissePeriode / $caPeriode) * 100, 1) : 0;

        $ventes = Vente::where('user_id', $userId)
            ->orderByDesc('date_vente')
            ->take(10)
            ->get();

        $derniersClients = Vente::with('client')
            ->where('user_id', $userId)
            ->select('client_id')
            ->groupBy('client_id')
            ->orderByRaw('MAX(date_vente) DESC')
            ->take(10)
            ->get();

        $produitsStockFaible = Produit::withoutGlobalScope('entreprise')
            ->select('produits.*')
            ->leftJoin('mouvement_stocks', 'produits.id', '=', 'mouvement_stocks.produit_id')
            ->whereIn('produits.entreprise_id', $targetEntrepriseIds)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'entree\' THEN mouvement_stocks.quantite ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'sortie\' THEN mouvement_stocks.quantite ELSE 0 END), 0) as stock_actuel
            ')
            ->groupBy('produits.id', 'produits.entreprise_id', 'produits.categorie_id', 'produits.nom', 'produits.reference', 'produits.prix_achat', 'produits.prix', 'produits.seuil_alerte', 'produits.alerte_envoyee', 'produits.last_alerted_at', 'produits.created_at', 'produits.updated_at')
            ->havingRaw('(COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'entree\' THEN mouvement_stocks.quantite ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN mouvement_stocks.type_mouvement = \'sortie\' THEN mouvement_stocks.quantite ELSE 0 END), 0)) <= produits.seuil_alerte')
            ->get();

        $months = collect(range(0, 11))
            ->map(fn ($i) => now()->subMonths($i)->format('Y-m'))
            ->reverse()
            ->values();

        $labels = $months->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'))->values();
        $startDate = now()->subMonths(11)->startOfMonth()->format('Y-m-d');

        $dateExprVente = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_vente)",
            'pgsql'  => "TO_CHAR(date_vente, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_vente, '%Y-%m')",
        };

        $dateExprPaiement = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_paiement)",
            'pgsql'  => "TO_CHAR(date_paiement, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_paiement, '%Y-%m')",
        };

        $sales = Vente::query()
            ->selectRaw("$dateExprVente as month, SUM(montant_total) as total")
            ->where('statut', 'valide')
            ->where('user_id', $userId)
            ->where('date_vente', '>=', $startDate)
            ->groupBy('month')
            ->pluck('total', 'month');

        $payments = Paiement::query()
            ->where('type_operation', 'encaissement')
            ->whereHas('vente', fn ($q) => $q->where('user_id', $userId)->where('statut', 'valide'))
            ->selectRaw("$dateExprPaiement as month, SUM(montant) as total")
            ->where('date_paiement', '>=', $startDate)
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = $months->map(fn ($m) => (float) ($sales[$m] ?? 0))->values();
        $data1 = $months->map(fn ($m) => (float) ($payments[$m] ?? 0))->values();
        $dataReste = $months->map(fn ($m) => max(0, (float) (($sales[$m] ?? 0) - ($payments[$m] ?? 0))))->values();

        $chiffreAffairesvendeurs = $encaissePeriode;
        $chiffreAffairesvendeursglobal = $caPeriode;
        $chiffreAffairesvendeursimpaye = $resteAPayerPeriode;
        $ca_journalier = $caPeriode;
        $ca_journalierNonPaye = $resteAPayerPeriode;
        $chiffreAffaireMoisEnCours = $caPeriode;
        $chiffreAffaireMoisEnCourNonPaye = $resteAPayerPeriode;

        return view('dashboards.vendeur', compact(
            'periode',
            'periodeLabel',
            'periodeTitle',
            'dateDebut',
            'dateFin',
            'caPeriode',
            'encaissePeriode',
            'resteAPayerPeriode',
            'nbVentesPeriode',
            'panierMoyenPeriode',
            'tauxRecouvrement',
            'chiffreAffairesvendeurs',
            'chiffreAffaireMoisEnCours',
            'ventes',
            'ca_journalierNonPaye',
            'chiffreAffaireMoisEnCourNonPaye',
            'data',
            'data1',
            'dataReste',
            'ca_journalier',
            'labels',
            'derniersClients',
            'produitsStockFaible',
            'chiffreAffairesvendeursglobal',
            'chiffreAffairesvendeursimpaye'
        ));
    }
}
