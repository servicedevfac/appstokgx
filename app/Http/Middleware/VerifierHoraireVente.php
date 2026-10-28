<?php

namespace App\Http\Middleware;

use App\Models\Horaire;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierHoraireVente
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next)
    {
        $maintenant = Carbon::now();
        $jour = strtolower($maintenant->locale('fr')->isoFormat('dddd')); // ex: 'lundi'
        $heureActuelle = $maintenant->format('H:i');

        $horaire = Horaire::where('jour_semaine', $jour)->first();

        if (!$horaire) {
            return redirect()->back()->with('error', "Les horaires pour $jour ne sont pas configurés.");
        }

        if ($heureActuelle < $horaire->heure_ouverture || $heureActuelle > $horaire->heure_fermeture) {
            return response()->view('errors.horaires', [
                'ouverture' => $horaire->heure_ouverture,
                'fermeture' => $horaire->heure_fermeture,
                 'jour' => ucfirst($jour),
            ], 403);
        }

        return $next($request);
    }
}
