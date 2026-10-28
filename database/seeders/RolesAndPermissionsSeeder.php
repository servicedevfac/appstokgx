<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        // Reset
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Créer les permissions
        $permissions = [
            'view permission',
            'create permission',
            'edit permission',
            'delete permission',

            'view role',
            'create role',
            'edit role',
            'delete role',

            'view utilisateur',
            'create utilisateur',
            'edit utilisateur',
            'delete utilisateur',

            'view produit',
            'create produit',
            'edit produit',
            'delete produit',
            'exporter produit',

            'view client',
            'create client',
            'edit client',
            'delete client',
            'exporter client',

            'view vente',
            'create vente',
            'edit vente',
            'delete vente',
            'exporter vente',
            'annuler vente',

            'view stock',
            'create stock',
            'edit stock',
            'delete stock',
            'exporter stock',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        
        $admin = Role::firstOrCreate(['name' => 'Administrateur']);
        $gestionnaire = Role::firstOrCreate(['name' => 'Gestionnaire']);
        $Commerciale = Role::firstOrCreate(['name' => 'Commerciale']);
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->givePermissionTo($permissions);
        $gestionnaire->givePermissionTo([
           'view client',
            'create client',
            'edit client',
            'view produit',
            'view vente',
            'create vente',
            'view stock',
        ]);
        $admin->givePermissionTo([
            'view utilisateur',
            'create utilisateur',
            'edit utilisateur',
            'delete utilisateur',
            'view client',
            'create client',
            'edit client',
            'delete client',
            'exporter client',
            'view vente',
            'create vente',
            'edit vente',
            'delete vente',
            'exporter vente',
            'view stock',
            'create stock',
            'edit stock',
            'delete stock',
            'exporter stock',
            'view produit',
            'create produit',
            'edit produit',
            'delete produit',
            'exporter produit',
            ]);
        $Commerciale->givePermissionTo([
           'view client',
            'create client',
            'edit client',
            'view produit',
            'view vente',
            'create vente',
            'view stock',
        ]);
    }
}


