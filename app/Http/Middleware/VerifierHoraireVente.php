<?php

namespace App\Http\Middleware;

use App\Models\Horaire;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class VerifierHoraireVente
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $maintenant = Carbon::now();
        $jour = strtolower($maintenant->locale('fr')->isoFormat('dddd')); // ex: 'lundi'
        $heureActuelle = $maintenant->format('H:i');

        $horaire = Horaire::where('jour_semaine', $jour)->first();

        // Si aucun horaire n'est configuré pour cette entreprise, ou si les heures sont vides, autoriser par défaut
        if (!$horaire || empty($horaire->heure_ouverture) || empty($horaire->heure_fermeture)) {
            return $next($request);
        }

        $ouverture = substr($horaire->heure_ouverture, 0, 5);
        $fermeture = substr($horaire->heure_fermeture, 0, 5);

        if ($heureActuelle < $ouverture || $heureActuelle > $fermeture) {
            return response()->view('errors.horaires', [
                'ouverture' => $horaire->heure_ouverture,
                'fermeture' => $horaire->heure_fermeture,
                'jour' => ucfirst($jour),
            ], 403);
        }

        return $next($request);
    }
}
