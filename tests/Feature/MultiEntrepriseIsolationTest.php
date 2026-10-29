<?php

namespace Tests\Feature;

use App\Models\Achat;
use App\Models\AvanceSalaire;
use App\Models\Categorie;
use App\Models\Client;
use App\Models\Depense;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use App\Services\EntrepriseContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiEntrepriseIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_superadmin_peut_creer_proprietaire_et_entreprise(): void
    {
        $superAdmin = User::factory()->create(['actif' => true]);
        $superAdmin->assignRole('super admin');

        $response = $this->actingAs($superAdmin)->post(route('superadmin.proprietaires.store'), [
            'nom' => 'Konan',
            'prenom' => 'Marc',
            'email' => 'marc.konan@stokgx.test',
            'telephone' => '0708091011',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'entreprise_nom' => 'Konan Distribution SARL',
            'entreprise_adresse' => 'Abidjan Plateau',
        ]);

        $response->assertRedirect();

        $proprietaire = User::where('email', 'marc.konan@stokgx.test')->first();
        $this->assertNotNull($proprietaire);
        $this->assertTrue($proprietaire->is_proprietaire);

        $entreprise = Entreprise::where('nom', 'Konan Distribution SARL')->first();
        $this->assertNotNull($entreprise);
        $this->assertEquals($proprietaire->id, $entreprise->proprietaire_id);
        $this->assertTrue($proprietaire->canAccessEntreprise($entreprise->id));
    }

    public function test_proprietaire_peut_gerer_plusieurs_entreprises_et_changer_entreprise_active(): void
    {
        $proprietaire = User::factory()->create([
            'actif' => true,
            'is_proprietaire' => true,
        ]);
        $proprietaire->assignRole('Propriétaire');

        $entA = Entreprise::create([
            'nom' => 'Entreprise Alpha',
            'code' => 'ENT-ALPHA',
            'proprietaire_id' => $proprietaire->id,
            'actif' => true,
        ]);

        $entB = Entreprise::create([
            'nom' => 'Entreprise Beta',
            'code' => 'ENT-BETA',
            'proprietaire_id' => $proprietaire->id,
            'actif' => true,
        ]);

        $proprietaire->update(['current_entreprise_id' => $entA->id]);

        // Bascule vers Entreprise B
        $response = $this->actingAs($proprietaire)->post(route('entreprises.switch', $entB->id));
        $response->assertRedirect();
        $this->assertEquals($entB->id, session('active_entreprise_id'));
        $this->assertEquals($entB->id, $proprietaire->fresh()->current_entreprise_id);
    }

    public function test_interdiction_acceder_ou_basculer_vers_entreprise_non_autorisee(): void
    {
        $propA = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $propB = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);

        $entA = Entreprise::create(['nom' => 'Soc A', 'code' => 'ENT-A', 'proprietaire_id' => $propA->id, 'actif' => true]);
        $entB = Entreprise::create(['nom' => 'Soc B', 'code' => 'ENT-B', 'proprietaire_id' => $propB->id, 'actif' => true]);

        $propA->update(['current_entreprise_id' => $entA->id]);

        $response = $this->actingAs($propA)->post(route('entreprises.switch', $entB->id));
        $response->assertStatus(403);
    }

    public function test_isolation_complete_des_donnees_entre_entreprise_a_et_entreprise_b(): void
    {
        $userA = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $userB = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);

        $entA = Entreprise::create(['nom' => 'Boutique A', 'code' => 'ENT-00A', 'proprietaire_id' => $userA->id, 'actif' => true]);
        $entB = Entreprise::create(['nom' => 'Boutique B', 'code' => 'ENT-00B', 'proprietaire_id' => $userB->id, 'actif' => true]);

        $userA->update(['current_entreprise_id' => $entA->id]);
        $userB->update(['current_entreprise_id' => $entB->id]);

        // Données de l'entreprise A
        EntrepriseContext::setCurrentId($entA->id);
        $catA = Categorie::create(['entreprise_id' => $entA->id, 'nom' => 'Cat A']);
        $prodA = Produit::create(['entreprise_id' => $entA->id, 'categorie_id' => $catA->id, 'nom' => 'Produit Secret A', 'prix' => 5000, 'seuil_alerte' => 2]);
        $clientA = Client::create(['entreprise_id' => $entA->id, 'code_client' => 'CA-01', 'nom' => 'Client A']);
        $fournA = Fournisseur::create(['entreprise_id' => $entA->id, 'nom' => 'Fournisseur A']);
        $depA = Depense::create(['entreprise_id' => $entA->id, 'titre' => 'Loyer A', 'categorie' => 'Loyer', 'montant' => 50000, 'date_depense' => now()->toDateString(), 'user_id' => $userA->id]);
        $empA = Employe::create(['entreprise_id' => $entA->id, 'nom' => 'Employe A', 'poste' => 'Vendeur', 'salaire_base' => 100000]);
        $venteA = Vente::create([
            'entreprise_id' => $entA->id,
            'client_id' => $clientA->id,
            'user_id' => $userA->id,
            'code_recu' => 'RECU-A-001',
            'montant_total' => 5000,
            'montant_paye' => 5000,
            'reste_a_payer' => 0,
            'statut' => 'valide',
            'date_vente' => now()->toDateString(),
            'mode_paiement' => 'espèces',
        ]);

        // Données de l'entreprise B
        EntrepriseContext::setCurrentId($entB->id);
        $prodB = Produit::create(['entreprise_id' => $entB->id, 'nom' => 'Produit B', 'prix' => 3000, 'seuil_alerte' => 2]);
        $clientB = Client::create(['entreprise_id' => $entB->id, 'code_client' => 'CB-01', 'nom' => 'Client B']);

        // Vérification côté Entreprise B : aucune donnée de A n'est visible
        $this->actingAs($userB);
        session(['active_entreprise_id' => $entB->id]);
        EntrepriseContext::setCurrentId($entB->id);

        $this->assertCount(1, Produit::all());
        $this->assertEquals('Produit B', Produit::first()->nom);
        $this->assertCount(0, Categorie::all());
        $this->assertCount(1, Client::all());
        $this->assertCount(0, Fournisseur::all());
        $this->assertCount(0, Depense::all());
        $this->assertCount(0, Employe::all());
        $this->assertCount(0, Vente::all());

        // Tentative d'accès direct par URL à la vente et au ticket de l'entreprise A par l'utilisateur B (Protection IDOR)
        $this->get(route('ventes.show', $venteA->id))->assertStatus(404);
        $this->get(route('ventes.ticket', $venteA->id))->assertStatus(403);

        // Tentative de soumission d'une vente dans l'entreprise B avec le client ou produit de l'entreprise A
        $responseSpoof = $this->post(route('ventes.store'), [
            'client_id' => $clientA->id,
            'mode_paiement' => 'espèces',
            'montant_paye' => 5000,
            'produits' => [
                ['produit_id' => $prodA->id, 'quantite' => 1],
            ],
        ]);
        $this->assertTrue(in_array($responseSpoof->status(), [302, 404, 422]));
        $this->assertEquals(0, Vente::where('entreprise_id', $entB->id)->count());
    }

    public function test_utilisateur_avec_roles_differents_selon_entreprise(): void
    {
        $owner = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $entA = Entreprise::create(['nom' => 'Ent A', 'code' => 'ENT-RA', 'proprietaire_id' => $owner->id, 'actif' => true]);
        $entB = Entreprise::create(['nom' => 'Ent B', 'code' => 'ENT-RB', 'proprietaire_id' => $owner->id, 'actif' => true]);

        $employeUser = User::factory()->create(['actif' => true, 'current_entreprise_id' => $entA->id]);

        // Gestionnaire dans Ent A, Commerciale (Vendeur) dans Ent B
        DB::table('entreprise_user')->insert([
            [
                'entreprise_id' => $entA->id,
                'user_id' => $employeUser->id,
                'role' => 'Gestionnaire',
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'entreprise_id' => $entB->id,
                'user_id' => $employeUser->id,
                'role' => 'Commerciale',
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Dans Ent A : Gestionnaire de stock -> peut créer un produit et réceptionner un achat, mais ne peut pas gérer les salaires
        EntrepriseContext::setCurrentId($entA->id);
        $this->assertEquals('Gestionnaire', $employeUser->roleInEntreprise($entA->id));
        $this->assertTrue($employeUser->hasPermissionInEntreprise('create produit', $entA->id));
        $this->assertTrue($employeUser->hasPermissionInEntreprise('receptionner achat', $entA->id));
        $this->assertFalse($employeUser->hasPermissionInEntreprise('create avance_salaire', $entA->id));

        // Dans Ent B : Vendeur / Caissier -> peut créer une vente, mais pas créer un produit ni réceptionner un achat
        EntrepriseContext::setCurrentId($entB->id);
        $this->assertEquals('Commerciale', $employeUser->roleInEntreprise($entB->id));
        $this->assertTrue($employeUser->hasPermissionInEntreprise('create vente', $entB->id));
        $this->assertFalse($employeUser->hasPermissionInEntreprise('create produit', $entB->id));
        $this->assertFalse($employeUser->hasPermissionInEntreprise('receptionner achat', $entB->id));
    }

    public function test_has_role_accepte_collection_spatie_et_vues_dashboards_s_affichent_sans_erreur(): void
    {
        $owner = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $owner->assignRole('Propriétaire');
        $ent = Entreprise::create(['nom' => 'Ent Test UI', 'code' => 'ENT-UI', 'proprietaire_id' => $owner->id, 'actif' => true]);
        $owner->update(['current_entreprise_id' => $ent->id]);

        DB::table('entreprise_user')->insert([
            'entreprise_id' => $ent->id,
            'user_id' => $owner->id,
            'role' => 'Propriétaire',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vérifie que hasRole et hasAnyRole acceptent une Collection de modèles Role (cas de Spatie hasPermissionViaRole)
        $rolesCollection = \Spatie\Permission\Models\Role::whereIn('name', ['Propriétaire', 'Administrateur'])->get();
        $this->assertTrue($owner->hasRole($rolesCollection));
        $this->assertTrue($owner->hasAnyRole($rolesCollection));
        $this->assertTrue($owner->can('create vente'));
        $this->assertTrue($owner->can('afficher fournisseurs'));

        // Vérifie le rendu HTML complet de /dashboard et /dashboard-consolide (qui évaluent @can dans layouts.sidebar)
        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertStatus(200);

        $this->actingAs($owner)
            ->get(route('dashboard.consolide'))
            ->assertStatus(200)
            ->assertSee('Tableau de Bord Consolidé Multi-Entreprises')
            ->assertSee('Ent Test UI');
    }
}
