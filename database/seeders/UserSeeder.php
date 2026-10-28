<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate([
            'email' => 'admin@example1.com',
        ], [
            'nom' => 'Djue Celestin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Administrateur');
        // Assigner les permissions à l'admin
         $superadmin = User::firstOrCreate([
            'email' => 'superadmin@example1.com',
        ], [
            'nom' => 'Palmer admin',
            'password' => Hash::make('password'),
        ]);
        $superadmin->assignRole('super admin');

        // Utilisateur gestionnaire
        $gestionnaire = User::firstOrCreate([
            'email' => 'gestionnaire@example1.com',
        ], [
            'nom' => 'Dogeles Gestionnaire',
            'password' => Hash::make('password'),
        ]);
        $gestionnaire->assignRole('Gestionnaire');

    }
    }

