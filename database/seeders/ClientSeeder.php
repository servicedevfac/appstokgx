<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Fournisseur;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Initialise les clients, fournisseurs et employés de l'entreprise principale SaaS.
     */
    public function run(): void
    {
        $entreprise = Entreprise::where('code', 'ENT-MAIN01')->first()
            ?? Entreprise::where('is_demo', false)->orderBy('id')->first();

        if (! $entreprise) {
            return;
        }

        $entrepriseId = $entreprise->id;

        // 1. Clients de l'entreprise
        $clients = [
            ['code_client' => 'CL-001', 'nom' => 'Dupont', 'prenom' => 'Jean', 'telephone' => '0102030405', 'adresse' => 'Abidjan, Cocody'],
            ['code_client' => 'CL-002', 'nom' => 'Martin', 'prenom' => 'Sophie', 'telephone' => '0607080910', 'adresse' => 'Abidjan, Marcory'],
            ['code_client' => 'CL-003', 'nom' => 'Durand', 'prenom' => 'Pierre', 'telephone' => '0708091011', 'adresse' => 'Abidjan, Treichville'],
            ['code_client' => 'CL-004', 'nom' => 'Kone', 'prenom' => 'Moussa', 'telephone' => '0555555555', 'adresse' => 'Abidjan, Yopougon'],
            ['code_client' => 'CL-005', 'nom' => 'Diop', 'prenom' => 'Fatou', 'telephone' => '0544444444', 'adresse' => 'San Pedro, Centre'],
        ];

        foreach ($clients as $client) {
            $client['entreprise_id'] = $entrepriseId;
            Client::withoutGlobalScope('entreprise')->updateOrCreate(
                ['code_client' => $client['code_client'], 'entreprise_id' => $entrepriseId],
                $client
            );
        }

        // 2. Fournisseurs de l'entreprise
        $fournisseurs = [
            [
                'code_fournisseur' => 'FRN-0001',
                'nom'              => 'Tech Grossiste CI',
                'contact'          => 'M. Bamba',
                'telephone'        => '0709080706',
                'email'            => 'commandes@techgrossiste.ci',
                'adresse'          => 'Zone Industrielle Marcory',
            ],
            [
                'code_fournisseur' => 'FRN-0002',
                'nom'              => 'Distri-Informatique Abidjan',
                'contact'          => 'Mme Touré',
                'telephone'        => '0504030201',
                'email'            => 'contact@distri-info.ci',
                'adresse'          => 'Abidjan, Plateau',
            ],
        ];

        foreach ($fournisseurs as $fournisseur) {
            $fournisseur['entreprise_id'] = $entrepriseId;
            Fournisseur::withoutGlobalScope('entreprise')->updateOrCreate(
                ['nom' => $fournisseur['nom'], 'entreprise_id' => $entrepriseId],
                $fournisseur
            );
        }

        // 3. Employés de l'entreprise
        $employes = [
            [
                'matricule'      => 'EMP-0001',
                'nom'            => 'Konan',
                'prenom'         => 'Yao',
                'poste'          => 'Magasinier Principal',
                'telephone'      => '0711223344',
                'email'          => 'konan.yao@stokgx-entreprise.ci',
                'salaire_base'   => 180000,
                'plafond_avance' => 75000,
                'date_embauche'  => now()->subMonths(6)->format('Y-m-d'),
                'actif'          => true,
            ],
            [
                'matricule'      => 'EMP-0002',
                'nom'            => 'Aka',
                'prenom'         => 'Marie',
                'poste'          => 'Conseillère Commerciale',
                'telephone'      => '0755667788',
                'email'          => 'aka.marie@stokgx-entreprise.ci',
                'salaire_base'   => 160000,
                'plafond_avance' => 60000,
                'date_embauche'  => now()->subMonths(3)->format('Y-m-d'),
                'actif'          => true,
            ],
        ];

        foreach ($employes as $emp) {
            $emp['entreprise_id'] = $entrepriseId;
            Employe::withoutGlobalScope('entreprise')->updateOrCreate(
                ['matricule' => $emp['matricule'], 'entreprise_id' => $entrepriseId],
                $emp
            );
        }
    }
}
