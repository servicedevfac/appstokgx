<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            ['code_client' => 'CL-001', 'nom' => 'Dupont', 'prenom' => 'Jean', 'telephone' => '0102030405', 'adresse' => '10 rue de Paris'],
            ['code_client' => 'CL-002', 'nom' => 'Martin', 'prenom' => 'Sophie', 'telephone' => '0607080910', 'adresse' => '5 avenue des Champs'],
            ['code_client' => 'CL-003', 'nom' => 'Durand', 'prenom' => 'Pierre', 'telephone' => '0708091011', 'adresse' => '12 boulevard Lyon'],
            ['code_client' => 'CL-004', 'nom' => 'Kone', 'prenom' => 'Moussa', 'telephone' => '0555555555', 'adresse' => 'Abidjan'],
            ['code_client' => 'CL-005', 'nom' => 'Diop', 'prenom' => 'Fatou', 'telephone' => '0544444444', 'adresse' => 'Dakar'],
        ];

        foreach ($clients as $client) {
            Client::firstOrCreate(['code_client' => $client['code_client']], $client);
        }
    }
}
