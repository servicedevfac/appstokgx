<?php

namespace Database\Seeders;

use App\Services\DemoService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepensePermissionsSeeder::class,
            UserSeeder::class,
            ClientSeeder::class,
            ProduitSeeder::class,
            DepenseSampleSeeder::class,
        ]);

        // Initialiser également l'entreprise de démonstration isolée et son abonnement Démo
        $demoResult = DemoService::resetDemoEnvironment();
        if (! empty($demoResult['entreprise'])) {
            $demoResult['entreprise']->ensureDefaultSubscription('essai');
        }
    }
}
