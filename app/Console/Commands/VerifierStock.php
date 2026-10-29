<?php

namespace App\Console\Commands;

use App\Models\Achat;
use App\Models\Entreprise;
use App\Models\Produit;
use App\Models\Vente;
use App\Notifications\StockAlerte;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class VerifierStock extends Command
{
    protected $signature = 'stock:alert';
    protected $description = 'Vérifie par entreprise les stocks faibles/ruptures ainsi que les échéances de créances et dettes sans doublon abusif';

    public function handle()
    {
        $this->info('Vérification des stocks et échéances par entreprise...');

        $entreprises = Entreprise::where('actif', true)->get();

        foreach ($entreprises as $entreprise) {
            $produits = Produit::withoutGlobalScopes()
                ->where('entreprise_id', $entreprise->id)
                ->get();

            $produitsEnAlerte = [];
            $produitsIds = [];

            foreach ($produits as $produit) {
                $estEnAlerte = $produit->stock <= $produit->seuil_alerte;

                if ($estEnAlerte) {
                    // Anti-spam : ne pas renvoyer d'alerte si déjà envoyée il y a moins de 24h
                    $dejaAlerteRecemment = $produit->alerte_envoyee
                        && $produit->last_alerted_at
                        && $produit->last_alerted_at->gt(now()->subHours(24));

                    if (!$dejaAlerteRecemment) {
                        $produitData = $produit->toArray();
                        $produitData['stock'] = $produit->stock;
                        $produitData['seuil'] = $produit->seuil_alerte;
                        $produitData['statut_stock'] = $produit->stock <= 0 ? 'RUPTURE' : 'STOCK FAIBLE';
                        $produitData['url'] = url('/produits');
                        $produitsEnAlerte[] = $produitData;
                        $produitsIds[] = $produit->id;
                    }
                } elseif ($produit->alerte_envoyee) {
                    // Réinitialiser le flag si le stock a été réapprovisionné au-dessus du seuil
                    Produit::withoutGlobalScopes()->where('id', $produit->id)->update([
                        'alerte_envoyee' => false,
                    ]);
                }
            }

            // Vérification des créances clients à échéance dépassée
            $creancesEchues = Vente::withoutGlobalScopes()
                ->where('entreprise_id', $entreprise->id)
                ->where('statut', '!=', 'annulee')
                ->where('reste_a_payer', '>', 0)
                ->whereNotNull('date_echeance')
                ->whereDate('date_echeance', '<=', now()->toDateString())
                ->count();

            // Vérification des dettes fournisseurs à échéance dépassée
            $dettesEchues = Achat::withoutGlobalScopes()
                ->where('entreprise_id', $entreprise->id)
                ->where('statut_reception', '!=', 'annule')
                ->where('reste_a_payer', '>', 0)
                ->whereNotNull('date_echeance')
                ->whereDate('date_echeance', '<=', now()->toDateString())
                ->count();

            if (count($produitsEnAlerte) > 0) {
                $destinataires = $entreprise->utilisateurs()
                    ->where('users.actif', true)
                    ->wherePivotIn('role', ['Propriétaire', 'Administrateur', 'Gestionnaire'])
                    ->get();

                if ($destinataires->isNotEmpty()) {
                    try {
                        Notification::sendNow($destinataires, new StockAlerte($produitsEnAlerte));
                    } catch (\Exception $e) {
                        $this->warn("Alerte enregistrée localement (email non envoyé) pour {$entreprise->nom}: {$e->getMessage()}");
                    }
                }

                Produit::withoutGlobalScopes()
                    ->whereIn('id', $produitsIds)
                    ->update([
                        'alerte_envoyee' => true,
                        'last_alerted_at' => now(),
                    ]);

                $this->info("[{$entreprise->nom}] Alerte traitée pour " . count($produitsEnAlerte) . " produit(s).");
            }

            if ($creancesEchues > 0 || $dettesEchues > 0) {
                $this->info("[{$entreprise->nom}] Échéances détectées : {$creancesEchues} créance(s) client, {$dettesEchues} dette(s) fournisseur.");
            }
        }

        $this->info('Vérification terminée.');
        return self::SUCCESS;
    }
}
