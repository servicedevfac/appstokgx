<?php

namespace Tests\Feature;

use App\Models\Achat;
use App\Models\Client;
use App\Models\Depense;
use App\Models\Detail_vente;
use App\Models\Entreprise;
use App\Models\Fournisseur;
use App\Models\Paiement;
use App\Models\PaiementFournisseur;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use App\Services\EntrepriseContext;
use App\Services\FinanceService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTresorerieTest extends TestCase
{
    use RefreshDatabase;

    protected User $proprietaire;
    protected Entreprise $ent1;
    protected Entreprise $ent2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->proprietaire = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $this->proprietaire->assignRole('Propriétaire');

        $this->ent1 = Entreprise::create([
            'nom' => 'Entreprise Finance 1',
            'code' => 'ENT-FIN1',
            'proprietaire_id' => $this->proprietaire->id,
            'actif' => true,
        ]);

        $this->ent2 = Entreprise::create([
            'nom' => 'Entreprise Finance 2',
            'code' => 'ENT-FIN2',
            'proprietaire_id' => $this->proprietaire->id,
            'actif' => true,
        ]);

        $this->proprietaire->update(['current_entreprise_id' => $this->ent1->id]);
        EntrepriseContext::setCurrentId($this->ent1->id);
    }

    public function test_separation_stricte_ca_encaissements_achats_paiements_depenses_et_exclusion_ventes_annulees(): void
    {
        $client = Client::create(['entreprise_id' => $this->ent1->id, 'code_client' => 'CF-01', 'nom' => 'Client Fin']);
        $fourn = Fournisseur::create(['entreprise_id' => $this->ent1->id, 'nom' => 'Fourn Fin']);
        $prod = Produit::create(['entreprise_id' => $this->ent1->id, 'nom' => 'Prod Fin', 'prix_achat' => 6000, 'prix' => 10000, 'seuil_alerte' => 2]);

        // 1) Vente valide à crédit : Total = 100 000, Payé = 40 000, Reste (Créance) = 60 000, COGS = 60 000
        $venteValide = Vente::create([
            'entreprise_id' => $this->ent1->id,
            'client_id' => $client->id,
            'user_id' => $this->proprietaire->id,
            'code_recu' => 'RECU-FIN-01',
            'montant_initial' => 100000,
            'montant_total' => 100000,
            'montant_paye' => 40000,
            'reste_a_payer' => 60000,
            'statut' => 'valide',
            'statut_paiement' => 'partiel',
            'date_vente' => '2026-10-15',
            'mode_paiement' => 'crédit',
        ]);

        Detail_vente::create([
            'vente_id' => $venteValide->id,
            'produit_id' => $prod->id,
            'quantite' => 10,
            'prix' => 10000,
            'cout_achat_unitaire' => 6000,
            'total' => 100000,
        ]);

        Paiement::create([
            'entreprise_id' => $this->ent1->id,
            'vente_id' => $venteValide->id,
            'user_id' => $this->proprietaire->id,
            'reference' => 'PAY-FIN-01',
            'montant' => 40000,
            'reste_a_payer' => 60000,
            'mode_paiement' => 'espèces',
            'type_operation' => 'encaissement',
            'date_paiement' => '2026-10-15',
        ]);

        // 2) Vente annulée : Total = 50 000 -> Ne doit JAMAIS être comptée dans le CA ni les créances (RM-047)
        Vente::create([
            'entreprise_id' => $this->ent1->id,
            'client_id' => $client->id,
            'user_id' => $this->proprietaire->id,
            'code_recu' => 'RECU-FIN-ANN',
            'montant_initial' => 50000,
            'montant_total' => 50000,
            'montant_paye' => 0,
            'reste_a_payer' => 0,
            'statut' => 'annulee',
            'statut_paiement' => 'annulee',
            'date_vente' => '2026-10-15',
            'mode_paiement' => 'espèces',
        ]);

        // 3) Achat fournisseur : Total = 80 000, Payé = 30 000, Dette fournisseur = 50 000
        $achat = Achat::create([
            'entreprise_id' => $this->ent1->id,
            'fournisseur_id' => $fourn->id,
            'user_id' => $this->proprietaire->id,
            'numero_achat' => 'ACH-FIN-01',
            'date_achat' => '2026-10-15',
            'montant_total' => 80000,
            'montant_paye' => 30000,
            'reste_a_payer' => 50000,
            'statut_reception' => 'recu',
            'statut_paiement' => 'partiel',
        ]);

        PaiementFournisseur::create([
            'entreprise_id' => $this->ent1->id,
            'achat_id' => $achat->id,
            'fournisseur_id' => $fourn->id,
            'user_id' => $this->proprietaire->id,
            'reference' => 'PF-FIN-01',
            'montant' => 30000,
            'reste_a_payer' => 50000,
            'mode_paiement' => 'virement',
            'date_paiement' => '2026-10-15',
            'type_operation' => 'paiement',
        ]);

        // 4) Dépenses : 1 dépense validée (5 000) et 1 dépense annulée/en_attente (12 000) -> seule la validée compte !
        Depense::create([
            'entreprise_id' => $this->ent1->id,
            'titre' => 'Transport validé',
            'categorie' => 'Transport',
            'montant' => 5000,
            'date_depense' => '2026-10-15',
            'statut' => 'validee',
            'user_id' => $this->proprietaire->id,
        ]);

        Depense::create([
            'entreprise_id' => $this->ent1->id,
            'titre' => 'Dépense non validée',
            'categorie' => 'Autre',
            'montant' => 12000,
            'date_depense' => '2026-10-15',
            'statut' => 'en_attente',
            'user_id' => $this->proprietaire->id,
        ]);

        $ind = FinanceService::calculateIndicators([$this->ent1->id], '2026-10-01', '2026-10-31');

        // RM-046 à RM-053 : Vérifications strictes
        $this->assertEquals(100000.0, $ind['chiffre_affaires']); // Vente valide uniquement (pas l'annulée, et pas confondu avec l'encaissement !)
        $this->assertEquals(40000.0, $ind['encaissements']);     // Encaissement réel reçu
        $this->assertEquals(60000.0, $ind['creances_clients']);  // Reste à encaisser
        $this->assertEquals(80000.0, $ind['achats']);            // Total achats
        $this->assertEquals(30000.0, $ind['paiements_fournisseurs']); // Décaissement fournisseur réel
        $this->assertEquals(50000.0, $ind['dettes_fournisseurs']);    // Dette fournisseur restante
        $this->assertEquals(5000.0, $ind['depenses']);           // Uniquement la dépense validée
        $this->assertEquals(60000.0, $ind['cout_marchandises_vendues']); // 10 * 6000
        $this->assertEquals(40000.0, $ind['marge_brute_estimee']);       // 100 000 - 60 000
        $this->assertEquals(35000.0, $ind['resultat_estimatif']);        // 40 000 - 5 000
        $this->assertEquals(5000.0, $ind['flux_tresorerie_net']);        // 40 000 (entrée) - (30 000 + 5 000) (sorties)
    }

    public function test_consolidation_multi_entreprises_pour_le_proprietaire(): void
    {
        $c1 = Client::create(['entreprise_id' => $this->ent1->id, 'code_client' => 'C1', 'nom' => 'Client 1']);
        $c2 = Client::create(['entreprise_id' => $this->ent2->id, 'code_client' => 'C2', 'nom' => 'Client 2']);

        Vente::create([
            'entreprise_id' => $this->ent1->id,
            'client_id' => $c1->id,
            'user_id' => $this->proprietaire->id,
            'code_recu' => 'R1',
            'montant_total' => 70000,
            'montant_paye' => 70000,
            'reste_a_payer' => 0,
            'statut' => 'valide',
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'espèces',
        ]);

        Vente::create([
            'entreprise_id' => $this->ent2->id,
            'client_id' => $c2->id,
            'user_id' => $this->proprietaire->id,
            'code_recu' => 'R2',
            'montant_total' => 130000,
            'montant_paye' => 100000,
            'reste_a_payer' => 30000,
            'statut' => 'valide',
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'crédit',
        ]);

        // Indicateurs consolidés sur [ent1, ent2]
        $consolide = FinanceService::calculateIndicators([$this->ent1->id, $this->ent2->id]);
        $this->assertEquals(200000.0, $consolide['chiffre_affaires']);
        $this->assertEquals(30000.0, $consolide['creances_clients']);
    }
}

