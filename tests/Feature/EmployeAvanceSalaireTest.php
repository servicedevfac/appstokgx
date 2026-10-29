<?php

namespace Tests\Feature;

use App\Models\AvanceSalaire;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\RemboursementAvance;
use App\Models\User;
use App\Services\EntrepriseContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeAvanceSalaireTest extends TestCase
{
    use RefreshDatabase;

    protected Entreprise $entreprise;
    protected User $admin;
    protected Employe $employe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $this->admin->assignRole('Administrateur');

        $this->entreprise = Entreprise::create([
            'nom' => 'Entreprise RH Test',
            'code' => 'ENT-RH',
            'plafond_avance_salaire' => 50000,
            'proprietaire_id' => $this->admin->id,
            'actif' => true,
        ]);

        $this->admin->update(['current_entreprise_id' => $this->entreprise->id]);
        EntrepriseContext::setCurrentId($this->entreprise->id);

        $this->employe = Employe::create([
            'entreprise_id' => $this->entreprise->id,
            'nom' => 'Bamba',
            'prenom' => 'Moussa',
            'poste' => 'Magasinier',
            'salaire_base' => 150000,
            'plafond_avance' => 60000,
            'actif' => true,
        ]);
    }

    public function test_octroi_avance_respecte_plafond_et_remboursement_separe_met_a_jour_solde(): void
    {
        // 1) Demande d'avance de 40 000 FCFA (<= plafond de 60 000 FCFA) -> acceptée
        $response = $this->actingAs($this->admin)->post(route('avances-salaires.store'), [
            'employe_id' => $this->employe->id,
            'montant' => 40000,
            'date_avance' => now()->toDateString(),
            'motif' => 'Avance scolaire',
        ]);
        $response->assertRedirect();

        $avance = AvanceSalaire::firstOrFail();
        $this->assertEquals(40000, (float) $avance->montant);
        $this->assertEquals(40000, (float) $avance->reste_a_rembourser);
        $this->assertEquals(40000, $this->employe->fresh()->solde_avance_restant);

        // 2) Deuxième demande de 25 000 FCFA -> Cumul (40 000 + 25 000 = 65 000 > plafond 60 000) -> Rejetée côté serveur (RM-057)
        $responsePlafond = $this->actingAs($this->admin)->post(route('avances-salaires.store'), [
            'employe_id' => $this->employe->id,
            'montant' => 25000,
            'date_avance' => now()->toDateString(),
            'motif' => 'Dépassement plafond',
        ]);
        $responsePlafond->assertSessionHasErrors('montant');
        $this->assertEquals(1, AvanceSalaire::count());

        // 3) Remboursement partiel séparé de 15 000 FCFA (RM-058, RM-059)
        $this->actingAs($this->admin)->post(route('avances-salaires.rembourser', $avance->id), [
            'montant' => 15000,
            'mode_remboursement' => 'retenue_salaire',
            'reference' => 'RMB-TEST-001',
        ]);

        $avance->refresh();
        $this->assertEquals(40000, (float) $avance->montant); // L'avance d'origine n'est pas écrasée
        $this->assertEquals(15000, (float) $avance->montant_rembourse);
        $this->assertEquals(25000, (float) $avance->reste_a_rembourser);
        $this->assertEquals(1, RemboursementAvance::count());

        // 4) Refus d'un remboursement en doublon (même référence)
        $dupResponse = $this->actingAs($this->admin)->post(route('avances-salaires.rembourser', $avance->id), [
            'montant' => 15000,
            'reference' => 'RMB-TEST-001',
        ]);
        $dupResponse->assertSessionHasErrors('reference');
        $this->assertEquals(25000, (float) $avance->fresh()->reste_a_rembourser);

        // 5) Refus d'un remboursement supérieur au reste à rembourser (30 000 > 25 000)
        $excessResponse = $this->actingAs($this->admin)->post(route('avances-salaires.rembourser', $avance->id), [
            'montant' => 30000,
        ]);
        $excessResponse->assertSessionHasErrors('montant');
        $this->assertEquals(25000, (float) $avance->fresh()->reste_a_rembourser);
    }

    public function test_confidentialite_des_donnees_salariales_pour_vendeur(): void
    {
        $vendeur = User::factory()->create(['actif' => true, 'current_entreprise_id' => $this->entreprise->id]);
        DB::table('entreprise_user')->insert([
            'entreprise_id' => $this->entreprise->id,
            'user_id' => $vendeur->id,
            'role' => 'Commerciale',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Un vendeur (Commerciale) n'a pas accès aux salaires ni aux avances (RM-060)
        $this->actingAs($vendeur)->get(route('employes.index'))->assertStatus(403);
        $this->actingAs($vendeur)->get(route('avances-salaires.index'))->assertStatus(403);
    }
}

