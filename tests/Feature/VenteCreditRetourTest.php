<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Entreprise;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\RetourClient;
use App\Models\User;
use App\Models\Vente;
use App\Services\EntrepriseContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenteCreditRetourTest extends TestCase
{
    use RefreshDatabase;

    protected Entreprise $entreprise;
    protected User $admin;
    protected Client $client;
    protected Produit $produit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $this->admin->assignRole('Administrateur');

        $this->entreprise = Entreprise::create([
            'nom' => 'Entreprise Ventes Test',
            'code' => 'ENT-VTEST',
            'proprietaire_id' => $this->admin->id,
            'actif' => true,
        ]);

        $this->admin->update(['current_entreprise_id' => $this->entreprise->id]);
        EntrepriseContext::setCurrentId($this->entreprise->id);

        $this->client = Client::create([
            'entreprise_id' => $this->entreprise->id,
            'code_client' => 'CLT-001',
            'nom' => 'Koné',
            'prenom' => 'Amadou',
            'telephone' => '0707070707',
        ]);

        $this->produit = Produit::create([
            'entreprise_id' => $this->entreprise->id,
            'nom' => 'Sac de Ciment 50kg',
            'reference' => 'CIM-50',
            'prix_achat' => 3800,
            'prix' => 4500,
            'seuil_alerte' => 5,
        ]);

        // Stock initial de 20 unités
        MouvementStock::create([
            'entreprise_id' => $this->entreprise->id,
            'produit_id' => $this->produit->id,
            'user_id' => $this->admin->id,
            'quantite' => 20,
            'type_mouvement' => 'entree',
            'date_mouvement' => now()->toDateString(),
            'motif' => 'Stock initial',
        ]);
    }

    public function test_vente_au_comptant_recalcule_prix_serveur_diminue_stock_et_cree_mouvement(): void
    {
        $response = $this->actingAs($this->admin)->post(route('ventes.store'), [
            'client_id' => $this->client->id,
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'espèces',
            'remise' => 1000,
            // Même si le navigateur tente d'envoyer un faux prix ou total, le serveur recalcule (4 * 4500 - 1000 = 17000)
            'prix_falsifie' => 100,
            'produits' => [
                ['produit_id' => $this->produit->id, 'quantite' => 4],
            ],
        ]);

        $response->assertRedirect();

        $vente = Vente::first();
        $this->assertNotNull($vente);
        $this->assertEquals(17000, (float) $vente->montant_total);
        $this->assertEquals(17000, (float) $vente->montant_paye);
        $this->assertEquals(0, (float) $vente->reste_a_payer);
        $this->assertEquals('paye', $vente->statut_paiement);

        // Stock passe de 20 à 16
        $this->assertEquals(16, $this->produit->fresh()->stock_actuel);

        // Le changement ultérieur du prix catalogue ne modifie pas la vente passée (RM-038)
        $this->produit->update(['prix' => 6000]);
        $this->assertEquals(4500, (float) $vente->details()->first()->prix);
        $this->assertEquals(17000, (float) $vente->fresh()->montant_total);
    }

    public function test_refus_vente_si_stock_insuffisant(): void
    {
        $response = $this->actingAs($this->admin)->post(route('ventes.store'), [
            'client_id' => $this->client->id,
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'espèces',
            'produits' => [
                ['produit_id' => $this->produit->id, 'quantite' => 25], // > 20 disponibles
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, Vente::count());
        $this->assertEquals(20, $this->produit->fresh()->stock_actuel);
    }

    public function test_vente_a_credit_paiements_partiels_et_protection_anti_doublon(): void
    {
        // Vente de 10 sacs = 45 000 FCFA, acompte initial de 15 000 FCFA
        $this->actingAs($this->admin)->post(route('ventes.store'), [
            'client_id' => $this->client->id,
            'date_vente' => now()->toDateString(),
            'date_echeance' => now()->addDays(15)->toDateString(),
            'mode_paiement' => 'crédit',
            'montant_paye' => 15000,
            'produits' => [
                ['produit_id' => $this->produit->id, 'quantite' => 10],
            ],
        ]);

        $vente = Vente::firstOrFail();
        $this->assertEquals(45000, (float) $vente->montant_initial);
        $this->assertEquals(45000, (float) $vente->montant_total);
        $this->assertEquals(15000, (float) $vente->montant_paye);
        $this->assertEquals(30000, (float) $vente->reste_a_payer);
        $this->assertEquals('partiel', $vente->statut_paiement);

        // 2ème paiement partiel de 10 000 FCFA avec référence unique
        $this->actingAs($this->admin)->post(route('ventes.payer', $vente->id), [
            'montant' => 10000,
            'mode_paiement' => 'mobile_money',
            'reference' => 'PAY-UNIQUE-001',
        ]);

        $vente->refresh();
        $this->assertEquals(45000, (float) $vente->montant_initial); // RM-041 : montant initial conservé
        $this->assertEquals(25000, (float) $vente->montant_paye);
        $this->assertEquals(20000, (float) $vente->reste_a_payer);
        $this->assertEquals(2, Paiement::where('vente_id', $vente->id)->count());

        // Tentative de double soumission du même paiement (même référence) -> rejeté
        $this->actingAs($this->admin)->post(route('ventes.payer', $vente->id), [
            'montant' => 10000,
            'mode_paiement' => 'mobile_money',
            'reference' => 'PAY-UNIQUE-001',
        ]);
        $this->assertEquals(25000, (float) $vente->fresh()->montant_paye);

        // Tentative de paiement supérieur au reste à payer (25 000 > 20 000) -> rejeté
        $this->actingAs($this->admin)->post(route('ventes.payer', $vente->id), [
            'montant' => 25000,
            'mode_paiement' => 'espèces',
        ]);
        $this->assertEquals(20000, (float) $vente->fresh()->reste_a_payer);

        // Solde final de 20 000 FCFA
        $this->actingAs($this->admin)->post(route('ventes.payer', $vente->id), [
            'montant' => 20000,
            'mode_paiement' => 'espèces',
        ]);
        $vente->refresh();
        $this->assertEquals(45000, (float) $vente->montant_paye);
        $this->assertEquals(0, (float) $vente->reste_a_payer);
        $this->assertEquals('paye', $vente->statut_paiement);
        $this->assertTrue((bool) $vente->est_paye);
    }

    public function test_retour_client_avec_et_sans_reintegration_stock_et_interdiction_suppression_physique(): void
    {
        $this->actingAs($this->admin)->post(route('ventes.store'), [
            'client_id' => $this->client->id,
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'espèces',
            'montant_paye' => 22500,
            'produits' => [
                ['produit_id' => $this->produit->id, 'quantite' => 5], // 5 * 4500 = 22500
            ],
        ]);

        $vente = Vente::firstOrFail();
        $this->assertEquals(15, $this->produit->fresh()->stock_actuel);

        // 1) Retour de 1 sac en bon état avec réintégration en stock (RM-043)
        $this->actingAs($this->admin)->post(route('ventes.retour', $vente->id), [
            'produit_id' => $this->produit->id,
            'quantite' => 1,
            'etat_marchandise' => 'bon_etat',
            'reintegrer_stock' => true,
            'montant_rembourse' => 4500,
            'motif' => 'Erreur de commande client',
        ]);

        $this->assertEquals(16, $this->produit->fresh()->stock_actuel);
        $this->assertEquals(1, RetourClient::count());

        // 2) Retour de 1 sac endommagé -> ne doit PAS réintégrer le stock disponible (RM-043)
        $this->actingAs($this->admin)->post(route('ventes.retour', $vente->id), [
            'produit_id' => $this->produit->id,
            'quantite' => 1,
            'etat_marchandise' => 'endommage',
            'reintegrer_stock' => true,
            'montant_rembourse' => 0,
            'motif' => 'Sac déchiré non réintégrable',
        ]);

        $this->assertEquals(16, $this->produit->fresh()->stock_actuel); // Inchangé !

        // 3) Suppression physique interdite sur une vente validée
        $this->actingAs($this->admin)->delete(route('ventes.destroy', $vente->id));
        $this->assertDatabaseHas('ventes', ['id' => $vente->id]);

        // 4) Annulation traçable avec motif et auteur
        $this->actingAs($this->admin)->post(route('ventes.annuler', $vente->id), [
            'motif' => 'Annulation complète après accord',
            'marchandise_recuperee' => false,
        ]);

        $vente->refresh();
        $this->assertEquals('annulee', $vente->statut);
        $this->assertEquals('Annulation complète après accord', $vente->motif_annulation);
        $this->assertEquals($this->admin->id, $vente->annule_par);
    }
}

