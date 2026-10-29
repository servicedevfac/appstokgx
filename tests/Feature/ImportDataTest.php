<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use App\Services\EntrepriseContext;
use App\Services\ImportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Tests\TestCase;

class ImportDataTest extends TestCase
{
    use RefreshDatabase;

    protected Entreprise $entreprise;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['actif' => true, 'is_proprietaire' => true]);
        $this->admin->assignRole('Administrateur');

        $this->entreprise = Entreprise::create([
            'nom' => 'Entreprise Import Test',
            'code' => 'ENT-IMP',
            'proprietaire_id' => $this->admin->id,
            'actif' => true,
        ]);

        $this->admin->update(['current_entreprise_id' => $this->entreprise->id]);
        EntrepriseContext::setCurrentId($this->entreprise->id);
    }

    public function test_rejet_fichier_format_invalide_ou_colonnes_manquantes(): void
    {
        $badFile = UploadedFile::fake()->createWithContent('bad.pdf', 'contenu invalide');

        $this->expectException(InvalidArgumentException::class);
        ImportService::analyzeAndCreatePreview($badFile, 'produits', $this->entreprise->id, $this->admin->id);
    }

    public function test_rejet_colonnes_obligatoires_manquantes(): void
    {
        // Il manque la colonne 'prix' pour un import produits
        $csvContent = "nom;categorie\nSucre 1kg;Alimentation\n";
        $file = UploadedFile::fake()->createWithContent('produits_incomplets.csv', $csvContent);

        $this->expectException(InvalidArgumentException::class);
        ImportService::analyzeAndCreatePreview($file, 'produits', $this->entreprise->id, $this->admin->id);
    }

    public function test_apercu_detection_doublons_signalement_lignes_invalides_et_confirmation_avec_mouvement_stock_initial(): void
    {
        // Ligne 2 : valide avec stock initial de 25
        // Ligne 3 : doublon du même produit dans le fichier -> ignorée
        // Ligne 4 : prix négatif (-500) -> rejetée avec numéro de ligne
        $csvContent = "nom;prix;prix_achat;categorie;seuil_alerte;stock_initial\n"
            . "Lait en poudre 500g;2500;1900;Laitiers;5;25\n"
            . "Lait en poudre 500g;2500;1900;Laitiers;5;10\n"
            . "Produit Prix Negatif;-500;200;Divers;2;5\n";

        $file = UploadedFile::fake()->createWithContent('import_produits.csv', $csvContent);

        $batch = ImportService::analyzeAndCreatePreview($file, 'produits', $this->entreprise->id, $this->admin->id);

        // RM-063 : Tant que l'import n'est pas confirmé, aucun produit n'est créé en base !
        $this->assertEquals('apercu', $batch->statut);
        $this->assertEquals(0, Produit::count());
        $this->assertCount(1, $batch->lignes_valides);
        $this->assertCount(1, $batch->lignes_ignorees); // Doublon ligne 3
        $this->assertCount(1, $batch->lignes_rejetees); // Prix négatif ligne 4
        $this->assertEquals(4, $batch->lignes_rejetees[0]['ligne']);

        // Confirmation transactionnelle de l'importation
        $confirmedBatch = ImportService::confirmImport($batch);
        $this->assertEquals('confirme', $confirmedBatch->statut);
        $this->assertEquals(1, $confirmedBatch->imported_rows);

        $produit = Produit::where('nom', 'Lait en poudre 500g')->first();
        $this->assertNotNull($produit);
        $this->assertEquals(2500, (float) $produit->prix);

        // RM-066 : Le stock initial de 25 a créé un mouvement d'ouverture traçable dans mouvement_stocks
        $this->assertEquals(25, $produit->stock_actuel);
        $mouvement = MouvementStock::where('produit_id', $produit->id)->first();
        $this->assertNotNull($mouvement);
        $this->assertEquals('entree', $mouvement->type_mouvement);
        $this->assertEquals(25, $mouvement->quantite);
        $this->assertStringContainsString('Stock initial d\'ouverture', $mouvement->motif);
    }
}

