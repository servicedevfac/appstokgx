<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\StockAlerte;
use Illuminate\Support\Facades\Notification;

class VerifierStock extends Command
{
    protected $signature = 'stock:alert';
    protected $description = 'Envoie une alerte si le stock des produits est inférieur au seuil';

    public function handle()
    {
        $this->info('Vérification des stocks...');

        // Récupérer tous les admins ou utilisateurs à notifier
        $admins = User::role('Administrateur')->get(); // ou User::where('role', 'admin')->get();

        // Récupérer tous les produits
        $produits = Produit::all();

        // Collecter tous les produits avec un stock inférieur au seuil d'alerte
        $produitsEnAlerte = [];
        $produitsIds = [];

        foreach ($produits as $produit) {
            if ($produit->stock_actuel <= $produit->seuil_alerte) {
                // Créer un tableau avec les données du produit
                $produitData = $produit->toArray();
                // Ajouter explicitement les clés nécessaires pour la notification
                $produitData['stock'] = $produit->stock_actuel;
                $produitData['seuil'] = $produit->seuil_alerte;
                $produitData['url'] = url('/produits/' . $produit->id);
                $produitsEnAlerte[] = $produitData;
                $produitsIds[] = $produit->id;

                echo "Produit en alerte : {$produit->nom}\n";
            } else {
                echo "Stock OK pour : {$produit->nom}\n";
            }
        }

        // Envoyer une seule notification si des produits sont en alerte
        if (count($produitsEnAlerte) > 0) {
            try {
                Notification::sendNow($admins, new StockAlerte($produitsEnAlerte));

                // Mettre à jour le statut d'alerte pour tous les produits concernés
                Produit::whereIn('id', $produitsIds)->update([
                    'alerte_envoyee' => true,
                    'last_alerted_at' => now()
                ]);

                $this->info('Alerte envoyée pour ' . count($produitsEnAlerte) . ' produit(s).');
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'envoi de l'alerte: {$e->getMessage()}");
                $this->info("Les alertes seront enregistrées sans envoi d'email.");

                // Mettre à jour le statut d'alerte même en cas d'erreur
                Produit::whereIn('id', $produitsIds)->update([
                    'alerte_envoyee' => true,
                    'last_alerted_at' => now()
                ]);
            }
        } else {
            $this->info('Aucun produit n\'a atteint le seuil d\'alerte.');
        }



        $this->info('Vérification terminée.');
    }
}
