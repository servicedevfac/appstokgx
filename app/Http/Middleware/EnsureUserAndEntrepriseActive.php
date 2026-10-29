<?php

namespace App\Http\Middleware;

use App\Services\EntrepriseContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAndEntrepriseActive
{
    /**
     * Routes opérationnelles propres à une entreprise (interdites au Super Admin SaaS global
     * et soumises à un abonnement d'entreprise actif).
     */
    private const ENTREPRISE_OPERATIONAL_ROUTES = [
        'ventes.*',
        'produits.*',
        'categories.*',
        'mouvementStocks.*',
        'fournisseurs.*',
        'achats.*',
        'clients.*',
        'depenses.*',
        'employes.*',
        'avances-salaires.*',
        'imports.*',
        'admin.horaires.*',
        'paiements.*',
        'export',
        'export.*',
        'entreprises.*',
        'dashboard.consolide',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->actif) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte a été désactivé.',
                ]);
            }

            // 1. Le Super Administrateur gère uniquement le SaaS global
            if ($user->isSuperAdmin() && ! $user->is_proprietaire) {
                if ($request->routeIs('entreprises.*')) {
                    return redirect()->route('superadmin.entreprises.index');
                }

                if ($request->routeIs(self::ENTREPRISE_OPERATIONAL_ROUTES) || $request->routeIs('abonnement.*')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Le Super Administrateur gère uniquement la plateforme SaaS globale.',
                        ], 403);
                    }

                    return redirect()->route('superadmin.dashboard')->with(
                        'error',
                        'Le compte Super Administrateur gère uniquement la plateforme SaaS globale et n\'intervient pas dans la gestion opérationnelle d\'une entreprise.'
                    );
                }

                return $next($request);
            }

            // 2. Utilisateur métier : synchronisation de l'entreprise active
            $activeId = EntrepriseContext::currentId($user);
            if ($activeId && session('current_entreprise_id') !== $activeId) {
                session(['current_entreprise_id' => $activeId]);
            }

            // 3. Vérification du rattachement à une entreprise et de la validité de son abonnement sur les routes métier
            if ($request->routeIs(self::ENTREPRISE_OPERATIONAL_ROUTES) || $request->routeIs('dashboard')) {
                $entreprise = EntrepriseContext::current($user);

                if (! $entreprise) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Tout utilisateur doit être rattaché à une entreprise active.',
                        ], 403);
                    }

                    return redirect()->route('abonnement.index')->with(
                        'error',
                        'Votre compte utilisateur doit être rattaché à une entreprise active pour accéder aux modules de gestion.'
                    );
                }

                if (! $entreprise->hasActiveSubscription()) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'L\'abonnement de votre entreprise a expiré ou est suspendu.',
                        ], 403);
                    }

                    return redirect()->route('abonnement.index')->with(
                        'error',
                        'L\'abonnement de votre entreprise (' . $entreprise->nom . ') est expiré ou suspendu. Veuillez renouveler votre abonnement pour continuer.'
                    );
                }
            }
        }

        return $next($request);
    }
}
