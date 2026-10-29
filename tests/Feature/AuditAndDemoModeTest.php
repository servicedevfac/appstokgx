<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Entreprise;
use App\Models\Produit;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DemoService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditAndDemoModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_journal_audit_masque_secrets_et_est_immuable(): void
    {
        $user = User::factory()->create(['actif' => true]);

        $log = AuditService::log(
            'user.test_action',
            $user,
            $user->id,
            [
                'nom' => 'Test User',
                'password' => 'SecretPassword123!',
                'remember_token' => 'secret_token_xyz',
            ],
            'Test de journalisation sécurisée'
        );

        // RM-072 : Aucun secret en clair dans le contexte du journal d'audit
        $this->assertArrayNotHasKey('password', $log->contexte);
        $this->assertArrayNotHasKey('remember_token', $log->contexte);
        $this->assertEquals('Test User', $log->contexte['nom']);

        // RM-071 : Inaltérabilité du journal d'audit (modification et suppression interdites)
        $this->expectException(LogicException::class);
        $log->update(['action' => 'tampered']);
    }

    public function test_suppression_entree_journal_audit_interdite(): void
    {
        $log = AuditService::log('system.check', 'System', 1, ['ok' => true], 'Check');

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_mode_demonstration_isole_et_reinitialisation_sans_impact_sur_entreprises_reelles(): void
    {
        // Création d'une entreprise réelle avec un produit réel
        $realOwner = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $realEnt = Entreprise::create([
            'nom' => 'Entreprise Réelle Production',
            'code' => 'ENT-REAL',
            'proprietaire_id' => $realOwner->id,
            'actif' => true,
            'is_demo' => false,
        ]);

        $realProduct = Produit::withoutGlobalScopes()->create([
            'entreprise_id' => $realEnt->id,
            'nom' => 'Produit Réel Précieux',
            'prix' => 50000,
            'seuil_alerte' => 5,
        ]);

        // Initialisation / Réinitialisation de l'environnement de démonstration (RM-075 à RM-077)
        $demoData = DemoService::resetDemoEnvironment();
        $demoEnt = $demoData['entreprise'];
        $demoUser = $demoData['user'];

        $this->assertTrue($demoEnt->is_demo);
        $this->assertTrue($demoUser->is_demo);

        // Le compte démo ne peut PAS accéder à l'entreprise réelle
        $this->assertFalse($demoUser->canAccessEntreprise($realEnt->id));
        $this->assertTrue($demoUser->canAccessEntreprise($demoEnt->id));

        // La réinitialisation du mode démo ne touche JAMAIS aux données de l'entreprise réelle
        DemoService::resetDemoEnvironment();
        $this->assertDatabaseHas('produits', [
            'id' => $realProduct->id,
            'entreprise_id' => $realEnt->id,
            'nom' => 'Produit Réel Précieux',
        ]);
    }
}

