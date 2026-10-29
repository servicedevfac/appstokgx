<?php

namespace Tests\Feature;

use App\Models\Achat;
use App\Models\Entreprise;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\User;
use App\Services\EntrepriseContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FournisseurAchatStockTest extends TestCase
{
    use RefreshDatabase;

    protected Entreprise $entreprise;
    protected User $admin;
    protected Fournisseur $fournisseur;
    protected Produit $produit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $this->admin->assignRole('Administrateur');

        $this->entreprise = Entreprise::create([
            'nom' => 'Entreprise Achats Test',
            'code' => 'ENT-ATEST',
            'proprietaire_id' => $this->admin->id,
            'actif' => true,
        ]);

        $this->admin->update(['current_entreprise_id' => $this->entreprise->id]);
        EntrepriseContext::setCurrentId($this->entreprise->id);

        $this->fournisseur = Fournisseur::create([
            'entreprise_id' => $this->entreprise->id,
            'nom' => 'Cimenterie Nationale',
            'telephone' => '0102030405',
        ]);

        $this->produit = Produit::create([
            'entreprise_id' => $this->entreprise->id,
            'nom' => 'Fer à béton HA10',
            'prix_achat' => 2500,
            'prix' => 3500,
            'seuil_alerte' => 10,
        ]);
    }

    public function test_achat_en_attente_n_augmente_pas_le_stock_avant_reception_puis_reception_met_a_jour_stock(): void
    {
        // RM-044 : Création d'un achat non réceptionné (statut_reception = 'en_attente')
        $response = $this->actingAs($this->admin)->post(route('achats.store'), [
            'fournisseur_id' => $this->fournisseur->id,
            'date_achat' => now()->toDateString(),
            'montant_paye_initial' => 50000,
            'mode_paiement' => 'virement',
            'reception_immediate' => false,
            'produits' => [
                [
                    'produit_id' => $this->produit->id,
                    'quantite' => 100,
                    'prix_achat_unitaire' => 2600,
                ],
            ],
        ]);

        $achat = Achat::firstOrFail();
        $response->assertRedirect(route('achats.show', $achat->id));

        $this->assertEquals('en_attente', $achat->statut_reception);
        $this->assertEquals(260000, (float) $achat->montant_total);
        $this->assertEquals(50000, (float) $achat->montant_paye);
        $this->assertEquals(210000, (float) $achat->reste_a_payer);

        // RM-044 : Le stock disponible est toujours à 0 tant que la réception n'est pas validée !
        $this->assertEquals(0, $this->produit->fresh()->stock_actuel);

        // Validation de la réception -> augmente le stock de 100 unités et met à jour le coût d'achat
        $this->actingAs($this->admin)->post(route('achats.receptionner', $achat->id));

        $this->assertEquals('recu', $achat->fresh()->statut_reception);
        $this->assertEquals(100, $this->produit->fresh()->stock_actuel);
        $this->assertEquals(2600, (float) $this->produit->fresh()->prix_achat);

        // Paiement fournisseur partiel de 110 000 FCFA
        $this->actingAs($this->admin)->post(route('achats.payer', $achat->id), [
            'montant' => 110000,
            'mode_paiement' => 'virement',
            'reference' => 'PF-TEST-001',
        ]);

        $achat->refresh();
        $this->assertEquals(160000, (float) $achat->montant_paye);
        $this->assertEquals(100000, (float) $achat->reste_a_payer);
        $this->assertEquals(100000, $this->fournisseur->fresh()->solde_dette);

        // Retour fournisseur de 10 barres défectueuses (10 * 2600 = 26 000 FCFA)
        $this->actingAs($this->admin)->post(route('achats.retour', $achat->id), [
            'produit_id' => $this->produit->id,
            'quantite' => 10,
            'motif' => 'Barres tordues à la livraison',
        ]);

        $this->assertEquals(90, $this->produit->fresh()->stock_actuel);
        $this->assertEquals(74000, (float) $achat->fresh()->reste_a_payer);
    }
}

