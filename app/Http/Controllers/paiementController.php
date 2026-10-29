<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\EntrepriseContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class paiementController extends Controller
{
    public function store(Request $request, $venteId)
    {
        if (! $request->filled('montant_paye') && $request->filled('montant')) {
            $request->merge(['montant_paye' => $request->input('montant')]);
        }

        return app(VenteController::class)->payer($request, $venteId);
    }

    public function ticketpaiement($id)
    {
        $user = Auth::user();
        $paiement = Paiement::withoutGlobalScope('entreprise')->with('vente')->findOrFail($id);

        $entrepriseId = $paiement->entreprise_id ?? $paiement->vente?->entreprise_id;
        if (! $user->canAccessEntreprise((int) $entrepriseId)) {
            abort(403, 'Accès interdit au ticket de paiement d\'une autre entreprise.');
        }

        $currentEntrepriseId = EntrepriseContext::currentId($user);
        if ($currentEntrepriseId && (int) $entrepriseId !== (int) $currentEntrepriseId) {
            abort(403, 'Ce ticket de paiement n\'appartient pas à l\'entreprise active.');
        }

        if (! $user->hasAnyRole(['Propriétaire', 'Administrateur', 'super admin'])
            && (int) $paiement->vente?->user_id !== (int) $user->id
        ) {
            abort(403, 'Accès refusé à ce ticket de paiement.');
        }

        $pdf = PDF::loadView('admin.ticket_de_paiment.ticket_paiement', compact('paiement'))
            ->setPaper([0, 0, 360.77, 600], 'portrait');

        $date = now()->format('y_m_d');
        $numero = str_pad((string) $paiement->id, 3, '0', STR_PAD_LEFT);
        $filename = 'paiement_' . $date . '_' . $numero . '.pdf';

        return $pdf->stream($filename);
    }
}
