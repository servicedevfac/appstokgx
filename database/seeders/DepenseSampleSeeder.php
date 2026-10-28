<?php

namespace Database\Seeders;

use App\Models\Depense;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepenseSampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : null;

        $samples = [
            [
                'titre' => 'Loyer du magasin et dépôt',
                'categorie' => 'Loyer',
                'montant' => 150000,
                'date_depense' => now()->startOfMonth()->format('Y-m-d'),
                'mode_paiement' => 'Virement bancaire',
                'beneficiaire' => 'Société Immobilière du Littoral',
                'description' => 'Paiement mensuel du loyer du local commercial principal et entrepôt de stockage.',
                'user_id' => $userId,
            ],
            [
                'titre' => 'Facture CIE Électricité - Septembre',
                'categorie' => 'Électricité & Eau',
                'montant' => 38500,
                'date_depense' => now()->subDays(3)->format('Y-m-d'),
                'mode_paiement' => 'Wave',
                'beneficiaire' => 'Compagnie Ivoirienne d\'Électricité (CIE)',
                'description' => 'Consommation électrique des climatiseurs et éclairage showroom.',
                'user_id' => $userId,
            ],
            [
                'titre' => 'Carburant moto et livraison clients',
                'categorie' => 'Transport & Logistique',
                'montant' => 15000,
                'date_depense' => now()->subDay()->format('Y-m-d'),
                'mode_paiement' => 'Orange Money',
                'beneficiaire' => 'Station Shell San Pedro',
                'description' => 'Plein carburant pour les livraisons de la semaine.',
                'user_id' => $userId,
            ],
            [
                'titre' => 'Achat sacs d\'emballage et scotch personnalisé',
                'categorie' => 'Fournitures & Emballages',
                'montant' => 24000,
                'date_depense' => now()->format('Y-m-d'),
                'mode_paiement' => 'Espèces',
                'beneficiaire' => 'Grossiste Emballages & Co',
                'description' => 'Lot de 500 sacs kraft biodégradables et rouleaux d\'adhésif avec logo STOKGX.',
                'user_id' => $userId,
            ],
            [
                'titre' => 'Avance sur salaire - Magasinier',
                'categorie' => 'Salaires & Rémunérations',
                'montant' => 50000,
                'date_depense' => now()->subDays(2)->format('Y-m-d'),
                'mode_paiement' => 'Wave',
                'beneficiaire' => 'Konan Kouassi',
                'description' => 'Avance exceptionnelle sur le salaire de la première quinzaine.',
                'user_id' => $userId,
            ],
        ];

        foreach ($samples as $s) {
            Depense::create($s);
        }
    }
}
