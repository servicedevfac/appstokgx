<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Vente;
use App\Models\Paiement;

class paiementController extends Controller
{
    public function store(Request $request, $venteId)
    {
        $vente = Vente::findOrFail($venteId);

        // 1. Vérification si la vente est déjà soldée
        if ($vente->est_paye || $vente->reste_a_payer <= 0) {
            return back()->with('error', 'Cette vente est déjà entièrement soldée. Aucun paiement supplémentaire ne peut être effectué.');
        }

        // 2. Validation des entrées
        $request->validate([
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|string|max:50',
        ], [
            'montant.required' => 'Veuillez saisir un montant de paiement.',
            'montant.numeric'  => 'Le montant doit être une valeur numérique.',
            'montant.min'      => 'Le montant doit être au moins de 1 FCFA.',
            'mode_paiement.required' => 'Veuillez sélectionner un mode de paiement.',
        ]);

        $montantSaisi = (float) $request->montant;
        $resteActuel = (float) $vente->reste_a_payer;

        // 3. Vérification critique : interdire de payer plus que le reste à payer
        if ($montantSaisi > $resteActuel) {
            return back()->with(
                'error',
                "Paiement refusé : le montant saisi (" . number_format($montantSaisi, 0, ',', ' ') . " FCFA) dépasse le solde restant à payer (" . number_format($resteActuel, 0, ',', ' ') . " FCFA)."
            )->withInput();
        }

        // 4. Calcul du nouveau reste à payer
        $nouveauMontantPaye = (float) $vente->montant_paye + $montantSaisi;
        $nouveauReste = max(0, (float) $vente->montant_total - $nouveauMontantPaye);

        // 5. Enregistrement du paiement
        $paiement = $vente->paiements()->create([
            'montant'       => $montantSaisi,
            'mode_paiement' => $request->mode_paiement,
            'date_paiement' => now()->format('Y-m-d'),
            'reste_a_payer' => $nouveauReste,
        ]);

        // 6. Mise à jour de la vente
        $vente->montant_paye = $nouveauMontantPaye;
        $vente->reste_a_payer = $nouveauReste;
        $vente->est_paye = ($nouveauReste <= 0);
        $vente->save();

        $message = "Paiement de " . number_format($montantSaisi, 0, ',', ' ') . " FCFA enregistré avec succès.";
        if ($nouveauReste > 0) {
            $message .= " Reste à payer : " . number_format($nouveauReste, 0, ',', ' ') . " FCFA.";
        } else {
            $message .= " La vente est désormais totalement soldée !";
        }

        return back()->with('success', $message);
    }

    public function ticketpaiement($id)
    {
        $paiement = Paiement::with('vente')->findOrFail($id);

        $pdf = PDF::loadView('admin.ticket_de_paiment.ticket_paiement', compact('paiement'))
            ->setPaper([0, 0, 360.77, 600], 'portrait');

        $date = now()->format('y_m_d'); // Exemple : 25_09_18
        $numero = str_pad($paiement->id, 3, '0', STR_PAD_LEFT);

        $filename = 'paiement_' . $date . '_' . $numero . '.pdf';

        return $pdf->stream($filename);
    }
}
