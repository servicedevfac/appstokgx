<?php

namespace App\Services;

use App\Models\Achat;
use App\Models\AvanceSalaire;
use App\Models\Categorie;
use App\Models\Client;
use App\Models\Depense;
use App\Models\Detail_vente;
use App\Models\DetailAchat;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Fournisseur;
use App\Models\ImportBatch;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\PaiementFournisseur;
use App\Models\Produit;
use App\Models\RemboursementAvance;
use App\Models\RetourClient;
use App\Models\RetourFournisseur;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoService
{
    public const DEMO_ENTREPRISE_CODE = 'ENT-DEMO';
    public const DEMO_USER_EMAIL = 'demo@stokgx.com';

    /**
     * Initialise ou réinitialise uniquement le périmètre de démonstration (RM-075 à RM-077)
     */
    public static function resetDemoEnvironment(): array
    {
        return DB::transaction(function () {
            $demoUser = User::updateOrCreate(
                ['email' => self::DEMO_USER_EMAIL],
                [
                    'nom'             => 'Compte',
                    'prenom'          => 'Démonstration',
                    'telephone'       => '0700000000',
                    'password'        => Hash::make('demo1234'),
                    'actif'           => true,
                    'is_proprietaire' => false,
                    'is_demo'         => true,
                ]
            );

            $demoEntreprise = Entreprise::updateOrCreate(
                ['code' => self::DEMO_ENTREPRISE_CODE],
                [
                    'nom'                    => 'STOKGX Démo & Sandbox',
                    'email'                  => 'contact@demo.stokgx.com',
                    'telephone'              => '+225 07 00 00 00 00',
                    'adresse'                => 'Zone de Démonstration, Abidjan',
                    'devise'                 => 'FCFA',
                    'plafond_avance_salaire' => 100000,
                    'proprietaire_id'        => $demoUser->id,
                    'actif'                  => true,
                    'is_demo'                => true,
                ]
            );

            // Sécurité absolue : vérifier que l'entreprise cible est bien marquée is_demo = true
            if (! $demoEntreprise->is_demo) {
                throw new \RuntimeException('Sécurité : refus de réinitialiser une entreprise non marquée comme démonstration.');
            }

            $entId = $demoEntreprise->id;

            // Nettoyage strictement confiné à l'entreprise de démonstration
            RemboursementAvance::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            AvanceSalaire::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Employe::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            RetourClient::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Paiement::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();

            $demoVenteIds = Vente::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->pluck('id');
            Detail_vente::whereIn('vente_id', $demoVenteIds)->delete();
            Vente::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();

            RetourFournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            PaiementFournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();

            $demoAchatIds = Achat::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->pluck('id');
            DetailAchat::whereIn('achat_id', $demoAchatIds)->delete();
            Achat::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();

            MouvementStock::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Produit::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Categorie::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Client::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Fournisseur::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            Depense::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();
            ImportBatch::withoutGlobalScope('entreprise')->where('entreprise_id', $entId)->delete();

            $demoUser->update(['current_entreprise_id' => $entId]);
            DB::table('entreprise_user')->updateOrInsert(
                ['entreprise_id' => $entId, 'user_id' => $demoUser->id],
                ['role' => 'Administrateur', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            // Création des données fictives de démonstration
            $catAlim = Categorie::withoutGlobalScope('entreprise')->create([
                'entreprise_id' => $entId,
                'nom'           => 'Alimentation Générale (Démo)',
                'description'   => 'Produits alimentaires de démonstration',
            ]);

            $prodRiz = Produit::withoutGlobalScope('entreprise')->create([
                'entreprise_id'  => $entId,
                'categorie_id'   => $catAlim->id,
                'nom'            => 'Riz Parfumé 25kg (Démo)',
                'reference'      => 'DEMO-RIZ-25',
                'prix_achat'     => 14000,
                'prix'           => 18000,
                'seuil_alerte'   => 10,
                'alerte_envoyee' => false,
            ]);

            $prodHuile = Produit::withoutGlobalScope('entreprise')->create([
                'entreprise_id'  => $entId,
                'categorie_id'   => $catAlim->id,
                'nom'            => 'Huile Raffinée 5L (Démo)',
                'reference'      => 'DEMO-HUILE-5L',
                'prix_achat'     => 4500,
                'prix'           => 6000,
                'seuil_alerte'   => 5,
                'alerte_envoyee' => false,
            ]);

            // Mouvements de stock fictifs
            MouvementStock::withoutGlobalScope('entreprise')->create([
                'entreprise_id'     => $entId,
                'produit_id'        => $prodRiz->id,
                'user_id'           => $demoUser->id,
                'quantite'          => 50,
                'type_mouvement'    => 'entree',
                'date_mouvement'    => now()->subDays(5)->format('Y-m-d'),
                'motif'             => 'Stock initial de démonstration',
                'reference_externe' => 'DEMO-INIT-RIZ',
            ]);

            MouvementStock::withoutGlobalScope('entreprise')->create([
                'entreprise_id'     => $entId,
                'produit_id'        => $prodHuile->id,
                'user_id'           => $demoUser->id,
                'quantite'          => 30,
                'type_mouvement'    => 'entree',
                'date_mouvement'    => now()->subDays(5)->format('Y-m-d'),
                'motif'             => 'Stock initial de démonstration',
                'reference_externe' => 'DEMO-INIT-HUILE',
            ]);

            // Client et Fournisseur fictifs
            $clientDemo = Client::withoutGlobalScope('entreprise')->create([
                'entreprise_id' => $entId,
                'code_client'   => 'CLT-DEMO-001',
                'nom'           => 'Kouadio',
                'prenom'        => 'Jean (Client Démo)',
                'telephone'     => '0701020304',
                'adresse'       => 'Cocody, Abidjan',
            ]);

            $fournisseurDemo = Fournisseur::withoutGlobalScope('entreprise')->create([
                'entreprise_id'    => $entId,
                'code_fournisseur' => 'FRN-DEMO-001',
                'nom'              => 'Grossiste Ivoire Distribution (Démo)',
                'contact'          => 'M. Koné',
                'telephone'        => '0501020304',
                'adresse'          => 'Zone Industrielle Yopougon',
            ]);

            // Vente fictive (partiellement payée)
            $venteDemo = Vente::withoutGlobalScope('entreprise')->create([
                'entreprise_id'   => $entId,
                'client_id'       => $clientDemo->id,
                'user_id'         => $demoUser->id,
                'code_recu'       => 'RECU_DEMO_0001',
                'montant_initial' => 36000,
                'montant_total'   => 36000,
                'remise'          => 0,
                'montant_paye'    => 20000,
                'reste_a_payer'   => 16000,
                'statut'          => 'valide',
                'statut_paiement' => 'partiel',
                'est_paye'        => false,
                'date_vente'      => now()->format('Y-m-d'),
                'date_echeance'   => now()->addDays(15)->format('Y-m-d'),
                'mode_paiement'   => 'espèces',
            ]);

            Detail_vente::create([
                'vente_id'            => $venteDemo->id,
                'produit_id'          => $prodRiz->id,
                'quantite'            => 2,
                'prix'                => 18000,
                'cout_achat_unitaire' => 14000,
                'total'               => 36000,
            ]);

            MouvementStock::withoutGlobalScope('entreprise')->create([
                'entreprise_id'  => $entId,
                'produit_id'     => $prodRiz->id,
                'user_id'        => $demoUser->id,
                'vente_id'       => $venteDemo->id,
                'quantite'       => 2,
                'type_mouvement' => 'sortie',
                'date_mouvement' => now()->format('Y-m-d'),
                'motif'          => 'Vente #RECU_DEMO_0001',
            ]);

            Paiement::withoutGlobalScope('entreprise')->create([
                'entreprise_id'  => $entId,
                'vente_id'       => $venteDemo->id,
                'user_id'        => $demoUser->id,
                'reference'      => 'PAY-DEMO-0001',
                'montant'        => 20000,
                'reste_a_payer'  => 16000,
                'mode_paiement'  => 'espèces',
                'type_operation' => 'encaissement',
                'date_paiement'  => now()->format('Y-m-d'),
            ]);

            // Dépense fictive
            Depense::withoutGlobalScope('entreprise')->create([
                'entreprise_id' => $entId,
                'titre'         => 'Facture Électricité (Démo)',
                'categorie'     => 'Électricité & Eau',
                'montant'       => 5000,
                'date_depense'  => now()->format('Y-m-d'),
                'mode_paiement' => 'Espèces',
                'statut'        => 'validee',
                'user_id'       => $demoUser->id,
            ]);

            return [
                'entreprise' => $demoEntreprise,
                'user'       => $demoUser,
            ];
        });
    }
}

