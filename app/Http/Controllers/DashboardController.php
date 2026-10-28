<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Vente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tableau de bord principal (Admin & Vendeur)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $driver = DB::getDriverName();

        // 1. Dashboard Administrateur / Super Admin
        if ($user->hasAnyRole(['Administrateur', 'super admin'])) {
            return $this->adminDashboard($driver, $request);
        }

        // 2. Dashboard Commercial / Gestionnaire
        if ($user->hasAnyRole(['Gestionnaire', 'Commercial'])) {
            return $this->vendeurDashboard($user, $driver, $request);
        }

        // 3. Fallback pour tout autre rôle
        return redirect()->route('ventes.index');
    }

    /**
     * Résout les bornes de date et les libellés selon la période demandée
     */
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
                    $periodeLabel = "Toutes les périodes (Global)";
                    $periodeTitle = 'global';
                    break;
                case 'mois':
                default:
                    $periode = 'mois';
                    $dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $dateFin = Carbon::now()->endOfMonth()->format('Y-m-d');
                    $periodeLabel = "Ce mois (" . Carbon::now()->translatedFormat('F Y') . ")";
                    $periodeTitle = 'mois';
                    break;
            }
        }

        return [
            'periode' => $periode,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'periodeLabel' => $periodeLabel,
            'periodeTitle' => $periodeTitle,
        ];
    }

    /**
     * Applique les contraintes de date sur une requête
     */
    private function applyDateFilter($query, string $dateColumn, string $periode, ?string $dateDebut, ?string $dateFin)
    {
        if ($periode === 'custom') {
            if ($dateDebut) {
                $query->whereDate($dateColumn, '>=', $dateDebut);
            }
            if ($dateFin) {
                $query->whereDate($dateColumn, '<=', $dateFin);
            }
            return;
        }

        switch ($periode) {
            case 'jour':
                $query->whereDate($dateColumn, Carbon::today()->format('Y-m-d'));
                break;
            case 'semaine':
                $query->whereDate($dateColumn, '>=', Carbon::now()->startOfWeek()->format('Y-m-d'))
                      ->whereDate($dateColumn, '<=', Carbon::now()->endOfWeek()->format('Y-m-d'));
                break;
            case 'mois':
                $query->whereYear($dateColumn, Carbon::now()->year)
                      ->whereMonth($dateColumn, Carbon::now()->month);
                break;
            case 'annee':
                $query->whereYear($dateColumn, Carbon::now()->year);
                break;
            case 'tout':
                // Pas de restriction de date
                break;
            default:
                $query->whereYear($dateColumn, Carbon::now()->year)
                      ->whereMonth($dateColumn, Carbon::now()->month);
                break;
        }
    }

    /**
     * Données du tableau de bord Administrateur
     */
    private function adminDashboard(string $driver, Request $request)
    {
        $periodData = $this->resolvePeriod($request);
        $periode = $periodData['periode'];
        $dateDebut = $periodData['dateDebut'];
        $dateFin = $periodData['dateFin'];
        $periodeLabel = $periodData['periodeLabel'];
        $periodeTitle = $periodData['periodeTitle'];

        // Ventes globales (10 dernières)
        $ventes = Vente::all();
        $derniersVentes = Vente::where('statut', 'valide')->orderByDesc('date_vente')->take(10)->get();

        // Clients récents
        $derniersClients = Vente::with('client')
            ->select('client_id')
            ->groupBy('client_id')
            ->orderByRaw('MAX(date_vente) DESC')
            ->take(10)
            ->get();

        // ── Indicateurs filtrés par la période sélectionnée ──
        $ventesPeriodeQuery = Vente::where('statut', 'valide');
        $this->applyDateFilter($ventesPeriodeQuery, 'date_vente', $periode, $dateDebut, $dateFin);

        $caPeriode = (float) (clone $ventesPeriodeQuery)->sum('montant_total');
        $nbVentesPeriode = (int) (clone $ventesPeriodeQuery)->count();
        $panierMoyenPeriode = $nbVentesPeriode > 0 ? round($caPeriode / $nbVentesPeriode) : 0;
        $resteAPayerPeriode = (float) (clone $ventesPeriodeQuery)->sum('reste_a_payer');

        // Paiements (Encaissé) sur la période
        $paiementsPeriodeQuery = Paiement::whereHas('vente', fn ($q) => $q->where('statut', 'valide'));
        $this->applyDateFilter($paiementsPeriodeQuery, 'created_at', $periode, $dateDebut, $dateFin);
        $encaissePeriode = (float) (clone $paiementsPeriodeQuery)->sum('montant');

        // Dépenses sur la période
        $depensesPeriodeQuery = Depense::query();
        $this->applyDateFilter($depensesPeriodeQuery, 'date_depense', $periode, $dateDebut, $dateFin);
        $depensesPeriode = (float) (clone $depensesPeriodeQuery)->sum('montant');

        // Bénéfice Net sur la période
        $beneficeNetPeriode = $encaissePeriode - $depensesPeriode;

        $derniersDepenses = Depense::with('user')->orderByDesc('date_depense')->take(5)->get();

        // Produits en alerte de stock
        $produitsStockFaible = Produit::select('produits.*')
            ->join('mouvement_stocks', 'produits.id', '=', 'mouvement_stocks.produit_id')
            ->selectRaw('
                SUM(CASE WHEN type_mouvement = \'entree\' THEN quantite ELSE 0 END) -
                SUM(CASE WHEN type_mouvement = \'sortie\' THEN quantite ELSE 0 END) as stock_actuel
            ')
            ->groupBy('produits.id', 'produits.nom', 'produits.prix', 'produits.seuil_alerte', 'produits.alerte_envoyee', 'produits.created_at', 'produits.updated_at')
            ->havingRaw('(SUM(CASE WHEN type_mouvement = \'entree\' THEN quantite ELSE 0 END) - SUM(CASE WHEN type_mouvement = \'sortie\' THEN quantite ELSE 0 END)) <= seuil_alerte')
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
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql'  => "TO_CHAR(created_at, 'YYYY-MM')",
            default  => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $dateExprDepense = match ($driver) {
            'sqlite' => "strftime('%Y-%m', date_depense)",
            'pgsql'  => "TO_CHAR(date_depense, 'YYYY-MM')",
            default  => "DATE_FORMAT(date_depense, '%Y-%m')",
        };

        $startMonthLimit = Carbon::now()->subMonths(11)->startOfMonth()->format('Y-m-d');

        // Ventes par mois
        $salesByMonth = Vente::selectRaw("$dateExprVente as month, SUM(montant_total) as total")
            ->where('date_vente', '>=', $startMonthLimit)
            ->where('statut', 'valide')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Paiements par mois
        $paymentsByMonth = Paiement::whereHas('vente', fn ($q) => $q->where('statut', 'valide'))
            ->selectRaw("$dateExprPaiement as month, SUM(montant) as total")
            ->where('created_at', '>=', $startMonthLimit)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Dépenses par mois
        $depensesByMonth = Depense::selectRaw("$dateExprDepense as month, SUM(montant) as total")
            ->where('date_depense', '>=', $startMonthLimit)
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = $months->map(fn ($m) => (float) ($salesByMonth[$m] ?? 0))->values();
        $data1 = $months->map(fn ($m) => (float) ($paymentsByMonth[$m] ?? 0))->values();
        $dataDepenses = $months->map(fn ($m) => (float) ($depensesByMonth[$m] ?? 0))->values();
        $dataBenefice = $months->map(fn ($m) => (float) (($paymentsByMonth[$m] ?? 0) - ($depensesByMonth[$m] ?? 0)))->values();
        $dataReste = $months->map(fn ($m) => max(0, (float) (($salesByMonth[$m] ?? 0) - ($paymentsByMonth[$m] ?? 0))))->values();

        // Variables de compatibilité
        $ca_journalier = $encaissePeriode;
        $chiffreAffaireMoisEnCours = $encaissePeriode;
        $chiffreAffaires = $encaissePeriode;
        $chiffreAffairesGlobaux = $caPeriode;
        $totalVentesNonPayes = $resteAPayerPeriode;
        $depensesMois = $depensesPeriode;
        $beneficeNetMois = $beneficeNetPeriode;

        return view('dashboards.admin', compact(
            'periode',
            'periodeLabel',
            'periodeTitle',
            'dateDebut',
            'dateFin',
            'caPeriode',
            'encaissePeriode',
            'resteAPayerPeriode',
            'depensesPeriode',
            'beneficeNetPeriode',
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

    /**
     * Données du tableau de bord Vendeur / Commercial
     */
    private function vendeurDashboard($user, string $driver, Request $request)
    {
        $userId = $user->id;
        $periodData = $this->resolvePeriod($request);
        $periode = $periodData['periode'];
        $dateDebut = $periodData['dateDebut'];
        $dateFin = $periodData['dateFin'];
        $periodeLabel = $periodData['periodeLabel'];
        $periodeTitle = $periodData['periodeTitle'];

        $ventes = Vente::where('user_id', $userId)
            ->orderByDesc('date_vente')
            ->take(10)
            ->get();

        // ── Indicateurs filtrés par la période sélectionnée ──
        $ventesPeriodeQuery = DB::table('ventes')
            ->where('statut', 'valide')
            ->where('user_id', $userId);
        $this->applyDateFilter($ventesPeriodeQuery, 'date_vente', $periode, $dateDebut, $dateFin);

        $caPeriode = (float) (clone $ventesPeriodeQuery)->sum('montant_total');
        $encaissePeriode = (float) (clone $ventesPeriodeQuery)->sum('montant_paye');
        $resteAPayerPeriode = (float) (clone $ventesPeriodeQuery)->sum('reste_a_payer');
        $nbVentesPeriode = (int) (clone $ventesPeriodeQuery)->count();
        $panierMoyenPeriode = $nbVentesPeriode > 0 ? round($caPeriode / $nbVentesPeriode) : 0;
        $tauxRecouvrement = $caPeriode > 0 ? round(($encaissePeriode / $caPeriode) * 100, 1) : 0;

        $derniersClients = Vente::with('client')
            ->where('user_id', $userId)
            ->select('client_id')
            ->groupBy('client_id')
            ->orderByRaw('MAX(date_vente) DESC')
            ->take(10)
            ->get();

        $produitsStockFaible = Produit::select('produits.*')
            ->join('mouvement_stocks', 'produits.id', '=', 'mouvement_stocks.produit_id')
            ->selectRaw('
                SUM(CASE WHEN type_mouvement = \'entree\' THEN quantite ELSE 0 END) -
                SUM(CASE WHEN type_mouvement = \'sortie\' THEN quantite ELSE 0 END) as stock_actuel
            ')
            ->groupBy('produits.id', 'produits.nom', 'produits.prix', 'produits.seuil_alerte', 'produits.alerte_envoyee', 'produits.created_at', 'produits.updated_at')
            ->havingRaw('(SUM(CASE WHEN type_mouvement = \'entree\' THEN quantite ELSE 0 END) - SUM(CASE WHEN type_mouvement = \'sortie\' THEN quantite ELSE 0 END)) <= seuil_alerte')
            ->get();

        // Graphiques
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
            ->whereHas('vente', fn ($q) => $q->where('user_id', $userId)->where('statut', 'valide'))
            ->selectRaw("$dateExprPaiement as month, SUM(montant) as total")
            ->where('date_paiement', '>=', $startDate)
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = $months->map(fn ($m) => (float) ($sales[$m] ?? 0))->values();
        $data1 = $months->map(fn ($m) => (float) ($payments[$m] ?? 0))->values();
        $dataReste = $months->map(fn ($m) => max(0, (float) (($sales[$m] ?? 0) - ($payments[$m] ?? 0))))->values();

        // Variables de compatibilité
        $chiffreAffairesvendeurs = $encaissePeriode;
        $chiffreAffairesvendeursglobal = $caPeriode;
        $chiffreAffairesvendeursimpaye = $resteAPayerPeriode;
        $ca_journalier = $encaissePeriode;
        $ca_journalierNonPaye = $resteAPayerPeriode;
        $chiffreAffaireMoisEnCours = $encaissePeriode;
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

