<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DepensePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view depense',
            'create depense',
            'edit depense',
            'delete depense',
            'exporter depense',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Seuls les administrateurs ont le droit de modifier et supprimer
        $adminRoles = ['super admin', 'Administrateur'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Le Gestionnaire peut seulement voir, enregistrer et exporter les dépenses (PAS modifier, PAS supprimer)
        $gestionnaire = Role::where('name', 'Gestionnaire')->first();
        if ($gestionnaire) {
            // Révoquer modification et suppression si attribuées auparavant
            $gestionnaire->revokePermissionTo(['edit depense', 'delete depense']);
            $gestionnaire->givePermissionTo(['view depense', 'create depense', 'exporter depense']);
        }

        // Rôles commerciaux
        $commercialRoles = ['Commercial', 'Commerciale'];
        foreach ($commercialRoles as $cRole) {
            $role = Role::where('name', $cRole)->first();
            if ($role) {
                $role->revokePermissionTo(['edit depense', 'delete depense']);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
