<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Produit;
use App\Services\EntrepriseContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbonnementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() && ! $user->is_proprietaire) {
            return redirect()->route('superadmin.abonnements.index');
        }

        $entreprise = EntrepriseContext::current($user);
        $entreprises = $user->accessibleEntreprises()->load('abonnements');

        $abonnement = null;
        $historique = collect();
        $usage = [
            'utilisateurs_count' => 0,
            'max_utilisateurs'   => null,
            'produits_count'     => 0,
            'max_produits'       => null,
        ];

        if ($entreprise) {
            $abonnement = $entreprise->abonnementActuel() ?? $entreprise->ensureDefaultSubscription('pro');
            $historique = $entreprise->abonnements()->with('createur')->get();

            $usage['utilisateurs_count'] = DB::table('entreprise_user')
                ->where('entreprise_id', $entreprise->id)
                ->where('actif', true)
                ->count();
            $usage['max_utilisateurs'] = $abonnement?->max_utilisateurs;

            $usage['produits_count'] = Produit::withoutGlobalScope('entreprise')
                ->where('entreprise_id', $entreprise->id)
                ->count();
            $usage['max_produits'] = $abonnement?->max_produits;
        }

        $plans = Abonnement::PLANS;

        return view('admin.abonnements.index', compact(
            'entreprise',
            'entreprises',
            'abonnement',
            'historique',
            'usage',
            'plans'
        ));
    }
}

