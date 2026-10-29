<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Entreprise;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProduitSeeder extends Seeder
{
    /**
     * Initialise les catégories, les produits et leurs stocks initiaux pour l'entreprise principale.
     */
    public function run(): void
    {
        $entreprise = Entreprise::where('code', 'ENT-MAIN01')->first()
            ?? Entreprise::where('is_demo', false)->orderBy('id')->first();

        if (! $entreprise) {
            return;
        }

        $entrepriseId = $entreprise->id;
        $userId = $entreprise->proprietaire_id ?? User::where('current_entreprise_id', $entrepriseId)->value('id');

        // 1. Catégories de produits
        $catInfo = Categorie::withoutGlobalScope('entreprise')->updateOrCreate(
            ['nom' => 'Ordinateurs & Écrans', 'entreprise_id' => $entrepriseId],
            ['description' => 'Postes informatiques, ordinateurs portables et moniteurs']
        );

        $catPerif = Categorie::withoutGlobalScope('entreprise')->updateOrCreate(
            ['nom' => 'Périphériques & Accessoires', 'entreprise_id' => $entrepriseId],
            ['description' => 'Claviers, souris et accessoires bureautiques']
        );

        $catStockage = Categorie::withoutGlobalScope('entreprise')->updateOrCreate(
            ['nom' => 'Stockage & Impression', 'entreprise_id' => $entrepriseId],
            ['description' => 'Supports de stockage externe et imprimantes']
        );

        // 2. Catalogue Produits avec stock initial
        $produits = [
            [
                'reference'     => 'PRD-HP01',
                'nom'           => 'Ordinateur Portable HP',
                'categorie_id'  => $catInfo->id,
                'prix_achat'    => 195000,
                'prix'          => 250000,
                'seuil_alerte'  => 5,
                'stock_initial' => 15,
            ],
            [
                'reference'     => 'PRD-LOG02',
                'nom'           => 'Clavier Sans Fil Logitech',
                'categorie_id'  => $catPerif->id,
                'prix_achat'    => 10000,
                'prix'          => 15000,
                'seuil_alerte'  => 10,
                'stock_initial' => 30,
            ],
            [
                'reference'     => 'PRD-DEL03',
                'nom'           => 'Souris Optique Dell',
                'categorie_id'  => $catPerif->id,
                'prix_achat'    => 3000,
                'prix'          => 5000,
                'seuil_alerte'  => 15,
                'stock_initial' => 45,
            ],
            [
                'reference'     => 'PRD-SAM04',
                'nom'           => 'Ecran 24 Pouces Samsung',
                'categorie_id'  => $catInfo->id,
                'prix_achat'    => 55000,
                'prix'          => 75000,
                'seuil_alerte'  => 5,
                'stock_initial' => 12,
            ],
            [
                'reference'     => 'PRD-EPS05',
                'nom'           => 'Imprimante Epson',
                'categorie_id'  => $catStockage->id,
                'prix_achat'    => 32000,
                'prix'          => 45000,
                'seuil_alerte'  => 3,
                'stock_initial' => 10,
            ],
            [
                'reference'     => 'PRD-HDD06',
                'nom'           => 'Disque Dur Externe 1To',
                'categorie_id'  => $catStockage->id,
                'prix_achat'    => 25000,
                'prix'          => 35000,
                'seuil_alerte'  => 8,
                'stock_initial' => 20,
            ],
            [
                'reference'     => 'PRD-USB07',
                'nom'           => 'Clé USB 64Go',
                'categorie_id'  => $catStockage->id,
                'prix_achat'    => 5000,
                'prix'          => 8000,
                'seuil_alerte'  => 20,
                'stock_initial' => 50,
            ],
        ];

        foreach ($produits as $data) {
            $stockInit = $data['stock_initial'];
            unset($data['stock_initial']);
            $data['entreprise_id'] = $entrepriseId;

            $produit = Produit::withoutGlobalScope('entreprise')->updateOrCreate(
                ['nom' => $data['nom'], 'entreprise_id' => $entrepriseId],
                $data
            );

            $hasMovement = MouvementStock::withoutGlobalScope('entreprise')
                ->where('entreprise_id', $entrepriseId)
                ->where('produit_id', $produit->id)
                ->exists();

            if (! $hasMovement && $stockInit > 0) {
                MouvementStock::withoutGlobalScope('entreprise')->create([
                    'entreprise_id'     => $entrepriseId,
                    'produit_id'        => $produit->id,
                    'user_id'           => $userId,
                    'quantite'          => $stockInit,
                    'type_mouvement'    => 'entree',
                    'date_mouvement'    => now()->toDateString(),
                    'motif'             => 'Stock initial à l\'ouverture',
                    'reference_externe' => 'SEED-' . $data['reference'],
                ]);
            }
        }
    }
}
