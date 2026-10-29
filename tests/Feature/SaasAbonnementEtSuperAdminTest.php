<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaasAbonnementEtSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_landing_page_saas_s_affiche_correctement(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('GESTOCK');
        $response->assertSee('Pack Essentiel');
        $response->assertSee('Pack Business Pro');
        $response->assertSee('Pack Enterprise');
    }

    public function test_superadmin_gere_uniquement_le_saas_global_et_pas_les_operations_entreprise(): void
    {
        $superAdmin = User::factory()->create([
            'actif'                 => true,
            'is_proprietaire'       => false,
            'current_entreprise_id' => null,
        ]);
        $superAdmin->assignRole('super admin');

        // /dashboard redirige le super admin vers /superadmin/dashboard
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertRedirect(route('superadmin.dashboard'));

        // Console SaaS accessible
        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Console de Pilotage SaaS Global');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.abonnements.index'))
            ->assertStatus(200)
            ->assertSee('Gestion des Abonnements SaaS');

        // Les routes opérationnelles d'entreprise sont interdites au super admin
        $this->actingAs($superAdmin)
            ->get(route('ventes.index'))
            ->assertRedirect(route('superadmin.dashboard'));

        $this->actingAs($superAdmin)
            ->getJson(route('produits.index'))
            ->assertStatus(403);
    }

    public function test_chaque_entreprise_voit_son_abonnement_et_blocage_si_expire_ou_suspendu(): void
    {
        $owner = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $owner->assignRole('Propriétaire');

        $entreprise = Entreprise::create([
            'nom'             => 'Boutique Alpha',
            'code'            => 'ENT-ALP01',
            'proprietaire_id' => $owner->id,
            'actif'           => true,
        ]);
        $owner->update(['current_entreprise_id' => $entreprise->id]);

        DB::table('entreprise_user')->insert([
            'entreprise_id' => $entreprise->id,
            'user_id'       => $owner->id,
            'role'          => 'Propriétaire',
            'actif'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $abonnement = $entreprise->ensureDefaultSubscription('pro');
        $this->assertNotNull($abonnement);
        $this->assertTrue($entreprise->hasActiveSubscription());

        // Consultation de la page Mon Abonnement par l'entreprise
        $this->actingAs($owner)
            ->get(route('abonnement.index'))
            ->assertStatus(200)
            ->assertSee('Pack Business Pro')
            ->assertSee('Boutique Alpha');

        // Suspension de l'abonnement par le Super Admin
        $superAdmin = User::factory()->create(['actif' => true, 'is_proprietaire' => false]);
        $superAdmin->assignRole('super admin');

        $this->actingAs($superAdmin)
            ->post(route('superadmin.abonnements.toggle', $abonnement->id))
            ->assertRedirect();

        $this->assertEquals('suspendu', $abonnement->fresh()->statut);
        $this->assertFalse($entreprise->fresh()->hasActiveSubscription());

        // L'entreprise dont l'abonnement est suspendu est redirigée vers /mon-abonnement
        $this->actingAs($owner)
            ->get(route('ventes.index'))
            ->assertRedirect(route('abonnement.index'));
    }

    public function test_proprietaire_ne_peut_pas_creer_une_entreprise_seul_superadmin_peut(): void
    {
        $owner = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $owner->assignRole('Propriétaire');

        $ent = Entreprise::create([
            'nom'             => 'Entreprise Initiale',
            'code'            => 'ENT-INI01',
            'proprietaire_id' => $owner->id,
            'actif'           => true,
        ]);
        $ent->ensureDefaultSubscription('pro');
        $owner->update(['current_entreprise_id' => $ent->id]);
        DB::table('entreprise_user')->insert([
            'entreprise_id' => $ent->id,
            'user_id'       => $owner->id,
            'role'          => 'Propriétaire',
            'actif'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Le propriétaire ne possède pas la permission de créer une entreprise
        $this->assertFalse($owner->can('create entreprise'));

        // Tentative de création d'entreprise par le propriétaire -> 403
        $this->actingAs($owner)
            ->post(route('entreprises.store'), [
                'nom'             => 'Entreprise Non Autorisée',
                'proprietaire_id' => $owner->id,
            ])
            ->assertStatus(403);

        // Un utilisateur étant déjà lié à une entreprise, même le Super Admin ne peut pas lui rattacher une 2ème entreprise
        $superAdmin = User::factory()->create(['actif' => true, 'is_proprietaire' => false]);
        $superAdmin->assignRole('super admin');

        $this->actingAs($superAdmin)
            ->post(route('superadmin.proprietaires.store-entreprise', $owner->id), [
                'nom'         => 'Deuxième Entreprise Refusée',
                'plan'        => 'pro',
                'periodicite' => 'annuel',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('entreprises', [
            'nom' => 'Deuxième Entreprise Refusée',
        ]);
        $this->assertCount(1, $owner->fresh()->accessibleEntrepriseIds());
    }
}

