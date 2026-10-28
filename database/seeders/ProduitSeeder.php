<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produit;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produits = [
            ['nom' => 'Ordinateur Portable HP', 'prix' => 250000, 'seuil_alerte' => 5],
            ['nom' => 'Clavier Sans Fil Logitech', 'prix' => 15000, 'seuil_alerte' => 10],
            ['nom' => 'Souris Optique Dell', 'prix' => 5000, 'seuil_alerte' => 15],
            ['nom' => 'Ecran 24 Pouces Samsung', 'prix' => 75000, 'seuil_alerte' => 5],
            ['nom' => 'Imprimante Epson', 'prix' => 45000, 'seuil_alerte' => 3],
            ['nom' => 'Disque Dur Externe 1To', 'prix' => 35000, 'seuil_alerte' => 8],
            ['nom' => 'Clé USB 64Go', 'prix' => 8000, 'seuil_alerte' => 20],
        ];

        foreach ($produits as $produit) {
            Produit::firstOrCreate(['nom' => $produit['nom']], $produit);
        }
    }
}
