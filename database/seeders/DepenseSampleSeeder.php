<?php

namespace Database\Seeders;

use App\Models\Depense;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepenseSampleSeeder extends Seeder
{
    public function run(): void
    {
        $entreprise = Entreprise::where('code', 'ENT-MAIN01')->first()
            ?? Entreprise::where('is_demo', false)->orderBy('id')->first();

        if (! $entreprise) {
            return;
        }

        $entrepriseId = $entreprise->id;
        $userId = $entreprise->proprietaire_id
            ?? User::where('current_entreprise_id', $entrepriseId)->value('id');

        $samples = [
            [
                'titre'         => 'Loyer du magasin et dépôt',
                'categorie'     => 'Loyer',
                'montant'       => 150000,
                'date_depense'  => now()->startOfMonth()->format('Y-m-d'),
                'mode_paiement' => 'Virement bancaire',
                'beneficiaire'  => 'Société Immobilière du Littoral',
                'description'   => 'Paiement mensuel du loyer du local commercial principal et entrepôt de stockage.',
                'statut'        => 'validee',
                'user_id'       => $userId,
                'entreprise_id' => $entrepriseId,
            ],
            [
                'titre'         => 'Facture CIE Électricité',
                'categorie'     => 'Électricité & Eau',
                'montant'       => 38500,
                'date_depense'  => now()->subDays(3)->format('Y-m-d'),
                'mode_paiement' => 'Wave',
                'beneficiaire'  => 'Compagnie Ivoirienne d\'Électricité (CIE)',
                'description'   => 'Consommation électrique des climatiseurs et éclairage showroom.',
                'statut'        => 'validee',
                'user_id'       => $userId,
                'entreprise_id' => $entrepriseId,
            ],
            [
                'titre'         => 'Carburant moto et livraison clients',
                'categorie'     => 'Transport & Logistique',
                'montant'       => 15000,
                'date_depense'  => now()->subDay()->format('Y-m-d'),
                'mode_paiement' => 'Orange Money',
                'beneficiaire'  => 'Station Shell',
                'description'   => 'Plein carburant pour les livraisons de la semaine.',
                'statut'        => 'validee',
                'user_id'       => $userId,
                'entreprise_id' => $entrepriseId,
            ],
            [
                'titre'         => 'Achat sacs d\'emballage et adhésifs',
                'categorie'     => 'Fournitures & Emballages',
                'montant'       => 24000,
                'date_depense'  => now()->format('Y-m-d'),
                'mode_paiement' => 'Espèces',
                'beneficiaire'  => 'Grossiste Emballages & Co',
                'description'   => 'Lot de 500 sacs kraft biodégradables et rouleaux d\'adhésif avec logo.',
                'statut'        => 'validee',
                'user_id'       => $userId,
                'entreprise_id' => $entrepriseId,
            ],
        ];

        foreach ($samples as $s) {
            Depense::withoutGlobalScope('entreprise')->updateOrCreate(
                ['titre' => $s['titre'], 'entreprise_id' => $entrepriseId],
                $s
            );
        }
    }
}
