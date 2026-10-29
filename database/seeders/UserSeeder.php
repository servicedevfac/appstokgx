<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\Horaire;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Initialise les comptes SaaS :
     * 1. Super Administrateur SaaS Global (non lié à une entreprise opérationnelle)
     * 2. Propriétaire + Entreprise Principale + Abonnement Business Pro
     * 3. Gestionnaire de stock et Vendeur / Caissier liés à l'Entreprise Principale
     */
    public function run(): void
    {
        // 1. Super Administrateur SaaS Global
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@example1.com'],
            [
                'nom'                   => 'Palmer',
                'prenom'                => 'SuperAdmin',
                'telephone'             => '0100000000',
                'password'              => Hash::make('password'),
                'actif'                 => true,
                'is_proprietaire'       => false,
                'current_entreprise_id' => null,
                'is_demo'               => false,
            ]
        );
        $superadmin->syncRoles(['super admin']);
        DB::table('entreprise_user')->where('user_id', $superadmin->id)->delete();

        // 2. Propriétaire de l'entreprise principale
        $proprietaire = User::updateOrCreate(
            ['email' => 'admin@example1.com'],
            [
                'nom'             => 'Djue',
                'prenom'          => 'Celestin',
                'telephone'       => '0700000001',
                'password'        => Hash::make('password'),
                'actif'           => true,
                'is_proprietaire' => true,
                'is_demo'         => false,
            ]
        );
        $proprietaire->syncRoles(['Propriétaire']);

        // Création de l'entreprise principale cliente SaaS
        $entreprise = Entreprise::updateOrCreate(
            ['code' => 'ENT-MAIN01'],
            [
                'nom'                    => 'STOKGX Entreprise Principale',
                'email'                  => 'contact@stokgx-entreprise.ci',
                'telephone'              => '+225 07 00 00 00 01',
                'adresse'                => 'Abidjan, Plateau',
                'devise'                 => 'FCFA',
                'plafond_avance_salaire' => 100000,
                'proprietaire_id'        => $proprietaire->id,
                'actif'                  => true,
                'is_demo'                => false,
            ]
        );

        // Abonnement actif de l'entreprise principale
        $entreprise->ensureDefaultSubscription('pro', $superadmin->id);

        // Rattachement du propriétaire à son entreprise
        $proprietaire->update(['current_entreprise_id' => $entreprise->id]);
        DB::table('entreprise_user')->updateOrInsert(
            ['entreprise_id' => $entreprise->id, 'user_id' => $proprietaire->id],
            ['role' => 'Propriétaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        // Horaires d'ouverture par défaut pour l'entreprise principale
        foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'] as $jour) {
            Horaire::withoutGlobalScope('entreprise')->updateOrCreate(
                ['entreprise_id' => $entreprise->id, 'jour_semaine' => $jour],
                [
                    'heure_ouverture' => '00:00',
                    'heure_fermeture' => '23:59',
                    'id_utilisateur'  => $proprietaire->id,
                ]
            );
        }

        // 3. Gestionnaire de stock rattaché à l'entreprise principale
        $gestionnaire = User::updateOrCreate(
            ['email' => 'gestionnaire@example1.com'],
            [
                'nom'                   => 'Dogeles',
                'prenom'                => 'Gestionnaire',
                'telephone'             => '0700000002',
                'password'              => Hash::make('password'),
                'actif'                 => true,
                'is_proprietaire'       => false,
                'current_entreprise_id' => $entreprise->id,
                'is_demo'               => false,
            ]
        );
        $gestionnaire->syncRoles(['Gestionnaire']);
        DB::table('entreprise_user')->updateOrInsert(
            ['entreprise_id' => $entreprise->id, 'user_id' => $gestionnaire->id],
            ['role' => 'Gestionnaire', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        // 4. Vendeur / Caissier rattaché à l'entreprise principale
        $vendeur = User::updateOrCreate(
            ['email' => 'vendeur@example1.com'],
            [
                'nom'                   => 'Konan',
                'prenom'                => 'Vendeur',
                'telephone'             => '0700000003',
                'password'              => Hash::make('password'),
                'actif'                 => true,
                'is_proprietaire'       => false,
                'current_entreprise_id' => $entreprise->id,
                'is_demo'               => false,
            ]
        );
        $vendeur->syncRoles(['Commerciale']);
        DB::table('entreprise_user')->updateOrInsert(
            ['entreprise_id' => $entreprise->id, 'user_id' => $vendeur->id],
            ['role' => 'Commerciale', 'actif' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
